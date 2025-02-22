<?php

declare(strict_types=1);

namespace App\Controllers\Mole;

use App\Controllers\BaseController;
use App\Models\Config;
use App\Models\InviteCode;
use App\Models\LoginIp;
use App\Models\User;
use App\Services\Auth;
use App\Services\Cache;
use App\Services\Captcha;
use App\Services\Mail;
use App\Services\MFA;
use App\Services\RateLimit;
use App\Utils\Cookie;
use App\Utils\Hash;
use App\Utils\ResponseHelper;
use App\Utils\Tools;
use Exception;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use RedisException;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use function array_rand;
use function date;
use function explode;
use function strlen;
use function strtolower;
use function time;
use function trim;

final class AuthController extends BaseController
{
    /**
     * @throws Exception
     */
    public function login(ServerRequest $request, Response $response, array $args): ResponseInterface
    {
        $configs = Config::getClass('feature');
        $captcha = [];

        if (Config::obtain('enable_login_captcha')) {
            $captcha = Captcha::generate();
        }

        $uuid = Uuid::uuid4();
        $telegram_id = $configs['telegram_oauth_id'];
        $google_client_id = $configs['google_oauth_client_id'];


        return $response->write($this->view()
            ->assign('base_url', $_ENV['baseUrl'])
            ->assign('uuid', $uuid)
            ->assign('telegram_id', $telegram_id)
            ->assign('google_client_id', $google_client_id)
            ->assign('captcha', $captcha)
            ->fetch('user/mole/login.tpl'));
    }

    /**
     * @param ServerRequest $request
     * @param Response $response
     * @param array $args
     *
     * @return Response|ResponseInterface
     */
    public function loginHandle(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        // if (Config::obtain('enable_login_captcha') && ! Captcha::verify($request->getParams())) {
        //     return $response->withJson([
        //         'ret' => 0,
        //         'msg' => '系统无法接受你的验证结果，请刷新页面后重试。',
        //     ]);
        // }

        // $mfa_code = $this->antiXss->xss_clean($request->getParam('mfa_code'));
        $mfa_code = "";
        $passwd = $request->getParam('passwd');
        // $rememberMe = $request->getParam('remember_me') === 'true' ? 1 : 0;
        $rememberMe = 1;
        $email = strtolower(trim($this->antiXss->xss_clean($request->getParam('email'))));
        $redir = $this->antiXss->xss_clean(Cookie::get('redir')) ?? '/user/dashboard';
        $user = (new User())->where('email', $email)->first();
        $loginIp = new LoginIp();

        if ($user === null || !Hash::checkPassword($user->pass, $passwd)) {
            $loginIp->collectLoginIP($_SERVER['REMOTE_ADDR'], 1, $user === null ? 0 : $user->id);

            return $response->write(
                $this->view()
                    ->fetch("user/mole/login-res.tpl")
            );
        }

        // if ($user->ga_enable && (strlen($mfa_code) !== 6 || !MFA::verifyGa($user, $mfa_code))) {
        //     $loginIp->collectLoginIP($_SERVER['REMOTE_ADDR'], 1, $user->id);

        //     return $response->withJson([
        //         'ret' => 0,
        //         'msg' => '两步验证码错误',
        //     ]);
        // }

        $time = 3600;

        if ($rememberMe) {
            $time = 86400 * ($_ENV['rememberMeDuration'] ?: 7);
        }

        Auth::login($user->id, $time);
        // 记录登录成功
        $loginIp->collectLoginIP($_SERVER['REMOTE_ADDR'], 0, $user->id);
        $user->last_login_time = time();
        $user->save();

        return $response->withHeader('HX-Redirect', $redir)->withJson([
            'ret' => 1,
            'msg' => '登录成功',
        ]);
    }

    /**
     * @throws Exception
     */
    public function register(ServerRequest $request, Response $response, $next): Response|ResponseInterface
    {
        $captcha = [];

        if (Config::obtain('enable_reg_captcha')) {
            $captcha = Captcha::generate();
        }

        $invite_code = $this->antiXss->xss_clean($request->getParam('code'));

        return $response->write(
            $this->view()
                ->assign('invite_code', $invite_code)
                ->assign('base_url', $_ENV['baseUrl'])
                ->assign('captcha', $captcha)
                ->fetch('user/mole/register.tpl')
        );
    }

    /**
     * @throws RedisException
     */
    public function sendVerify(ServerRequest $request, Response $response, $next): Response|ResponseInterface
    {
        if (Config::obtain('reg_email_verify')) {
            $email = strtolower(trim($this->antiXss->xss_clean($request->getParam('email'))));

            if ($email === '') {
                return ResponseHelper::error($response, '未填写邮箱');
            }

            // check email format
            $check_res = Tools::isEmailLegal($email);
            if ($check_res['ret'] === 0) {
                return $response->withJson($check_res);
            }

            if (
                !RateLimit::checkEmailIpLimit($request->getServerParam('REMOTE_ADDR')) ||
                !RateLimit::checkEmailAddressLimit($email)
            ) {
                return ResponseHelper::error($response, '你的请求过于频繁，请稍后再试');
            }

            $user = (new User())->where('email', $email)->first();

            if ($user !== null) {
                return ResponseHelper::error($response, '此邮箱已经注册');
            }

            $email_code = Tools::genRandomChar(6);
            $redis = (new Cache())->initRedis();
            $redis->setex('email_verify:' . $email_code, Config::obtain('email_verify_code_ttl'), $email);

            try {
                Mail::send(
                    $email,
                    $_ENV['appName'] . '- 验证邮件',
                    'verify_code.tpl',
                    [
                        'code' => $email_code,
                        'expire' => date('Y-m-d H:i:s', time() + Config::obtain('email_verify_code_ttl')),
                    ]
                );
            } catch (Exception | ClientExceptionInterface $e) {
                return ResponseHelper::error($response, '邮件发送失败，请联系网站管理员。');
            }

            return ResponseHelper::success($response, '验证码发送成功，请查收邮件。');
        }

        return ResponseHelper::error($response, '站点未启用邮件验证');
    }

