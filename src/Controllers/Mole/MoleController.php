<?php

declare(strict_types=1);

namespace App\Controllers\Mole;

use App\Controllers\BaseController;
use App\Models\Invoice;
use App\Services\Purchase;
use App\Models\Config;
use App\Services\Cache;
use App\Services\Auth;
use App\Models\Ann;
use App\Models\User;
use App\Models\UserCoupon;
use App\Models\Paylist;
use App\Models\Order;
use App\Models\Product;
use App\Models\UserMoneyLog;
use App\Models\Docs;
use App\Services\DataUsage;
use App\Services\MFA;
use App\Services\MockData;
use App\Services\DeviceService;
use App\Utils\ResponseHelper;
use App\Utils\Tools;
use App\Utils\Hash;
use Ramsey\Uuid\Uuid;
use Exception;
use RedisException;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use function strtotime;
use function time;

final class MoleController extends BaseController
{
    /**
     * @throws Exception
     */
    public function sometrigger(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $res = DeviceService::addDeviceToUser($this->user);
        return $response->write(json_encode($res));
    }

    /**
     * @throws Exception
     */
    public function dashboard(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $anns = (new Ann())->orderBy('date', 'desc')->get();

        $userDevices = DeviceService::getUserDeviceList($this->user);
        $activated_order = (new Order())
            ->where('user_id', $this->user->id)
            ->where('status', 'activated')
            ->where('product_type', 'tabp')
            ->first();
        $data_usage = DataUsage::getUserDataUsage($this->user->id);
        $expected_suffice_till = null;
        if ($activated_order !== null) {
            $activated_order->product_content = json_decode($activated_order->product_content);
            $availale_date = intval($this->user->money / $activated_order->price) * $activated_order->product_content->time;
            $expected_suffice_till = strtotime($this->user->plan_start_date) + ($availale_date * 24 * 60 * 60);
        }

        return $response->write(
            $this->view()
                ->assign('data', MockData::getData())
                ->assign('user_devices', $userDevices)
                ->assign('announcements', $anns)
                ->assign('activated_order', $activated_order)
                ->assign('data_usage', $data_usage)
                ->assign('expected_suffice_till', $expected_suffice_till)
                ->fetch('user/mole/dashboard.tpl')
        );
    }

    /**
     * @throws Exception
     */
    public function getAnnByID(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $ann = (new Ann())->find($args['id']);
        return $response->write(
            $this->view()
                ->assign('ann', $ann)
                ->fetch('user/mole/component/dashboard/announcement_detail.tpl')
        );
    }

    /**
     * @throws Exception
     */
    public function plan(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $addition_quota = false;
        if(substr_compare($request->getUri()->__toString(), "addition-quota", -strlen("addition-quota")) === 0)
        {
            $addition_quota = true;
        }
        $available_plans = (new Product())
            ->where('status', '1')
            ->where('type', 'tabp')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($available_plans as $plan) {
            $content = json_decode($plan->content);
            $plan->devices_limit = $plan->limit;
            $plan->description = $content->description;
            $plan->features = json_decode($content->features, true);
        }

        $data_plans = (new Product())
            ->where('status', '1')
            ->where('type', 'bandwidth')
            ->orderBy('id', 'asc')
            ->get();

        $userDevices = DeviceService::getUserDeviceList($this->user);
        $activated_order = (new Order())
            ->where('user_id', $this->user->id)
            ->where('status', 'activated')
            ->where('product_type', 'tabp')
            ->first();
        $data_usage = DataUsage::getUserDataUsage($this->user->id);

        $next_payment_date = 0;
        if ($activated_order !== null) {
            $activated_order->product_content = json_decode($activated_order->product_content);
            $next_payment_date = $activated_order->update_time + $activated_order->product_content->time * 24 * 60 * 60;
        }

        return $response->write(
            $this->view()
                ->assign("addition_quota", $addition_quota)
                ->assign('data', MockData::getData())
                ->assign('user_devices', $userDevices)
                ->assign('available_plans', $available_plans)
                ->assign('data_plans', $data_plans)
                ->assign('activated_order', $activated_order)
                ->assign('data_usage', $data_usage)
                ->assign('next_payment_date', $next_payment_date)
                ->assign('member_since', $this->user->plan_start_date ? $this->user->plan_start_date : 0)
                ->fetch('user/mole/plan.tpl')
        );
    }


