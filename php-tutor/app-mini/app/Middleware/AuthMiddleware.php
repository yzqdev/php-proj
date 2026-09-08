<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Http\ApiResponder;
use App\Security\JwtService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

/**
 * Bearer Token 认证中间件:解析并校验 JWT,
 * 通过后把 userId 写入请求属性,失败返回 401。
 */
final class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly JwtService $jwt,
        private readonly ResponseFactoryInterface $responseFactory,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $header = $request->getHeaderLine('Authorization');
        if (preg_match('/^Bearer\s+(\S+)$/i', $header, $matches) !== 1) {
            return $this->unauthorized($request);
        }

        try {
            $token = $this->jwt->parse($matches[1]);
            if (!$this->jwt->validate($token)) {
                return $this->unauthorized($request);
            }
            $request = $request->withAttribute('userId', $this->jwt->userIdFromToken($token));
        } catch (Throwable) {
            return $this->unauthorized($request);
        }

        return $handler->handle($request);
    }

    private function unauthorized(ServerRequestInterface $request): ResponseInterface
    {
        return ApiResponder::error(
            $this->responseFactory->createResponse(),
            'unauthorized',
            '需要登录或凭据无效',
            401,
        );
    }
}
