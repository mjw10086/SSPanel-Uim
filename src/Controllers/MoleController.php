<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Recurrence;
use App\Models\Withdraw;
use App\Services\Purchase;
use Ramsey\Uuid\Uuid;
use DateTime;
use App\Models\Ann;
use App\Models\Config;
use App\Models\User;
use App\Models\InviteCode;
use App\Models\LoginIp;
use App\Models\Node;
use App\Models\OnlineLog;
use App\Models\Order;
use App\Models\Payback;
use App\Models\Device;
use App\Models\Paylist;
use App\Models\Invoice;
use App\Models\UserDevices;
use App\Models\Product;
use App\Models\UserMoneyLog;
use App\Models\Docs;
use App\Services\Auth;
use App\Services\Captcha;
use App\Services\DataUsage;
use App\Services\Subscribe;
use App\Services\MockData;
use App\Services\DeviceService;
use App\Utils\ResponseHelper;
use App\Utils\Tools;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use function str_replace;
use function strtotime;
use function time;

final class MoleController extends BaseController
{
    /**
     * @throws Exception
     */
    public function sometrigger(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $res = DeviceService::addDeviceToUser($this->user->id);
        return $response->write(json_encode($res));
    }

    /**
     * @throws Exception
     */
    public function dashboard(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $anns = (new Ann())->orderBy('date', 'desc')->get();

        $userDevices = DeviceService::getUserDeviceList($this->user->id);
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
    public function billing(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $billing_history = (new Invoice())->where('user_id', $this->user->id)->where('status', 'paid_balance')->orderBy('update_time', 'desc')->get();
        foreach ($billing_history as $billing) {
            $billing->content = json_decode($billing->content);
        }

        $balance_history = (new UserMoneyLog())->where('user_id', $this->user->id)->where('type', 'top-up')->orWhere('type', 'withdraw')->orderBy('create_time', 'desc')->get();

        $activated_order = (new Order())
            ->where('user_id', $this->user->id)
            ->where('status', 'activated')
            ->where('product_type', 'tabp')
            ->first();

        $expected_suffice_till = null;
        if ($activated_order !== null) {
            $activated_order->product_content = json_decode($activated_order->product_content);
            $availale_date = intval($this->user->money / $activated_order->price) * $activated_order->product_content->time;
            $expected_suffice_till = strtotime($this->user->plan_start_date) + ($availale_date * 24 * 60 * 60);
        }

        return $response->write(
            $this->view()
                ->assign('data', MockData::getData())
                ->assign('billing_history', $billing_history)
                ->assign('balance_history', $balance_history)
                ->assign('expected_suffice_till', $expected_suffice_till)
                ->fetch('user/mole/billing.tpl')
        );
    }

    /**
     * @throws Exception
     */
    public function plan(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
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

        $userDevices = DeviceService::getUserDeviceList($this->user->id);
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
                ->assign('data', MockData::getData())
                ->assign('user_devices', $userDevices)
                ->assign('available_plans', $available_plans)
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
            '取消计划 #' . $activated_order->id,
            "cancel_plan"
        );

        $this->user->money = $this->user->money + $refund_amount;
        $this->user->save();
    }

    private function _cryptomusServiceList()
    {
        $configs = Config::getClass('billing');
        $cryptomus_merchant_uuid = $configs['cryptomus_merchant_uuid'];
        $cryptomus_payout_key = $configs['cryptomus_payout_key'];

        $requestBuilder = new \Cryptomus\Api\RequestBuilder($cryptomus_payout_key, $cryptomus_merchant_uuid);

        $result = $requestBuilder->sendRequest('v1' . '/payout/services');

        return $result;
    }


    /**
     * @throws Exception
     */
    public function getCryptomusNetworkList(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $list = $this->_cryptomusServiceList();
        return $response->write(
            $this->view()
                ->assign("list", $list)
                ->fetch('user/mole/component/billing/cryptomus_service_list.tpl')
        );
    }


