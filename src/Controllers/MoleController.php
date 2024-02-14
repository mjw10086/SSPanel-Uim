<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Ann;
use App\Models\Config;
use App\Models\InviteCode;
use App\Models\LoginIp;
use App\Models\Node;
use App\Models\OnlineLog;
use App\Models\Order;
use App\Models\Payback;
use App\Models\Device;
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

        return $response->write(
            $this->view()
                ->assign('data', MockData::getData())
                ->assign('user_devices', $userDevices)
                ->assign('available_plans', $available_plans)
                ->assign('activated_order', $activated_order)
                ->assign('data_usage', $data_usage)
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

        return true;
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