    private function _cancelCurrentPlan()
    {
        // get current plan
        $activated_order = (new Order())
            ->where('user_id', $this->user->id)
            ->where('status', 'activated')
            ->where('product_type', 'tabp')
            ->first();

        if ($activated_order === null) {
            return true;
        }
        $product_content = json_decode($activated_order->product_content);

        // calculate refund amount
        $data_usage = DataUsage::getUserDataUsage($this->user->id);
        $refund_amount_base_time = $activated_order->price - ((time() - $activated_order->update_time) / ($product_content->time * 24 * 60 * 60)) * $activated_order->price;
        $refund_amount_base_quota = $activated_order->price - ($data_usage / ($product_content->bandwidth * 1024 * 1024 * 1024)) * $activated_order->price;

        $refund_amount = number_format($refund_amount_base_time < $refund_amount_base_quota ? $refund_amount_base_time : $refund_amount_base_quota, 2, '.', '');

        // cancel current activated order & update user data
        $activated_order->status = 'cancelled';
        $activated_order->save();

        DataUsage::cancelUserPlan($this->user->id);
        $reset_traffic = $_ENV['class_expire_reset_traffic'];
        if ($reset_traffic >= 0) {
            $this->user->transfer_enable = Tools::toGB($reset_traffic);
        }

        $this->user->node_speedlimit = 0;
        $this->user->node_iplimit = 0;
        $this->user->u = 0;
        $this->user->d = 0;
        $this->user->transfer_today = 0;
        $this->user->class = 0;
        $this->user->plan_start_date = null;


        // update user balance change log
        (new UserMoneyLog())->add(
            $this->user->id,
            $this->user->money,
            (float) $this->user->money + $refund_amount,
            (float) $refund_amount,
            'unused ' . $activated_order->product_name,
            "plan cancel"
        );

        $this->user->money = $this->user->money + $refund_amount;
        $this->user->save();
    }

    /**
     * @throws Exception
     */
    public function purchaseOrder(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $product_id = $this->antiXss->xss_clean($request->getQueryParams()['product_id']) ?? null;

        if ($product_id === null || $product_id === '') {
            return $response->write(
                $this->view()
                    ->fetch('user/mole/component/plan/purchase_error_occur.tpl')
            );
        }

        $activated_order = (new Order())
            ->where('user_id', $this->user->id)
            ->where('status', 'activated')
            ->where('product_type', 'tabp')
            ->first();

        if ($activated_order !== null && $product_id === $activated_order->product_id) {
            return $response->write(
                $this->view()
                    ->fetch('user/mole/component/plan/repeat_plan.tpl')
            );
        }

        $product = (new Product())->where('id', $product_id)->first();
        // check balance
        if ($product->price > $this->user->money) {
            return $response->write(
                $this->view()
                    ->assign('balance', $this->user->money)
                    ->assign('price', $product->price)
                    ->fetch('user/mole/component/plan/no_enough_balance.tpl')
            );
        }

        if ($activated_order !== null) {
            $this->_cancelCurrentPlan();
        }

        // create order
        $result = Purchase::createOrder($product_id, $this->user);
        if ($result === false) {
            return $response->write(
                $this->view()
                    ->fetch('user/mole/component/plan/purchase_error_occur.tpl')
            );
        }

        // pay with balance
        $res = Purchase::purchaseWithBalance($result, $this->user);
        if ($res === true) {
            return $response->write(
                $this->view()
                    ->assign('plan_name', $product->name)
                    ->fetch('user/mole/component/plan/purchase_success.tpl')
            );
        }

        // activate plan
        return $response->write(
            $this->view()
                ->fetch('user/mole/component/plan/purchase_error_occur.tpl')
        );
    }