    /**
     * @throws Exception
     */
    public function createWithdraw(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        // read paramter
        $amount = $this->antiXss->xss_clean($request->getParam('amount'));
        $address = $this->antiXss->xss_clean($request->getParam('address'));
        $network_list = explode(" ", $this->antiXss->xss_clean($request->getParam('network')));
        $network = $network_list[0];
        $currency = $network_list[1];

        // check amount
        if ($amount < 1 && $this->user->money < $amount) {
            return $response->withJson([
                'ret' => 0,
                'msg' => '非法的金额',
            ]);
        }

        // create withdraw order to cryptomus
        $configs = Config::getClass('billing');
        $cryptomus_merchant_uuid = $configs['cryptomus_merchant_uuid'];
        $cryptomus_payout_key = $configs['cryptomus_payout_key'];

        $payout = \Cryptomus\Api\Client::payout($cryptomus_payout_key, $cryptomus_merchant_uuid);

        $data = [
            'amount' => $amount,
            'currency' => 'USD',
            'to_currency' => $currency,
            'network' => $network,
            'order_id' => Tools::genRandomChar(),
            'address' => $address,
            'is_subtract' => '0',
            'url_callback' => "http://echo.connexusy.com",
            // 'url_callback' => $_ENV['baseUrl'] . '/user/billing/withdraw/return',
        ];

        try {
            $result = $payout->create($data);
        } catch (Exception $e) {
            $message = $e->getMessage();
            $status = "System Error";
            if (
                $message == "Not found service to_currency"
                || $message == "The service was not found"
                || $message == "The withdrawal amount is too small"
                || strpos($message, "mum amount")
            ) {
                $status = "Failed";
                $message = $message . ", try change your amount, currency or network";
            } else {
                $status = "System Error";
                $message = "Try later or contact with administrator";
            }
            
            return $response->write(
                $this->view()
                    ->assign("status", $status)
                    ->assign("message", $message)
                    ->fetch('user/mole/component/billing/withdraw_result.tpl')
            );
        }

        // while success, change user balance
        $this->user->money = $this->user->money - $result["amount"];
        $this->user->save();

        (new UserMoneyLog())->add(
            $this->user->id,
            $this->user->money,
            (float) $this->user->money - $result["amount"],
            (float) $result["amount"],
            '提款 #' . $result["uuid"],
            "withdraw"
        );

        // create withdraw record
        $withdraw = new Withdraw();
        $withdraw->user_id = $this->user->id;
        $withdraw->uuid = $result["uuid"];
        $withdraw->type = "cryptomus";
        $withdraw->amount = $result["amount"];
        $withdraw->withdraw_message = json_encode($result);
        $withdraw->status = $result["status"];
        $withdraw->message = "";

        $withdraw->save();

        $message = "Your withdraw is in progress";
        return $response->write(
            $this->view()
                ->assign("status", $result["status"])
                ->assign("message", $message)
                ->fetch('user/mole/component/billing/withdraw_result.tpl')
        );
    }

    /**
     * @throws Exception
     */
    public function returnWithdraw(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $configs = Config::getClass('billing');
        $cryptomus_payout_key = $configs['cryptomus_payout_key'];

        // read paramter
        $uuid = $this->antiXss->xss_clean($request->getParam('uuid'));
        $status = $this->antiXss->xss_clean($request->getParam('status'));

        // check request validity
        $body = $request->getParsedBody();
        $get_sign = $body["sign"];
        unset($body["sign"]);
        $sign = md5(base64_encode(json_encode($body, JSON_UNESCAPED_UNICODE)) . $cryptomus_payout_key);

        if ($get_sign !== $sign) {
            return $response->withJson([
                'ret' => 0,
                'msg' => '非法请求',
            ]);
        }

        // get withdraw record
        $withdrawRecord = (new Withdraw())->where("uuid", $uuid)->first();
        if ($withdrawRecord === null) {
            return $response->withJson([
                'ret' => 0,
                'msg' => 'no such withdraw record',
            ]);
        }

        // check status
        if ($status == "fail" || $status == "cancel" || $status == "system_fail") {
            // back money to user balance
            $user = (new User())->where("id", $withdrawRecord->user_id);

            (new UserMoneyLog())->add(
                $user->id,
                $user->money,
                (float) $user->money + $withdrawRecord->amount,
                (float) $withdrawRecord->amount,
                '提款退回 #' . $withdrawRecord->uuid,
                "withdraw"
            );

            $user->money = $user->money + $withdrawRecord->amount;
            $user->save();
        }

        // update withdraw record
        $withdrawRecord->status = $status;
        $withdrawRecord->message = json_encode($body);

        $withdrawRecord->save();

        return $response->withJson([
            'ret' => 1,
            'msg' => 'success',
        ]);
    }

    /**
     * @throws Exception
     */
    public function createRecurrence(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $configs = Config::getClass('billing');
        $cryptomus_merchant_uuid = $configs['cryptomus_merchant_uuid'];
        $cryptomus_payment_key = $configs['cryptomus_payment_key'];

        $data = [
            "amount" => "15",
            "currency" => "USD",
            "name" => "Recurring payment",
            "period" => "monthly"
        ];

        $requestBuilder = new \Cryptomus\Api\RequestBuilder($cryptomus_payment_key, $cryptomus_merchant_uuid);

        $result = $requestBuilder->sendRequest('v1' . '/recurrence/create', $data);

        $recurrence = new Recurrence();
        $recurrence->uuid = $result["uuid"];
        $recurrence->user_id = $this->user->id;
        $recurrence->amount = 10;
        $recurrence->status = $result["status"];
        $recurrence->period = $result["period"];
        $recurrence->message = json_encode($result);
        $recurrence->save();

        return $response->write(
            json_encode($result)
        );
    }

