<?php

declare(strict_types=1);

namespace App\Services\Gateway;

use App\Models\Config;
use App\Models\Paylist;
use App\Services\Auth;
use App\Services\Exchange;
use App\Services\View;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use RedisException;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Throwable;
use voku\helper\AntiXSS;

final class Cryptomus extends Base
{
    protected array $gateway_config;

    public function __construct()
    {
        $this->antiXss = new AntiXSS();
        $configs = Config::getClass('billing');
        $cryptomus_merchant_uuid = $configs['cryptomus_merchant_uuid'];
        $cryptomus_payment_key = $configs['cryptomus_payment_key'];

        $this->payment = \Cryptomus\Api\Client::payment($cryptomus_payment_key, $cryptomus_merchant_uuid);
    }

    public static function _name(): string
    {
        return 'cryptomus';
    }

    public static function _enable(): bool
    {
        return self::getActiveGateway('cryptomus');
    }

    public static function _readableName(): string
    {
        return 'Cryptomus';
    }

    // redirect to purchase page
    public function purchase(ServerRequest $request, Response $response, array $args): ResponseInterface
    {
        $price = $this->antiXss->xss_clean($request->getParam('price'));
        $invoice_id = $this->antiXss->xss_clean($request->getParam('invoice_id'));
        $paylist = (new Paylist())->where('invoice_id', $invoice_id)->first();
        $trade_no = (empty($paylist)) ? self::generateGuid() : $paylist->tradeno;

        if ($price <= 0) {
            return $response->withJson([
                'ret' => 0,
                'msg' => '非法的金额',
            ]);
        }

        // create order in cryptomus
        $data = [
            'amount' => $price,
            'currency' => 'USD',
            'order_id' => $trade_no,
            'url_return' => $_ENV['baseUrl'] . '/user/invoice/' . $invoice_id . '/view',
            'url_callback' => self::getUserReturnUrl() . "?trade_no=" . $trade_no . "&order_id=" . $invoice_id,
            'is_payment_multiple' => false,
            'lifetime' => '3600',
        ];

        try {
            $result = $this->payment->create($data);
        } catch (RequestBuilderException $e) {
            return $response->withJson([
                'ret' => 0,
                'msg' => 'Error occuren when Cryptomus creates order',
            ]);
        }

        // create paylist in system
        $paylist = (new Paylist())->where('invoice_id', $invoice_id)->first();
        if (empty($paylist)) {
            $user = Auth::getUser();
            $pl = new Paylist();
            $pl->userid = $user->id;
            $pl->total = $price;
            $pl->invoice_id = $invoice_id;
            $pl->tradeno = $trade_no;
            $pl->gateway = self::_readableName();
            $pl->save();
        }

        // return cryptomus order payment info
        $data = ["order_id" => $trade_no];

        try {
            $result = $this->payment->info($data);
            return $response->withRedirect($result["url"]);
        } catch (RequestFailedException $e) {
            return $response->withJson([
                'ret' => 0,
                'msg' => 'Cryptomus API error',
            ]);
        }
    }

    public function notify($request, $response, $args): ResponseInterface
    {
        return $response->write('ok');
    }

    public function getReturnHTML($request, $response, $args): ResponseInterface
    {
        $trade_no = $this->antiXss->xss_clean($request->getParam('trade_no'));
        $system_order_id = $this->antiXss->xss_clean($request->getParam('order_id'));
        $data = ["order_id" => $trade_no];

        try {
            $result = $this->payment->info($data);
            if ($result["payment_status"] == "paid" || $result["payment_status"] == "paid_over") {
                $this->postPayment($trade_no);
            }
        } catch (Exception $e) {
            return $response->withJson([
                'ret' => 0,
                'msg' => '支付失败',
            ]);
        }

        return $response->withRedirect($_ENV['baseUrl'] . '/user/invoice/' . $system_order_id . '/view');
    }

    public static function getPurchaseHTML(): string
    {
        return View::getSmarty()->fetch('gateway/cryptomus.tpl');
    }
}