    /**
     * @throws Exception
     */
    public function cancelCurrentPlan(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $this->_cancelCurrentPlan();
        return $response->withHeader('HX-Refresh', 'true');
    }



    /**
     * @throws Exception
     */
    public function purchaseDataQuota(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $product_id = $this->antiXss->xss_clean($request->getQueryParams()['product_id']) ?? null;

        // check product selected
        if ($product_id === null || $product_id === '') {
            return $response->write(
                $this->view()
                    ->assign('status', "Operation Fail")
                    ->assign('message', 'no product selected')
                    ->fetch('user/mole/component/plan/operation-res.tpl')
            );
        }

        //  check have tabp order already
        $activated_order = (new Order())
            ->where('user_id', $this->user->id)
            ->where('status', 'activated')
            ->where('product_type', 'tabp')
            ->first();

        if ($activated_order === null) {
            return $response->write(
                $this->view()
                    ->assign('status', "Operation Fail")
                    ->assign('message', 'no activated plan')
                    ->fetch('user/mole/component/plan/operation-res.tpl')
            );
        }

        // purchase with balance
        $product = (new Product())->where('id', $product_id)->first();
        // check balance
        if ($product->price > $this->user->money) {
            return $response->write(
                $this->view()
                    ->assign('status', "Operation Fail")
                    ->assign('message', 'no enough balance')
                    ->fetch('user/mole/component/plan/operation-res.tpl')
            );
        }

        // create order
        $result = Purchase::createOrder($product_id, $this->user);
        if ($result === false) {
            return $response->write(
                $this->view()
                    ->assign('status', "Operation Fail")
                    ->assign('message', 'error occur when purchase plan')
                    ->fetch('user/mole/component/plan/operation-res.tpl')
            );
        }

        // pay with balance
        $res = Purchase::purchaseWithBalance($result, $this->user);
        if ($res === true) {
            return $response->write(
                $this->view()
                    ->assign('status', "Operation Succuss")
                    ->assign('message', 'purchase success')
                    ->fetch('user/mole/component/plan/operation-res.tpl')
            );
        }

        return $response->write(
            $this->view()
                ->assign('status', "Operation Fail")
                ->assign('message', 'System Error, try later')
                ->fetch('user/mole/component/plan/operation-res.tpl')
        );
    }

    /**
     * @throws Exception
     */
    public function devices(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $userDevices = DeviceService::getUserDeviceList($this->user);

        return $response->write(
            $this->view()
                ->assign('data', MockData::getData())
                ->assign('user_devices', $userDevices)
                ->fetch('user/mole/devices.tpl')
        );
    }

    /**
     * @throws Exception
     */
    public function activate(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        if (isset($_POST['id'])) {
            $device_id = $_POST['id'];
            $result = DeviceService::activateUserDevice($this->user, $device_id);
            return $response->write(
                $this->view()
                    ->assign('user_devices', $result)
                    ->fetch('user/mole/component/devices/devices_list.tpl')
            );
        }
        return $response->write(
            $this->view()
                ->assign('user_devices', DeviceService::getUserDeviceList($this->user))
                ->fetch('user/mole/component/devices/devices_list.tpl')
        );
    }

    /**
     * @throws Exception
     */
    public function deactivate(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        if (isset($_POST['id'])) {
            $device_id = $_POST['id'];
            $result = DeviceService::deactivatedUserDevice($this->user, $device_id);
            return $response->write(
                $this->view()
                    ->assign('user_devices', $result)
                    ->fetch('user/mole/component/devices/devices_list.tpl')
            );
        }
        return $response->write(
            $this->view()
                ->assign('user_devices', DeviceService::getUserDeviceList($this->user))
                ->fetch('user/mole/component/devices/devices_list.tpl')
        );
    }

    /**
     * @throws Exception
     */
    public function remove_device(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $device_id = $args['id'];
        $result = DeviceService::removeDeviceFromUser($this->user, $device_id);
        return $response->write(
            $this->view()
                ->assign('user_devices', $result)
                ->fetch('user/mole/component/devices/devices_list.tpl')
        );
    }