    /**
     * @throws Exception
     */
    public function returnRecurrence(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $configs = Config::getClass('billing');
        $cryptomus_merchant_uuid = $configs['cryptomus_merchant_uuid'];
        $cryptomus_payment_key = $configs['cryptomus_payment_key'];

        $data = [
            "uuid" => "f5845f32-b5c8-410a-a2a9-37e4d1467d6b",
        ];

        $requestBuilder = new \Cryptomus\Api\RequestBuilder($cryptomus_payment_key, $cryptomus_merchant_uuid);

        $result = $requestBuilder->sendRequest('v1' . '/recurrence/info', $data);

        return $response->write(
            json_encode($result)
        );
    }

    /**
     * @throws Exception
     */
    public function createTopUp(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $amount = $this->antiXss->xss_clean($request->getParam('topup_amount'));
        $payment_method = $this->antiXss->xss_clean($request->getParam('paymentSelect'));

        if ($amount < 1) {
            return $response->withJson([
                'ret' => 0,
                'msg' => '非法的金额',
            ]);
        }

        // create paylist
        $pl = new Paylist();
        $pl->userid = $this->user->id;
        $pl->total = $amount;
        $pl->invoice_id = 0;
        $pl->tradeno = Tools::genRandomChar();
        $pl->gateway = "Cryptomus";
        $pl->save();

        if ($payment_method === "crypto" || $payment_method === "usdt") {
            $configs = Config::getClass('billing');
            $cryptomus_merchant_uuid = $configs['cryptomus_merchant_uuid'];
            $cryptomus_payment_key = $configs['cryptomus_payment_key'];

            $payment = \Cryptomus\Api\Client::payment($cryptomus_payment_key, $cryptomus_merchant_uuid);

            $data = [
                'amount' => $amount,
                'currency' => 'USD',
                'order_id' => $pl->tradeno,
                'url_return' => $_ENV['baseUrl'] . '/user/billing/topup/return' . "?trade_no=" . $pl->tradeno,
                'url_callback' => $_ENV['baseUrl'] . '/user/billing/topup/return' . "?trade_no=" . $pl->tradeno,
                'is_payment_multiple' => false,
                'lifetime' => '3600',
            ];

            if ($payment_method === "usdt") {
                $data['network'] = "POLYGON";
                $data['currency'] = "USDT";
            }

            $result = $payment->create($data);

            return $response->withRedirect($result["url"]);
        }

        return $response->write(
            $amount . $payment_method
        );
    }


    /**
     * @throws Exception
     */
    public function returnTopUp(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $trade_no = $this->antiXss->xss_clean($request->getParam('trade_no'));
        $data = ["order_id" => $trade_no];

        $configs = Config::getClass('billing');
        $cryptomus_merchant_uuid = $configs['cryptomus_merchant_uuid'];
        $cryptomus_payment_key = $configs['cryptomus_payment_key'];

        $payment = \Cryptomus\Api\Client::payment($cryptomus_payment_key, $cryptomus_merchant_uuid);

        $result = $payment->info($data);
        if ($result["payment_status"] == "paid" || $result["payment_status"] == "paid_over") {
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
                    "top-up"
                );
            }
        }

        return $response->withRedirect($_ENV['baseUrl'] . '/user/billing');
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
    public function devices(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $userDevices = DeviceService::getUserDeviceList($this->user->id);

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
            $result = DeviceService::activateUserDevice($this->user->id, $device_id);
            return $response->write(
                $this->view()
                    ->assign('user_devices', $result)
                    ->fetch('user/mole/component/devices/devices_list.tpl')
            );
        }
        return $response->write(
            $this->view()
                ->assign('user_devices', DeviceService::getUserDeviceList($this->user->id))
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
            $result = DeviceService::deactivatedUserDevice($this->user->id, $device_id);
            return $response->write(
                $this->view()
                    ->assign('user_devices', $result)
                    ->fetch('user/mole/component/devices/devices_list.tpl')
            );
        }
        return $response->write(
            $this->view()
                ->assign('user_devices', DeviceService::getUserDeviceList($this->user->id))
                ->fetch('user/mole/component/devices/devices_list.tpl')
        );
    }

    /**
     * @throws Exception
     */
    public function remove_device(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $device_id = $args['id'];
        $result = DeviceService::removeDeviceFromUser($this->user->id, $device_id);
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
     * @throws Exception
     */
    public function account(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        return $response->write(
            $this->view()->assign('data', MockData::getData())->fetch('user/mole/account.tpl')
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
}
