<?php

declare(strict_types=1);

namespace App\Middleware;

use Predis\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Exception\ApiException;
use App\Controller\ResponseFactory;

/**
 * 登录限流中间件（Redis 固定窗口计数）：
 * 同一 IP 对 /api/auth/login、/api/auth/register 的尝试，60 秒内最多 10 次，
 * 超出返回 429（Retry-After: 60）。键 60 秒后由 Redis TTL 自动清除。
 */
final class RateLimitMiddleware implements MiddlewareInterface
{
    private const WINDOW = 60;

    private const MAX_ATTEMPTS = 10;

    private const KEY_PREFIX = 'ratelimit:';

    public function __construct(
        private readonly ClientInterface $redis,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        if ($path !== '/api/auth/login' && $path !== '/api/auth/register') {
            return $handler->handle($request);
        }

        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';
        $key = self::KEY_PREFIX . $ip;

        // INCR + 首次 EXPIRE 构成固定窗口计数；TTL 到期键自动清零
        $attempts = (int) $this->redis->incr($key);
        if ($attempts === 1) {
            $this->redis->expire($key, self::WINDOW);
        }

        if ($attempts > self::MAX_ATTEMPTS) {
            throw new ApiException('尝试过于频繁，请 1 分钟后再试', 429);
        }

        return $handler->handle($request);
    }
}
