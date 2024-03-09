<?php

declare(strict_types=1);

namespace App\Controllers\Mole;

use App\Controllers\BaseController;
use App\Models\Recurrence;
use App\Models\Withdraw;
use App\Models\Config;
use App\Models\User;
use App\Models\Order;
use App\Models\Paylist;
use App\Models\Invoice;
use App\Models\UserMoneyLog;
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

        $current_recurrence = (new Recurrence())->where('user_id', $this->user->id)
            ->where('status', 'activate')->first();

        return $response->write(
            $this->view()
                ->assign('current_recurrence', $current_recurrence)
                ->assign('billing_history', $billing_history)
                ->assign('balance_history', $balance_history)
                ->assign('expected_suffice_till', $expected_suffice_till)
                ->fetch('user/mole/billing.tpl')
        );
    }

    /**
     * @throws Exception
     */
    public function getCryptomusNetworkList(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $configs = Config::getClass('billing');
        $cryptomus_merchant_uuid = $configs['cryptomus_merchant_uuid'];
        $cryptomus_payout_key = $configs['cryptomus_payout_key'];

        $requestBuilder = new \Cryptomus\Api\RequestBuilder($cryptomus_payout_key, $cryptomus_merchant_uuid);

        $list = $requestBuilder->sendRequest('v1' . '/payout/services');

        return $response->write(
            $this->view()
                ->assign("list", $list)
                ->fetch('user/mole/component/billing/cryptomus_service_list.tpl')
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
            // 'url_callback' => "http://echo.connexusy.com",
            'url_callback' => $_ENV['baseUrl'] . '/user/billing/withdraw/return',
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
        $withdraw->message = json_encode($result);

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

        $data = [
            "amount" => "1",
            "currency" => "USD",
            "name" => "Ironlink recurring payment",
            "period" => "monthly",
            // "url_callback" => "http://echo.connexusy.com"
            'url_callback' => $_ENV['baseUrl'] . '/user/billing/recurrence/return',
        ];

        $requestBuilder = new \Cryptomus\Api\RequestBuilder($cryptomus_payment_key, $cryptomus_merchant_uuid);

        $result = $requestBuilder->sendRequest('v1' . '/recurrence/create', $data);

        $recurrence = new Recurrence();
        $recurrence->type = "crypto";
        $recurrence->uuid = $result["uuid"];
        $recurrence->user_id = $this->user->id;
        $recurrence->amount = 10;
        $recurrence->status = $result["status"];
        $recurrence->period = $result["period"];
        $recurrence->create_message = $result["period"];
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
            "uuid" => $recurrenceRecord->uuid,
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
        $uuid = $this->antiXss->xss_clean($request->getParam('uuid'));
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
            $user = (new User())->where("id", $recurrenceRecord->user_id);

            (new UserMoneyLog())->add(
                $user->id,
                $user->money,
                (float) $user->money + $recurrenceRecord->amount,
                (float) $recurrenceRecord->amount,
                '订阅 #' . $recurrenceRecord->uuid,
                "recurrence"
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
                'url_callback' => $_ENV['baseUrl'] . '/user/billing/topup/return' . "?trade_no=" . $pl->tradeno,
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

}
