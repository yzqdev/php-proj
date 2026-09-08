<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exceptions\BusinessException;
use App\Helpers\Config;
use App\Helpers\Logger;
use Slim\Interfaces\ErrorRendererInterface;
use Slim\Psr7\Response;
use Throwable;

/**
 * ============================================================
 * Slim 错误渲染器（PSR-15 ErrorRendererInterface 实现）
 * ============================================================
 *
 * 对比 Java / Spring Boot：
 *   - Spring 的 @ControllerAdvice + @ExceptionHandler
 *   - 这里等价：Slim 捕获 Throwable 后调用 ErrorRenderer 转 JSON
 *
 * 关键设计：
 *   - 所有异常统一走这里，不再依赖 set_exception_handler
 *   - BusinessException 携带 bizCode / httpStatus，直接映射
 *   - 其他异常一律 500（debug 模式下返回细节）
 */
final class ErrorRenderer implements ErrorRendererInterface
{
    public function __invoke(Throwable $exception, bool $displayErrorDetails): string
    {
        $debug = Config::get('app.debug', false);

        // 记录异常日志（所有异常都记，包括业务异常）
        Logger::error($exception::class . ': ' . $exception->getMessage(), [
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
            'trace'   => substr((string)$exception->getTraceAsString(), 0, 1200),
            'request' => ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN') . ' ' . ($_SERVER['REQUEST_URI'] ?? '/'),
        ]);

        $isBiz = $exception instanceof BusinessException;
        $httpStatus = $isBiz ? $exception->getHttpStatus() : 500;
        $bizCode = $isBiz ? $exception->getBizCode() : 50000;

        $body = [
            'code'    => $bizCode,
            'message' => $debug ? $exception->getMessage() : ($isBiz ? $exception->getMessage() : '服务器内部错误'),
            'data'    => null,
        ];
        if ($debug) {
            $body['debug'] = [
                'type'  => $exception::class,
                'file'  => $exception->getFile(),
                'line'  => $exception->getLine(),
                'trace' => substr((string)$exception->getTraceAsString(), 0, 1200),
            ];
        }

        $response = new Response();
        $response->getBody()->write(json_encode($body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return $response
            ->withHeader('Content-Type', 'application/json; charset=UTF-8')
            ->withStatus($httpStatus)
            ->getBody()
            ->getContents();
    }
}