<?php

declare(strict_types=1);

namespace App\Controllers\Mole\OAuth;

use App\Controllers\BaseController;
use App\Models\Config;
use App\Models\User;
use App\Services\Auth;
use App\Services\MFA;
use App\Utils\Tools;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

final class GoogleOAuthController extends BaseController
{
    private function getToken($code, $redirect_uri)
    {
        $configs = Config::getClass('feature');
        $url = 'https://accounts.google.com/o/oauth2/token';

        $data = array(
            'code' => $code,
            'client_id' => $configs["google_oauth_client_id"],
            'client_secret' => $configs["google_oauth_client_secret"],
            'redirect_uri' => $redirect_uri,
            'grant_type' => 'authorization_code'
        );

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));

        if ($configs["server_proxy"] !== "") {
            curl_setopt($curl, CURLOPT_PROXY, $configs["server_proxy"]);
        }

        $response = curl_exec($curl);

        if ($response === false) {
            curl_close($curl);
            return false;
        }

        curl_close($curl);
        return $response;
    }

    private function getUserProfile($token)
    {
        $configs = Config::getClass('feature');
        $apiEndpoint = 'https://www.googleapis.com/oauth2/v1/userinfo';

        $headers = [
            'Authorization: Bearer ' . $token,
            'Accept: application/json'
        ];

        $curl = curl_init();

        // Set cURL options
        curl_setopt_array($curl, [
            CURLOPT_URL => $apiEndpoint,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        if ($configs["server_proxy"] !== "") {
            curl_setopt($curl, CURLOPT_PROXY, $configs["server_proxy"]);
        }

        $response = curl_exec($curl);

        // Check if request was successful
        if ($response === false) {
            curl_close($curl);
            return false;
        }

        curl_close($curl);
        return $response;
    }

    public function callback(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $code = $this->antiXss->xss_clean($request->getQueryParams()['code']);

        $result = $this->getToken($code, $_ENV['baseUrl'] . '/oauth/callback/google');
        $token = json_decode($result, true);
        $user_profile_str = $this->getUserProfile($token["access_token"]);

        $user_profile = json_decode($user_profile_str, true);

        // login into account
        $time = 86400 * ($_ENV['rememberMeDuration'] ?: 7);

        $user = (new User())->where("google_id", $user_profile["id"])->first();

        if ($user !== null) {
            Auth::login($user->id, $time);

            return $response->write(
                $this->view()
                    ->assign("message", '{"oauth": "google", "status": "success"}')
                    ->fetch('user/mole/google-oauth-callback.tpl')
            );
        }

        // if user email has exist
        $exist_user = (new User())->where("email", $user_profile["email"])->where("google_id", "")->first();
        if ($exist_user !== null) {
            return $response->write(
                $this->view()
                    ->assign("message", '{"oauth": "google", "status": "duplicate"}')
                    ->fetch('user/mole/google-oauth-callback.tpl')
            );
        }

        // create account
        $user = new User();
        $configs = Config::getClass('reg');

        $user->user_name = "";
        $user->setUserEmail($user_profile["email"]);
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
        $user->contact_method = 1;
        $random_group = Config::obtain('random_group');

        if ($random_group === '') {
            $user->node_group = 0;
        } else {
            $user->node_group = $random_group[array_rand(explode(',', $random_group))];
        }
        $user->google_id = $user_profile["id"];
        $user->google_username = $user_profile["name"];

        $user->save();


        return $response->write(
            $this->view()
                ->assign("message", '{"oauth": "google", "status": "success"}')
                ->fetch('user/mole/google-oauth-callback.tpl')
        );
    }


    /**
     * @throws Exception
     */
    public function addGoogleOauth(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $code = $this->antiXss->xss_clean($request->getQueryParams()['code']);

        $result = $this->getToken($code, $_ENV['baseUrl'] . '/user/account/oauth/google');

        $token = json_decode($result, true);
        $user_profile_str = $this->getUserProfile($token["access_token"]);

        $user_profile = json_decode($user_profile_str, true);

        $exist_user = (new User())->where("google_id", $user_profile["id"])->first();
        if($exist_user !== null){
            return $response->write("error");
        }

        $this->user->google_id = $user_profile["id"];
        $this->user->google_username = $user_profile["name"];
        $this->user->save();

        return $response->write(
            $this->view()
                ->assign("message", '{"oauth": "google", "status": "success"}')
                ->fetch('user/mole/google-oauth-callback.tpl')
        );
    }


    /**
     * @throws Exception
     */
    public function removeGoogleOauth(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        // remove google oauth info
        $this->user->google_id = "";
        $this->user->google_username = "";
        $this->user->save();

        return $response->withHeader('HX-Refresh', 'true');
    }

    /**
     * @throws Exception
     */
    public function initPurchaseOauthCallback(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $code = $this->antiXss->xss_clean($request->getQueryParams()['code']);

        $result = $this->getToken($code, $_ENV['baseUrl'] . '/init-purchase/oauth/callback/google');
        $token = json_decode($result, true);
        $user_profile_str = $this->getUserProfile($token["access_token"]);

        $user_profile = json_decode($user_profile_str, true);

        $exist_user = (new User())->where("email", $user_profile["email"])->where("google_id", "")->first();
        if ($exist_user !== null) {
            return $response->write(
                $this->view()
                    ->assign("message", '{"oauth": "google", "status": "duplicate"}')
                    ->fetch('user/mole/google-oauth-callback.tpl')
            );
        }

        $res = [
            "id" => $user_profile["id"],
            "name" => $user_profile["name"],
            "email" => $user_profile["email"]
        ];

        $configs = Config::getClass('feature');
        $token = $configs["google_oauth_client_secret"];
        $checksum = hash('sha256', json_encode($res) . $token);
        $res["validation"] = $checksum;

        return $response->write(
            $this->view()
                ->assign("message", '{"status": "success","oauth": "google", "data":' . json_encode($res) . '}')
                ->fetch('user/mole/google-oauth-callback.tpl')
        );
    }

}
