<?php

declare(strict_types=1);

namespace Service\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Service\Auth\TokenStore;
use Service\Http\ResponseFactory;

/**
 * 平台接口（Bearer Token）鉴权中间件。
 *
 * 与网盘使用的 CloudDriveAuthMiddleware（PHP session）相互独立：
 * 本中间件校验 `Authorization: Bearer <token>`，令牌由 TokenStore 存在 Redis 中。
 * Redis 不可用时 TokenStore 会抛异常，由顶层 ApiErrorHandler 兜底为 500（fail closed，不放行）。
 */
final class ApiAuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly TokenStore $tokens,
        private readonly ResponseFactoryInterface $responseFactory,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $this->bearerToken($request);

        if ($token === '' || $this->tokens->resolve($token) === null) {
            return ResponseFactory::error($this->responseFactory->createResponse(401), '未登录', 401);
        }

        return $handler->handle($request);
    }

    /** 从 Authorization 头提取 Bearer 令牌，缺失或格式不符返回空串 */
    private function bearerToken(ServerRequestInterface $request): string
    {
        $header = $request->getHeaderLine('Authorization');

        return str_starts_with($header, 'Bearer ') ? trim(substr($header, 7)) : '';
    }
}