    /**
     * @throws Exception
     */
    public function getActivateCode(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $result = DeviceService::getActivateCode($this->user->id);
        return $response->write(
            $this->view()
                ->assign('activateCode', $result)
                ->fetch('user/mole/component/devices/activation.tpl')
        );
    }

    /**
     * @throws RedisException
     */
    public function updateEmail(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $new_email = $this->antiXss->xss_clean($request->getParam('newemail'));
        $user = $this->user;
        $old_email = $user->email;

        if (!$_ENV['enable_change_email'] || $user->is_shadow_banned) {
            return $response->write(
                $this->view()
                    ->assign('status', "Operation Failed")
                    ->assign('message', 'Update Email Fail')
                    ->fetch('user/mole/component/account/operation-res.tpl')
            );
        }

        if ($new_email === '') {
            return $response->write(
                $this->view()
                    ->assign('status', "Operation Failed")
                    ->assign('message', 'You must fill up email')
                    ->fetch('user/mole/component/account/operation-res.tpl')
            );
        }

        $check_res = Tools::isEmailLegal($new_email);

        if ($check_res['ret'] !== 1) {
            return $response->withJson($check_res);
        }

        $exist_user = (new User())->where('email', $new_email)->first();

        if ($exist_user !== null) {
            return $response->write(
                $this->view()
                    ->assign('status', "Operation Failed")
                    ->assign('message', 'The email is using')
                    ->fetch('user/mole/component/account/operation-res.tpl')
            );
        }

        if ($new_email === $old_email) {
            return $response->write(
                $this->view()
                    ->assign('status', "Operation Failed")
                    ->assign('message', 'new email must be different between old one')
                    ->fetch('user/mole/component/account/operation-res.tpl')
            );
        }

        // if (Config::obtain('reg_email_verify')) {
        //     $redis = (new Cache())->initRedis();
        //     $email_verify_code = $request->getParam('emailcode');
        //     $email_verify = $redis->get('email_verify:' . $email_verify_code);

        //     if (!$email_verify) {
        //         return ResponseHelper::error($response, '你的邮箱验证码不正确');
        //     }

        //     $redis->del('email_verify:' . $email_verify_code);
        // }

        $user->email = $new_email;

        if (!$user->save()) {
            return $response->write(
                $this->view()
                    ->assign('status', "Operation Failed")
                    ->assign('message', 'System Error')
                    ->fetch('user/mole/component/account/operation-res.tpl')
            );
        }

        return $response->write(
            $this->view()
                ->assign('status', "Operation Succuss")
                ->assign('message', 'Email has changed, relogin pls')
                ->fetch('user/mole/component/account/operation-res.tpl')
        );
    }


    public function updatePassword(ServerRequest $request, Response $response, array $args): ResponseInterface
    {
        $pwd = $request->getParam('passwd');
        $user = $this->user;

        if ($pwd === '') {
            return $response->write(
                $this->view()
                    ->assign('status', "Operation Failed")
                    ->assign('message', 'Password can not be empty')
                    ->fetch('user/mole/component/account/operation-res.tpl')
            );
        }

        if (strlen($pwd) < 8) {
            return $response->write(
                $this->view()
                    ->assign('status', "Operation Failed")
                    ->assign('message', 'Password too short')
                    ->fetch('user/mole/component/account/operation-res.tpl')
            );
        }

        if (!$user->updatePassword($pwd)) {
            return $response->write(
                $this->view()
                    ->assign('status', "Operation Failed")
                    ->assign('message', 'Update Fail')
                    ->fetch('user/mole/component/account/operation-res.tpl')
            );
        }

        // if (Config::obtain('enable_forced_replacement')) {
        //     $user->cleanLink();
        // }

        return $response->write(
            $this->view()
                ->assign('status', "Operation Succuss")
                ->assign('message', 'Password has changed, relogin pls')
                ->fetch('user/mole/component/account/operation-res.tpl')
        );
    }


