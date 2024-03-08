<?php

declare(strict_types=1);

namespace App\Controllers\Mole;

use App\Controllers\BaseController;
use App\Services\Auth;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

final class OAuthController extends BaseController
{

    /**
     * @throws Exception
     */
    public function createAccount(ServerRequest $request, Response $response, $next): Response|ResponseInterface
    {
        return $response->write("create account");
    }


    /**
     * @throws Exception
     */
    public function login(ServerRequest $request, Response $response, array $args): ResponseInterface
    {
        return $response->write("login");
    }

    public function logout(ServerRequest $request, Response $response, $next): Response
    {
        Auth::logout();

        return $response->withStatus(302)
            ->withHeader('Location', '/auth/login');
    }
}
