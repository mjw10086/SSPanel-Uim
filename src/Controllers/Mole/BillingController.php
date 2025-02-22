<?php

declare(strict_types=1);

namespace App\Controllers\Mole;

use App\Controllers\BaseController;
use App\Models\Product;
use App\Models\Recurrence;
use App\Models\Withdraw;
use App\Models\Config;
use App\Models\User;
use App\Models\Order;
use App\Models\Paylist;
use App\Models\Invoice;
use App\Models\UserMoneyLog;
use App\Services\Notification;
use App\Utils\Tools;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use function strtotime;
use function time;

final class BillingController extends BaseController
{
    /**
     * @throws Exception
     */
    public function billing(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $billing_history = (new UserMoneyLog())->where('user_id', $this->user->id)->whereIn('type', ['pay with balance', 'plan cancel'])->orderBy('create_time', 'desc')->get();

        $balance_history = (new UserMoneyLog())->where('user_id', $this->user->id)->whereIn('type', ['admin', 'card manual', 'card recurring', 'crypto manual', 'crypto recurring', 'withdraw', 'withdraw failed'])->orderBy('create_time', 'desc')->get();

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

        $current_recurrence = (new Recurrence())->where('user_id', $this->user->id)
            ->where('status', 'activate')->first();

        $notifications = Notification::fetchUserNotificationInSystem($this->user);

        return $response->write(
            $this->view()
                ->assign('notifications', $notifications)
                ->assign('current_recurrence', $current_recurrence)
                ->assign('billing_history', $billing_history)
                ->assign('balance_history', $balance_history)
                ->assign('expected_suffice_till', $expected_suffice_till)
                ->fetch('user/mole/billing.tpl')
        );
    }

    //=========================================
    //  Withdraw
    //=========================================

    /**
     * @throws Exception
     */
    public function createWithdraw(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        // read paramter
        $amount = $this->antiXss->xss_clean($request->getParam('amount'));
        $address = $this->antiXss->xss_clean($request->getParam('address'));
        $network = $this->antiXss->xss_clean($request->getParam('network'));

        // check amount
        if ($amount < 1 && $this->user->money < $amount) {
            return $response->withJson([
                'ret' => 0,
                'msg' => '非法的金额',
            ]);
        }

        // create money log
        (new UserMoneyLog())->add(
            $this->user->id,
            $this->user->money,
            (float) $this->user->money - $amount,
            (float) -$amount,
            '提款 #' . $address,
            "withdraw"
        );

        // change user balance
        $this->user->money = $this->user->money - $amount;

        // create withdraw proposal
        $withdraw = new Withdraw();
        $withdraw->user_id = $this->user->id;
        $withdraw->transfer_id = '';
        $withdraw->type = 'cryptomus';
        $withdraw->amount = $amount;
        $withdraw->status = 'pending';
        $withdraw->to_account = $address;
        $withdraw->addition_msg = $network;
        $withdraw->note = '';
        $withdraw->save();

        // return notification
        $message = "Your withdraw is in progress";

        return $response->write(
            $this->view()
                ->assign("status", 'pending')
                ->assign("message", $message)
                ->fetch('user/mole/component/billing/withdraw_result.tpl')
        );
    }


    //=========================================
    //  Recurrence
    //=========================================

    /**
     * @throws Exception
     */
    public function createRecurrence(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $recurrenceRecord = (new Recurrence())->where("user_id", $this->user->id)->where("status", "activate")->first();
        if ($recurrenceRecord !== null) {
            return $response->withJson([
                'ret' => 0,
                'msg' => 'You have an recurrence already',
            ]);
        }

        $paymentSelect = $this->antiXss->xss_clean($request->getParam('paymentSelect'));

        if ($paymentSelect == "card") {
            return $response->withJson([
                'ret' => 0,
                'msg' => '暂不支持',
            ]);
        }

        $configs = Config::getClass('billing');
        $cryptomus_merchant_uuid = $configs['cryptomus_merchant_uuid'];
        $cryptomus_payment_key = $configs['cryptomus_payment_key'];
        $uuid = Tools::genRandomChar();

        $activated_order = (new Order())
            ->where('user_id', $this->user->id)
            ->where('status', 'activated')
            ->where('product_type', 'tabp')
            ->first();

        if($activated_order == null){
            return $response->write("no activated plan");
        }

        $product = (new Product())->where("id", $activated_order->product_id)->first();

        $data = [
            "amount" => (string)($product->price),
            "currency" => "USD",
            "order_id" => $uuid,
            "name" => "Ironlink recurring payment",
            "period" => "monthly",
            // "url_callback" => "http://echo.connexusy.com",
            'url_callback' => $_ENV['baseUrl'] . '/user/billing/recurrence/return',
        ];

        $requestBuilder = new \Cryptomus\Api\RequestBuilder($cryptomus_payment_key, $cryptomus_merchant_uuid);

        $result = $requestBuilder->sendRequest('v1' . '/recurrence/create', $data);

        $recurrence = new Recurrence();
        $recurrence->type = "crypto";
        $recurrence->uuid = $uuid;
        $recurrence->user_id = $this->user->id;
        $recurrence->amount = 1;
        $recurrence->status = $result["status"];
        $recurrence->period = $result["period"];
        $recurrence->create_message = json_encode($result);
        $recurrence->message = "";
        $recurrence->save();

        return $response->withRedirect($result["url"]);
    }