    public function updateContactMethod(ServerRequest $request, Response $response, array $args): ResponseInterface
    {
        $email_enable = (bool) $request->getParam('email');
        $im_enable = (bool) $request->getParam('telegram');
        $value = 0;

        if ($email_enable && !$im_enable) {
            $value = 1;
        } else if (!$email_enable && $im_enable) {
            $value = 2;
        } else if ($email_enable && $im_enable) {
            $value = 3;
        }

        $user = $this->user;
        $user->contact_method = $value;

        if (!$user->save()) {
            return $response->withHeader('HX-Refresh', 'true');
        }

        return $response->withHeader('HX-Refresh', 'true');
    }

    /**
     * @throws Exception
     */
    public function account(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        return $response->write(
            $this->view()
                ->assign('user', $this->user)
                ->assign('data', MockData::getData())->fetch('user/mole/account.tpl')
        );
    }


    /**
     * @throws Exception
     */
    public function faq(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $faq_list = (new Docs())->orderBy('id', 'desc')->get();

        return $response->write(
            $this->view()
                ->assign('data', MockData::getData())
                ->assign('faq_list', $faq_list)
                ->fetch('user/mole/faq.tpl')
        );
    }

    /**
     * @throws Exception
     */
    public function initPurchase(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $product_id = $args['id'] ?? null;
        if ($product_id === null) {
            return $response->withStatus(404)->write($this->view()->fetch('404.tpl'));
        }

        $product = (new Product())->where("id", $product_id)->first();
        if ($product === null) {
            return $response->withStatus(404)->write($this->view()->fetch('404.tpl'));
        }
        $product->content = json_decode($product->content, true);
        $next_pay = strtotime('+30 days', time());

        return $response->write(
            $this->view()
                ->assign("product", $product)
                ->assign("next_pay", $next_pay)
                ->fetch('user/mole/init-purchase.tpl')
        );
    }

