<?php
declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * 访问日志中间件:记录 method/path/status/耗时;
 * 异常时记录上下文后继续抛出,由外层错误处理器兜底。
 */
final class AccessLogMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $start = hrtime(true);

        try {
            $response = $handler->handle($request);
        } catch (Throwable $exception) {
            $this->logger->error('request failed', [
                'method' => $request->getMethod(),
                'path' => $request->getUri()->getPath(),
                'duration_ms' => $this->durationMs($start),
                'exception' => $exception,
            ]);
            throw $exception;
        }

        $this->logger->info('request completed', [
            'method' => $request->getMethod(),
            'path' => $request->getUri()->getPath(),
            'status' => $response->getStatusCode(),
            'duration_ms' => $this->durationMs($start),
        ]);

        return $response;
    }

    private function durationMs(int $start): float
    {
        return round((hrtime(true) - $start) / 1e6, 2);
    }
}
