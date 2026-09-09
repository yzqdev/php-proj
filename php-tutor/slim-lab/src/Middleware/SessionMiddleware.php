<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * 会话启动中间件：替代旧 api/clouddrive.php 顶部的 session_start()。
 * 等价 session_start()（PHP 内建会话，默认 cookie 参数）。
 */
final class SessionMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        return $handler->handle($request);
    }
}
