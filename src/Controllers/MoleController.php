<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Purchase;
use DateTime;
use App\Models\Ann;
use App\Models\Config;
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
        $billing_history = (new Order())->where('user_id', $this->user->id)->where('status', 'activated')->orderBy('update_time', 'desc')->get();
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