    /**
     * @throws Exception
     */
    public function createInitPurchase(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        // ----------------
        // get parameter
        $coupon_code = $this->antiXss->xss_clean($request->getParam('coupon_code'));
        $product_id = $this->antiXss->xss_clean($request->getParam('product_id')) ?? null;
        $payment_method = $this->antiXss->xss_clean($request->getParam('paymentSelect'));
        $email = $this->antiXss->xss_clean($request->getParam('email'));


        // -----------------
        // check and create account
        if ($this->user->id === null) {
            // create account with email
            $user = new User();
            $configs = Config::getClass('reg');

            $user->user_name = "";
            $user->email = $email;
            $user->remark = '';
            $user->pass = '';
            $user->passwd = Tools::genRandomChar(16);
            $user->uuid = Uuid::uuid4();
            $user->api_token = Uuid::uuid4();
            $user->port = Tools::getSsPort();
            $user->u = 0;
            $user->d = 0;
            $user->method = $configs['sign_up_for_method'];
            $user->forbidden_ip = Config::obtain('reg_forbidden_ip');
            $user->forbidden_port = Config::obtain('reg_forbidden_port');
            $user->im_type = 0;
            $user->im_value = '';
            $user->transfer_enable = Tools::toGB($configs['sign_up_for_free_traffic']);
            $user->invite_num = $configs['sign_up_for_invitation_codes'];
            $user->auto_reset_day = Config::obtain('free_user_reset_day');
            $user->auto_reset_bandwidth = Config::obtain('free_user_reset_bandwidth');
            $user->daily_mail_enable = $configs['sign_up_for_daily_report'];
            $user->money = 0;
            $user->ref_by = 0;
            $user->ga_token = MFA::generateGaToken();
            $user->ga_enable = 0;
            $user->class_expire = date('Y-m-d H:i:s', time() + (int) $configs['sign_up_for_class_time'] * 86400);
            $user->class = $configs['sign_up_for_class'];
            $user->node_iplimit = $configs['connection_ip_limit'];
            $user->node_speedlimit = $configs['connection_rate_limit'];
            $user->reg_date = date('Y-m-d H:i:s');
            $user->reg_ip = $_SERVER['REMOTE_ADDR'];
            $user->theme = $_ENV['theme'];
            $user->locale = $_ENV['locale'];
            $random_group = Config::obtain('random_group');

            if ($random_group === '') {
                $user->node_group = 0;
            } else {
                $user->node_group = $random_group[array_rand(explode(',', $random_group))];
            }

            $user->save();

            $time = 86400 * ($_ENV['rememberMeDuration'] ?: 7);

            Auth::login($user->id, $time);
            $this->user = $user;
        }

        // -----------------
        // deal with product and coupon
        if ($product_id === null || $product_id === '') {
            return $response->write("need product id");
        }

        $product = (new Product())->where('id', $product_id)->first();
        if ($product === null) {
            return $response->write("no such product");
        }

        $result = $this->checkCoupon($coupon_code, $product);
        if ($result === false) {
            return $response->write("invalid coupon");
        }

        $pay_amount = $result;

        // -------------------
        // return payment page
        if ($pay_amount < 1) {
            return $response->write("pay amount error");
        }

        $pl = new Paylist();
        $pl->userid = $this->user->id;
        $pl->total = $pay_amount;
        $pl->invoice_id = 0;
        $pl->tradeno = Tools::genRandomChar();
        $pl->gateway = "Cryptomus";
        $pl->save();

        // ------------------
        // create order
        $result = Purchase::createOrder($product_id, $this->user, (new UserCoupon())->where('code', $coupon_code)->first());
        if ($result === false) {
            return $response->write("create order fail");
        }

        if ($payment_method === "crypto" || $payment_method === "usdt") {
            $configs = Config::getClass('billing');
            $cryptomus_merchant_uuid = $configs['cryptomus_merchant_uuid'];
            $cryptomus_payment_key = $configs['cryptomus_payment_key'];

            $payment = \Cryptomus\Api\Client::payment($cryptomus_payment_key, $cryptomus_merchant_uuid);

            $data = [
                'amount' => (string) $pay_amount,
                'currency' => 'USD',
                'order_id' => $pl->tradeno,
                'url_return' => $_ENV['baseUrl'] . '/init-purchase/' . $product_id,
                'url_callback' => $_ENV['baseUrl'] . '/init-purchase/return' . "&invoice_id=" . $result,
                'url_success' => $_ENV['baseUrl'] . '/init-purchase/check' . "?trade_no=" . $pl->tradeno . "&invoice_id=" . $result,
                'is_payment_multiple' => false,
                'lifetime' => '3600',
            ];

            if ($payment_method === "usdt") {
                $data['network'] = "POLYGON";
                $data['to_currency'] = "USDT";
            }

            $result = $payment->create($data);

            return $response->withRedirect($result["url"]);
        }

        return $response->write(
            $pay_amount . $payment_method
        );
    }

    /**
     * @throws Exception
     */
    public function checkInitPurchase(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        // ----------------
        // get parameter
        $trade_no = $this->antiXss->xss_clean($request->getParam('trade_no'));
        $invoice_id = $this->antiXss->xss_clean($request->getParam('invoice_id'));

        // ----------------
        // check payment status(to update)
        $data = ["order_id" => $trade_no];

        $configs = Config::getClass('billing');
        $cryptomus_merchant_uuid = $configs['cryptomus_merchant_uuid'];
        $cryptomus_payment_key = $configs['cryptomus_payment_key'];

        $payment = \Cryptomus\Api\Client::payment($cryptomus_payment_key, $cryptomus_merchant_uuid);

        $result = $payment->info($data);
        if ($result["payment_status"] != "paid" && $result["payment_status"] != "paid_over") {
            return $response->write("fail");
        }

        $paylist = (new Paylist())->where('tradeno', $trade_no)->first();

        if ($paylist?->status === 0) {
            $paylist->datetime = time();
            $paylist->status = 1;
            $paylist->save();

            $this->user->money = $this->user->money + $paylist->total;
            $this->user->save();

            (new UserMoneyLog())->add(
                $this->user->id,
                $this->user->money,
                (float) $this->user->money + $paylist->total,
                (float) $paylist->total,
                '充值 #' . $trade_no,
                "crypto manual"
            );
        }


        // -----------------
        // order update
        $res = Purchase::purchaseWithBalance($invoice_id, $this->user);
        if ($res === true) {
            return $response->write("success");
        }

        return $response->write(
            json_encode($_POST)
        );
    }

