<?php

declare(strict_types=1);

namespace Yzqde\Playground\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use UnexpectedValueException;

/**
 * 访问日志:每个请求记一条,含方法、路径、状态码、耗时与来源
 *
 * 必须作为最外层中间件注册(见 public/index.php):这样即便内部抛出异常
 * 被错误中间件转成响应,这里仍能拿到最终状态码并计入耗时。
 * CorsMiddleware 的 OPTIONS 预检在本中间件之内短路,因此预检请求也会被记录。
 */
final class AccessLogMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $started = microtime(true);
        $response = $handler->handle($request);

        try {
            $status = $response->getStatusCode();
            $durationMs = round((microtime(true) - $started) * 1000, 2);
            $method = $request->getMethod();
            $path = $request->getUri()->getPath();

            $context = [
                'method' => $method,
                'path' => $path,
                'status' => $status,
                'duration_ms' => $durationMs,
                'ip' => $request->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1',
                'user_agent' => $request->getHeaderLine('User-Agent'),
            ];

            // 5xx 需要人工介入,4xx 多为调用方问题但仍值得关注,其余为常规流量
            $level = $status >= 500 ? 'error' : ($status >= 400 ? 'warning' : 'info');
            $this->logger->$level(sprintf('%s %s -> %d (%s ms)', $method, $path, $status, $durationMs), $context);
        } catch (RuntimeException | UnexpectedValueException) {
            // 刻意吞掉:日志写不进去(磁盘满、目录权限)不应把正常请求拖成 500
        }

        return $response;
    }
}
