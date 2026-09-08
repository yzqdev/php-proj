<?php

declare(strict_types=1);

namespace Yzqde\Playground\Middleware;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Slim\Interfaces\ErrorRendererInterface;
use Throwable;
use Yzqde\Playground\Exception\ApiException;

/**
 * 统一 JSON 错误响应:{success, message}
 *
 * 详细信息(堆栈、SQL、路径)只写进 Monolog 日志,不返回给客户端;
 * 业务异常的 message 面向用户书写,可安全展示;调试模式下显示异常原文。
 */
final class JsonErrorRenderer implements ErrorRendererInterface
{
    public function __construct(private readonly Logger  $logger)
    {
    }

    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
        $this->logger->error($exception->getMessage(), [
            'exception' => $exception::class,
            'file' => $exception->getFile() . ':' . $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);

        $showMessage = $displayErrorDetails || $exception instanceof ApiException;
        return (string)json_encode(
            ['success' => false, 'message' => $showMessage ? $exception->getMessage() : '服务器内部错误'],
            JSON_UNESCAPED_UNICODE,
        );
    }
}
