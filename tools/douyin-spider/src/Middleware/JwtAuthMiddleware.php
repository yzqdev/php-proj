<?php

declare(strict_types=1);

namespace Yzqde\DouyinSpider\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Yzqde\DouyinSpider\Exception\AuthenticationException;

use function env;

/**
 * JWT 认证中间件
 *
 * 从 Authorization: Bearer <token> 头中解析 Token，
 * 并将用户信息存入请求属性 authUser。
 */
class JwtAuthMiddleware implements MiddlewareInterface
{
    private readonly string $jwtSecret;

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
        $secret = env('JWT_SECRET');
        if ($secret === false || $secret === '') {
            throw new \RuntimeException('JWT_SECRET 环境变量未设置');
        }
        $this->jwtSecret = $secret;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $header = $request->getHeaderLine('Authorization');
        if ($header === '') {
            throw new AuthenticationException('未提供认证令牌');
        }

        $parts = explode(' ', $header);
        if (count($parts) !== 2 || strtolower($parts[0]) !== 'bearer') {
            throw new AuthenticationException('无效的认证格式，应为 Bearer <token>');
        }

        $token = $parts[1];
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
        } catch (\Throwable $e) {
            $this->logger->warning('JWT 验证失败: ' . $e->getMessage());
            throw new AuthenticationException('令牌无效或已过期');
        }

        $authUser = [
            'id' => (int) $decoded->sub,
            'username' => $decoded->username ?? '',
            'role' => $decoded->role ?? '',
        ];

        $request = $request->withAttribute('authUser', $authUser);

        return $handler->handle($request);
    }
}
