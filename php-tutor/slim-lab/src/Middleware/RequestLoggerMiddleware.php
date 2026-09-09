<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

/**
 * 访问日志中间件：位于中间件栈最外层，每个请求记录一行
 * 「方法 路径 状态码 耗时」，并带 request_id 上下文（与异常日志关联）。
 * 注意：不记录请求体（避免把密码等敏感数据写进日志）。
 */
final readonly class RequestLoggerMiddleware implements MiddlewareInterface
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $rid = bin2hex(random_bytes(4));
        $request = $request->withAttribute('rid', $rid);
        $start = hrtime(true);

        try {
            $response = $handler->handle($request);
        } catch (\Throwable $e) {
            // 正常情况下 ErrorMiddleware 会在内层兜底，此处仅为防御性记录后原样抛出
            $this->logger->error('request crashed before response', [
                'rid' => $rid,
                'req' => $request->getMethod() . ' ' . $request->getUri()->getPath(),
                'exception' => $e::class . ': ' . $e->getMessage(),
            ]);
            throw $e;
        }

        $ms = round((hrtime(true) - $start) / 1e6, 1);
        $this->logger->log(
            $response->getStatusCode() >= 500 ? 'error' : 'info',
            sprintf('%s %s %d', $request->getMethod(), $request->getUri()->getPath(), $response->getStatusCode()),
            [
                'rid' => $rid,
                'ms' => $ms,
                'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? '-',
            ],
        );

        return $response;
    }
}