    /**
     * @param Response $response
     * @param $name
     * @param $email
     * @param $passwd
     * @param $invite_code
     * @param $imtype
     * @param $imvalue
     * @param $money
     * @param $is_admin_reg
     *
     * @return ResponseInterface
     *
     * @throws Exception
     */
    public function registerHelper(
        Response $response,
        $name,
        $email,
        $passwd,
        $imtype,
        $imvalue,
        $money,
        $is_admin_reg
    ): ResponseInterface {
        $redir = $this->antiXss->xss_clean(Cookie::get('redir')) ?? '/user';
        $configs = Config::getClass('reg');
        // do reg user
        $user = new User();

        $user->user_name = $name;
        $user->email = $email;
        $user->remark = '';
        $user->pass = Hash::passwordHash($passwd);
        $user->passwd = Tools::genRandomChar(16);
        $user->uuid = Uuid::uuid4();
        $user->api_token = Uuid::uuid4();
        $user->port = Tools::getSsPort();
        $user->u = 0;
        $user->d = 0;
        $user->method = $configs['sign_up_for_method'];
        $user->forbidden_ip = Config::obtain('reg_forbidden_ip');
        $user->forbidden_port = Config::obtain('reg_forbidden_port');
        $user->im_type = $imtype;
        $user->im_value = $imvalue;
        $user->transfer_enable = Tools::toGB($configs['sign_up_for_free_traffic']);
        $user->invite_num = $configs['sign_up_for_invitation_codes'];
        $user->auto_reset_day = Config::obtain('free_user_reset_day');
        $user->auto_reset_bandwidth = Config::obtain('free_user_reset_bandwidth');
        $user->daily_mail_enable = $configs['sign_up_for_daily_report'];

        if ($money > 0) {
            $user->money = $money;
        } else {
            $user->money = 0;
        }

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
        $user->contact_method = 1;
        $user->locale = $_ENV['locale'];
        $random_group = Config::obtain('random_group');

        if ($random_group === '') {
            $user->node_group = 0;
        } else {
            $user->node_group = $random_group[array_rand(explode(',', $random_group))];
        }

        if ($user->save() && !$is_admin_reg) {
            Auth::login($user->id, 3600);
            (new LoginIp())->collectLoginIP($_SERVER['REMOTE_ADDR'], 0, $user->id);

            return $response->withHeader('HX-Redirect', $redir)->withJson([
                'ret' => 1,
                'msg' => '注册成功！正在进入登录界面',
            ]);
        }

        return ResponseHelper::error($response, '未知错误');
    }

    /**
     * @param ServerRequest $request
     * @param Response $response
     * @param array $args
     *
     * @return Response|ResponseInterface
     *
     * @throws RedisException
     * @throws Exception
     */
    public function registerHandle(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        if (Config::obtain('reg_mode') === 'close') {
            return $response->write(
                $this->view()
                    ->assign('status', "Registration Not Open")
                    ->assign('message', "Registration is currently not open")
                    ->fetch("user/mole/operation-res.tpl")
            );
        }

        if (Config::obtain('enable_reg_captcha') && !Captcha::verify($request->getParams())) {
            return $response->write(
                $this->view()
                    ->assign('status', "System Unable to Accept Your Verification Result")
                    ->assign('message', "The system is unable to accept your verification result. Please refresh the page and try again.")
                    ->fetch("user/mole/operation-res.tpl")
            );
        }

        $email = strtolower(trim($this->antiXss->xss_clean($request->getParam('email'))));
        $name = $email;
        $passwd = $request->getParam('passwd');

        $imtype = 0;
        $imvalue = '';

        // check email format
        $check_res = Tools::isEmailLegal($email);

        if ($check_res['ret'] === 0) {
            return $response->withJson($check_res);
        }
        // check email
        $user = (new User())->where('email', $email)->first();

        if ($user !== null) {
            return $response->write(
                $this->view()
                    ->assign('status', "Email Already Registered")
                    ->assign('message', "The email has already been registered.")
                    ->fetch("user/mole/operation-res.tpl")
            );
        }
        // check pwd length
        if (strlen($passwd) < 8) {
            return $response->write(
                $this->view()
                    ->assign('status', "Password Must Be Greater Than 8 Characters")
                    ->assign('message', "Please enter a password greater than 8 characters.")
                    ->fetch("user/mole/operation-res.tpl")
            );
        }

        return $this->registerHelper($response, $name, $email, $passwd, $imtype, $imvalue, 0, 0);
    }

    public function logout(ServerRequest $request, Response $response, $next): Response
    {
        Auth::logout();

        return $response->withStatus(302)
            ->withHeader('Location', '/auth/login');
    }
}
