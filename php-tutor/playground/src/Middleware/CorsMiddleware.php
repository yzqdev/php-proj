<?php

declare(strict_types=1);

namespace Yzqde\Playground\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;

/**
 * CORS:前后端分离部署,跨域由后端放行(允许所有来源)
 *
 * 声明为最外层中间件,保证包括错误响应在内的所有响应都带 CORS 头;
 * OPTIONS 预检在这里直接短路,不进入路由。
 */
final class CorsMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() === 'OPTIONS') {
            return $this->withCors((new ResponseFactory())->createResponse(204));
        }
        return $this->withCors($handler->handle($request));
    }

    private function withCors(ResponseInterface $response): ResponseInterface
    {
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
            ->withHeader('Access-Control-Max-Age', '86400');
    }
}
