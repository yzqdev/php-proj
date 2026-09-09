<?php
declare(strict_types=1);

namespace App\Controller;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use App\Exception\ApiException;
use Throwable;

/**
 * 全局兜底错误处理（注册到 Slim ErrorMiddleware 的默认处理器）：
 *   - ApiException（业务错误，等价旧 apiError）→ {"code":业务码,"message":原文,"data":null}
 *   - 404 / 405 / 其他异常 → 与旧 index.php 行为对齐的 JSON 信封（未知模块 / 服务器内部错误），
 *     不泄露堆栈、路径等内部信息。
 * 同时负责异常日志：业务 4xx 记 warning，未捕获 5xx 记 error（含异常类与抛出位置，供控制台排查）。
 */
final class ApiErrorHandler
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails,
    ): ResponseInterface {
        $response = $this->render($exception);
        $this->log($request, $exception, (int) $response->getStatusCode());

        return $response;
    }

    /**
     * 渲染与迁移前一致的 JSON 错误信封。
     */
    private function render(Throwable $exception): ResponseInterface
    {
        if ($exception instanceof ApiException) {
            $status = $exception->getCode();
            $status = $status >= 400 && $status <= 599 ? $status : 400;

            return ResponseFactory::error($this->responseFactory->createResponse($status), $exception->getMessage(), $status);
        }

        $status = $exception->getCode();
        $status = is_int($status) && $status >= 400 && $status <= 599 ? $status : 500;

        return match ($status) {
            404, 405 => ResponseFactory::error($this->responseFactory->createResponse(404), '未知模块', 404),
            default => ResponseFactory::error($this->responseFactory->createResponse(500), '服务器内部错误', 500),
        };
    }

    /**
     * 结构化异常日志：request_id 与访问日志关联；只记异常类/消息/位置/前几帧堆栈，
     * 不记录请求体（避免密码等敏感信息进入日志）。
     */
    private function log(ServerRequestInterface $request, Throwable $exception, int $status): void
    {
        $context = [
            'rid' => $request->getAttribute('rid'),
            'req' => $request->getMethod() . ' ' . $request->getUri()->getPath(),
            'code' => $status,
            'exception' => $exception::class,
            'at' => $exception->getFile() . ':' . $exception->getLine(),
        ];

        if ($status >= 500) {
            // 控制台堆栈只保留前 4 帧（类::函数 文件:行），足够定位且不淹没终端
            $frames = [];
            foreach (array_slice($exception->getTrace(), 0, 4) as $i => $frame) {
                $frames[] = sprintf(
                    '#%d %s%s%s',
                    $i,
                    ($frame['file'] ?? '?') . ':' . ($frame['line'] ?? '?') . ' ',
                    $frame['class'] ?? '',
                    ($frame['class'] ?? '' ) !== '' ? '::' : '',
                ) . ($frame['function'] ?? '');
            }
            $context['trace'] = $frames;
            $this->logger->error('uncaught_exception', $context + ['msg' => $exception->getMessage()]);
        } else {
            $this->logger->warning('biz_exception', $context + ['msg' => $exception->getMessage()]);
        }
    }
}
