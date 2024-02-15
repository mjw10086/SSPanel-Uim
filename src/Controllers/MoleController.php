<?php

declare(strict_types=1);

namespace App\Controllers;

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

    private function createOrder($product_id)
    {
        $product = (new Product())->find($product_id);

        if ($product === null || $product->stock === 0) {
            return false;
        }

        $buy_price = $product->price;
        $user = $this->user;

        if ($user->is_shadow_banned) {
            return false;
        }

        $product_limit = json_decode($product->limit);

        if ($product_limit->class_required !== '' && $user->class < (int) $product_limit->class_required) {
            return false;
        }

        if (
            $product_limit->node_group_required !== ''
            && $user->node_group !== (int) $product_limit->node_group_required
        ) {
            return false;
        }

        if ($product_limit->new_user_required !== 0) {
            $order_count = (new Order())->where('user_id', $user->id)->count();
            if ($order_count > 0) {
                return false;
            }
        }

        $order = new Order();
        $order->user_id = $user->id;
        $order->product_id = $product->id;
        $order->product_type = $product->type;
        $order->product_name = $product->name;
        $order->product_content = $product->content;
        $order->coupon = '';
        $order->price = $buy_price;
        $order->status = 'pending_payment';
        $order->create_time = time();
        $order->update_time = time();
        $order->save();

        $invoice_content = [];

        $invoice_content[] = [
            'content_id' => 0,
            'name' => $product->name,
            'price' => $product->price,
        ];


        $invoice = new Invoice();
        $invoice->user_id = $user->id;
        $invoice->order_id = $order->id;
        $invoice->content = json_encode($invoice_content);
        $invoice->price = $buy_price;
        $invoice->status = 'unpaid';
        $invoice->create_time = time();
        $invoice->update_time = time();
        $invoice->pay_time = 0;
        $invoice->save();

        if ($product->stock > 0) {
            $product->stock -= 1;
        }
        $product->sale_count += 1;
        $product->save();

        return $invoice->id;
    }

    private function purchaseWithBalance($invoice_id)
    {
        $invoice = (new Invoice())->where('user_id', $this->user->id)->where('id', $invoice_id)->first();

        if ($invoice === null) {
            return false;
        }

        $user = $this->user;

        if ($user->is_shadow_banned) {
            return false;
        }

        if ($user->money < $invoice->price) {
            return false;
        }

        $money_before = $user->money;
        $user->money -= $invoice->price;
        $user->save();

        (new UserMoneyLog())->add(
            $user->id,
            $money_before,
            (float) $user->money,
            -$invoice->price,
            '支付账单 #' . $invoice->id,
            "order_payment"
        );

        $invoice->status = 'paid_balance';
        $invoice->update_time = time();
        $invoice->pay_time = time();
        $invoice->save();

        $this->processPendingOrder();
        $this->processTabpOrderActivation();

        return true;
    }

    public function processPendingOrder(): void
    {
        $pending_payment_orders = (new Order())->where('status', 'pending_payment')->get();

        foreach ($pending_payment_orders as $order) {
            // 检查账单支付状态
            $invoice = (new Invoice())->where('order_id', $order->id)->first();

            if ($invoice === null) {
                continue;
            }
            // 标记订单为等待激活
            if (in_array($invoice->status, ['paid_gateway', 'paid_balance', 'paid_admin'])) {
                $order->status = 'pending_activation';
                $order->update_time = time();
                $order->save();

                continue;
            }
            // 取消超时未支付的订单和关联账单
            if ($order->create_time + 86400 < time()) {
                $order->status = 'cancelled';
                $order->update_time = time();
                $order->save();

                $invoice->status = 'cancelled';
                $invoice->update_time = time();
                $invoice->save();

            }
        }
    }

    public function processTabpOrderActivation(): void
    {
        $user = $this->user;

        $user_id = $user->id;
        // 获取用户账户等待激活的TABP订单
        $pending_activation_orders = (new Order())->where('user_id', $user_id)
            ->where('status', 'pending_activation')
            ->where('product_type', 'tabp')
            ->orderBy('id')
            ->get();
        // 获取用户账户已激活的TABP订单，一个用户同时只能有一个已激活的TABP订单
        $activated_order = (new Order())->where('user_id', $user_id)
            ->where('status', 'activated')
            ->where('product_type', 'tabp')
            ->orderBy('id')
            ->first();
        // 如果用户账户中没有已激活的TABP订单，且有等待激活的TABP订单，则激活最早的等待激活TABP订单
        if ($activated_order === null && count($pending_activation_orders) > 0) {
            $order = $pending_activation_orders[0];
            // 获取TABP订单内容准备激活
            $content = json_decode($order->product_content);
            // 激活TABP
            $user->u = 0;
            $user->d = 0;
            $user->transfer_today = 0;
            $user->transfer_enable = Tools::toGB($content->bandwidth);
            $user->class = $content->class;
            $old_class_expire = new DateTime();
            $user->class_expire = $old_class_expire
                ->modify('+' . $content->class_time . ' days')->format('Y-m-d H:i:s');
            $user->node_group = $content->node_group;
            $user->node_speedlimit = $content->speed_limit;
            $user->node_iplimit = $content->ip_limit;
            if ($user->plan_start_date === null) {
                $user->plan_start_date = new DateTime();
            }
            $user->save();
            $order->status = 'activated';
            $order->update_time = time();
            $order->save();
        }
        // 如果用户账户中有已激活的TABP订单，则判断是否过期
        if ($activated_order !== null) {
            $content = json_decode($activated_order->product_content);

            if ($activated_order->update_time + $content->time * 86400 < time()) {
                $activated_order->status = 'expired';
                $activated_order->update_time = time();
                $activated_order->save();
            }
        }
    }

    /**
     * @throws Exception
     */
    public function createTopUp(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $amount = $this->antiXss->xss_clean($request->getParam('topup_amount'));
        $payment_method = $this->antiXss->xss_clean($request->getParam('paymentSelect'));

        if ($amount <= 0) {
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

        // create order
        $result = $this->createOrder($product_id);
        if ($result === false) {
            return $response->write(
                $this->view()
                    ->fetch('user/mole/component/plan/purchase_error_occur.tpl')
            );
        }

        // pay with balance
        $res = $this->purchaseWithBalance($result);
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