    /**
     * @throws Exception
     */
    public function cancelRecurrence(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $recurrenceRecord = (new Recurrence())->where("user_id", $this->user->id)->where("status", "activate")->first();
        if ($recurrenceRecord == null) {
            return $response->withJson([
                'ret' => 0,
                'msg' => 'no recurrence activate',
            ]);
        }

        $configs = Config::getClass('billing');
        $cryptomus_merchant_uuid = $configs['cryptomus_merchant_uuid'];
        $cryptomus_payment_key = $configs['cryptomus_payment_key'];

        $data = [
            "order_id" => $recurrenceRecord->uuid,
        ];

        $result = null;

        try {
            $requestBuilder = new \Cryptomus\Api\RequestBuilder($cryptomus_payment_key, $cryptomus_merchant_uuid);
            $result = $requestBuilder->sendRequest('v1' . '/recurrence/cancel', $data);
        } catch (\Cryptomus\Api\RequestBuilderException $e) {
            if ($e->getMessage() !== "Already canceled") {
                return $response->withJson([
                    'ret' => 0,
                    'msg' => $e->getMessage(),
                ]);
            }
        }

        if ($result === null || $result["status"] === "cancel_by_merchant") {
            $recurrenceRecord->status = "canceled";
            $recurrenceRecord->save();
        }

        return $response->withHeader('HX-Refresh', 'true');
    }

    /**
     * @throws Exception
     */
    public function returnRecurrence(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $configs = Config::getClass('billing');
        $cryptomus_payment_key = $configs['cryptomus_payment_key'];

        // read paramter
        $uuid = $this->antiXss->xss_clean($request->getParam('order_id'));
        $status = $this->antiXss->xss_clean($request->getParam('status'));

        // check request validity
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

        // get recurrence record
        $recurrenceRecord = (new Recurrence())->where("uuid", $uuid)->first();
        if ($recurrenceRecord === null) {
            return $response->withJson([
                'ret' => 0,
                'msg' => 'no such recurrence record',
            ]);
        }

        // check status
        if ($status == "paid") {
            // back money to user balance
            $user = (new User())->where("id", $recurrenceRecord->user_id)->first();

            (new UserMoneyLog())->add(
                $user->id,
                $user->money,
                (float) $user->money + $recurrenceRecord->amount,
                (float) $recurrenceRecord->amount,
                '订阅 #' . $recurrenceRecord->uuid,
                "crypto recurring"
            );

            $user->money = $user->money + $recurrenceRecord->amount;
            $user->save();

            // update recurrence record
            $recurrenceRecord->status = "activate";
            $recurrenceRecord->message = json_encode($body);

            $recurrenceRecord->save();
        }

        return $response->withJson([
            'ret' => 1,
            'msg' => 'success',
        ]);
    }


    //=========================================
    //  Topup
    //=========================================

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
                'url_return' => $_ENV['baseUrl'] . '/user/billing/topup/check' . "?trade_no=" . $pl->tradeno,
                'url_callback' => $_ENV['baseUrl'] . '/user/billing/topup/return',
                'url_success' => $_ENV['baseUrl'] . '/user/billing/topup/check' . "?trade_no=" . $pl->tradeno,
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
            $amount . $payment_method
        );
    }


    /**
     * @throws Exception
     */
    public function returnTopUp(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $configs = Config::getClass('billing');
        $cryptomus_payment_key = $configs['cryptomus_payment_key'];

        // read paramter
        $trade_no = $this->antiXss->xss_clean($request->getParam('order_id'));
        $status = $this->antiXss->xss_clean($request->getParam('status'));

        // read paramter
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

        return $response->withJson([
            'ret' => 1,
            'msg' => 'success',
        ]);
    }


    /**
     * @throws Exception
     */
    public function checkTopUp(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        // (to update)
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

                (new UserMoneyLog())->add(
                    $this->user->id,
                    $this->user->money,
                    (float) $this->user->money + $paylist->total,
                    (float) $paylist->total,
                    '充值 #' . $trade_no,
                    "crypto manual"
                );

                $this->user->money = $this->user->money + $paylist->total;
                $this->user->save();
            }
        }

        return $response->withRedirect($_ENV['baseUrl'] . '/user/billing');
    }

}
