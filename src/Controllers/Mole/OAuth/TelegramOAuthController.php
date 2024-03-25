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

final class TelegramOAuthController extends BaseController
{

    public function callback(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $configs = Config::getClass('feature');
        $token = $configs['telegram_oauth_id'] . ":" . $configs['telegram_oauth_token'];

        $data = $request->getParsedBody();
        if ($data["event"] != "auth_result") {
            return $response->write("error");
        }

        $auth_data = $data["result"];
        $check_hash = $auth_data['hash'];
        unset($auth_data['hash']);
        $data_check_arr = [];
        foreach ($auth_data as $key => $value) {
            $data_check_arr[] = $key . '=' . $value;
        }
        sort($data_check_arr);
        $data_check_string = implode("\n", $data_check_arr);
        $secret_key = hash('sha256', $token, true);
        $hash = hash_hmac('sha256', $data_check_string, $secret_key);

        if (strcmp($hash, $check_hash) !== 0) {
            return $response->write('Data is NOT from Telegram!');
        }
        if ((time() - $auth_data['auth_date']) > 86400) {
            return $response->write('Data is outdated!');
        }

        // login into account
        $time = 86400 * ($_ENV['rememberMeDuration'] ?: 7);

        $user = (new User())->where("telegram_id", $auth_data["id"])->first();
        if ($user !== null) {
            Auth::login($user->id, $time);

            return $response->withJson(
                [
                    "status" => 1
                ]
            );
        }

        // create account
        $user = new User();
        $configs = Config::getClass('reg');

        $user->user_name = "";
        $user->setUserEmail("");
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
        $user->contact_method = 2;
        $random_group = Config::obtain('random_group');

        if ($random_group === '') {
            $user->node_group = 0;
        } else {
            $user->node_group = $random_group[array_rand(explode(',', $random_group))];
        }
        $user->telegram_id = $auth_data["id"];
        $user->telegram_username = $auth_data["username"];

        $user->save();

        Auth::login($user->id, $time);

        return $response->withJson(
            [
                "status" => 1
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function addTelegramOauth(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $configs = Config::getClass('feature');
        $token = $configs['telegram_oauth_id'] . ":" . $configs['telegram_oauth_token'];

        $data = $request->getParsedBody();
        if ($data["event"] != "auth_result") {
            return $response->write("error");
        }

        $auth_data = $data["result"];
        $check_hash = $auth_data['hash'];
        unset($auth_data['hash']);
        $data_check_arr = [];
        foreach ($auth_data as $key => $value) {
            $data_check_arr[] = $key . '=' . $value;
        }
        sort($data_check_arr);
        $data_check_string = implode("\n", $data_check_arr);
        $secret_key = hash('sha256', $token, true);
        $hash = hash_hmac('sha256', $data_check_string, $secret_key);

        if (strcmp($hash, $check_hash) !== 0) {
            return $response->write('Data is NOT from Telegram!');
        }
        if ((time() - $auth_data['auth_date']) > 86400) {
            return $response->write('Data is outdated!');
        }

        $this->user->telegram_id = $auth_data["id"];
        $this->user->telegram_username = $auth_data["username"];
        $this->user->save();

        return $response->withJson(
            [
                "status" => 1
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function removeTelegramOauth(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        // remove telegram oauth info
        $this->user->telegram_id = "";
        $this->user->telegram_username = "";
        $this->user->save();
        return $response->withHeader('HX-Refresh', 'true');
    }

}
