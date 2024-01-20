<?php

declare(strict_types=1);

namespace App\Controllers\ExtraAPI;

use App\Controllers\BaseController;
use App\Services\Analytics;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

final class NotificationController extends BaseController
{
    public function createNotification(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        return $response->write("Hello World");
    }
}
