<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Controller\ResponseFactory;

/**
 * CORS 中间件：完整复刻旧 index.php 顶部的全局跨域头与 OPTIONS 短路逻辑：
 *   - 所有响应追加 Access-Control-Allow-* 四个头（值与迁移前一致）
 *   - OPTIONS 请求一律返回 204（带 CORS 头与 JSON Content-Type），不再进入路由
 *   - 若业务响应已自带 Access-Control-Allow-Origin（如 clouddrive stream 的 *），则不覆盖
 */
final class CorsMiddleware implements MiddlewareInterface
{
    private const ALLOW_ORIGIN = 'http://localhost:5173';
    private const ALLOW_METHODS = 'GET, POST, PUT, DELETE, OPTIONS';
    private const ALLOW_HEADERS = 'Content-Type, Authorization, X-Requested-With';

    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() === 'OPTIONS') {
            return $this->responseFactory->createResponse(204)
                ->withHeader('Access-Control-Allow-Origin', self::ALLOW_ORIGIN)
                ->withHeader('Access-Control-Allow-Methods', self::ALLOW_METHODS)
                ->withHeader('Access-Control-Allow-Headers', self::ALLOW_HEADERS)
                ->withHeader('Access-Control-Allow-Credentials', 'true')
                ->withHeader('Content-Type', ResponseFactory::JSON_CONTENT_TYPE);
        }

        $response = $handler->handle($request);

        if (!$response->hasHeader('Access-Control-Allow-Origin')) {
            $response = $response->withHeader('Access-Control-Allow-Origin', self::ALLOW_ORIGIN);
        }
        if (!$response->hasHeader('Access-Control-Allow-Methods')) {
            $response = $response->withHeader('Access-Control-Allow-Methods', self::ALLOW_METHODS);
        }
        if (!$response->hasHeader('Access-Control-Allow-Headers')) {
            $response = $response->withHeader('Access-Control-Allow-Headers', self::ALLOW_HEADERS);
        }
        if (!$response->hasHeader('Access-Control-Allow-Credentials')) {
            $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }
}
