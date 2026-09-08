<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\MyLogger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * ============================================================
 * 请求日志中间件（PSR-15）
 * ============================================================
 *
 * 对比 Java / Spring Boot：
 *   - Spring 的 OncePerRequestFilter / RequestBodyAdvice
 *   - 等价：每个请求前后各记一行日志，便于排查"这个接口最近有没有被调过"
 */
final class RequestLogger
{
    public function __invoke(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $startAt = microtime(true);

        // 请求开始
        MyLogger::info('请求开始', [
            'method'    => $request->getMethod(),
            'path'      => $request->getUri()->getPath(),
            'remote'    => $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1',
            'userAgent' => substr((string) $request->getHeaderLine('User-Agent'), 0, 80),
        ]);

        $response = $handler->handle($request);

        // 请求结束
        $durationMs = (microtime(true) - $startAt) * 1000;
        MyLogger::info('请求结束', [
            'method'     => $request->getMethod(),
            'path'       => $request->getUri()->getPath(),
            'status'     => $response->getStatusCode(),
            'durationMs' => round($durationMs, 2),
        ]);

        return $response;
    }
}