    /**
     * @throws Exception
     */
    public function returnInitPurchase(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        // read paramter
        $coupon_code = $this->antiXss->xss_clean($request->getParam('coupon_code'));
        $invoice_id = $this->antiXss->xss_clean($request->getParam('invoice_id'));
        $trade_no = $this->antiXss->xss_clean($request->getParam('order_id'));
        $status = $this->antiXss->xss_clean($request->getParam('status'));

        $configs = Config::getClass('billing');
        $cryptomus_payment_key = $configs['cryptomus_payment_key'];

        $body = $request->getParsedBody();
        $get_sign = $body["sign"];
        unset($body["sign"]);
        $sign = md5(base64_encode(json_encode($body, JSON_UNESCAPED_UNICODE)) . $cryptomus_payment_key);

        if ($get_sign !== $sign) {
            return $response->withJson([
                'ret' => 0,
                'msg' => '非法请求',
            ]);
        }

        if ($status == "paid" || $status == "paid_over") {
            $paylist = (new Paylist())->where('tradeno', $trade_no)->first();
            $user = (new User())->where("id", $paylist->userid)->first();

            if ($paylist?->status === 0) {
                $paylist->datetime = time();
                $paylist->status = 1;
                $paylist->save();

                (new UserMoneyLog())->add(
                    $user->id,
                    $user->money,
                    (float) $user->money + $paylist->total,
                    (float) $paylist->total,
                    '充值 #' . $trade_no,
                    "crypto manual"
                );

                $user->money = $user->money + $paylist->total;
                $user->save();
            }
        }

        // -----------------
        // order update
        $res = Purchase::purchaseWithBalance($invoice_id, $user);
        if ($res === true) {
            return $response->write("success");
        }

        return $response->withJson([
            'ret' => 1,
            'msg' => 'success',
        ]);
    }

    private function checkCoupon(string $coupon_code, Product $product)
    {
        $buy_price = $product->price;

        if ($coupon_code === '' || $coupon_code === null) {
            return $buy_price;
        }
        $coupon = (new UserCoupon())->where('code', $coupon_code)->first();
        if ($coupon === null || ($coupon->expire_time !== 0 && $coupon->expire_time < time())) {
            return false;
        }

        $coupon_limit = json_decode($coupon->limit);
        if ($coupon_limit->disabled) {
            return false;
        }

        if ($coupon_limit->product_id !== '' && !in_array($product->id, explode(',', $coupon_limit->product_id))) {
            return false;
        }

        $coupon_use_limit = $coupon_limit->use_time;

        if ($coupon_use_limit > 0) {
            $user_use_count = (new Order())->where('user_id', $this->user->id)->whereIn('status', ['activated', 'expired', 'cancelled'])->where('coupon', $coupon->code)->count();
            if ($user_use_count >= $coupon_use_limit) {
                return false;
            }
        }

        if (property_exists($coupon_limit, 'total_use_time')) {
            $coupon_total_use_limit = $coupon_limit->total_use_time;
        } else {
            $coupon_total_use_limit = -1;
        }

        if ($coupon_total_use_limit > 0 && $coupon->use_count >= $coupon_total_use_limit) {
            return false;
        }

        $content = json_decode($coupon->content);

        if ($content->type === 'percentage') {
            $discount = $product->price * $content->value / 100;
        } else {
            $discount = $content->value;
        }

        $buy_price = $product->price - $discount;

        return $buy_price;
    }
}
