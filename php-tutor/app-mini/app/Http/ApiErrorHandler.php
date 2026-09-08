<?php
declare(strict_types=1);

namespace App\Http;

use App\Exceptions\ApiException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Handlers\ErrorHandler;
use Slim\Interfaces\CallableResolverInterface;

/**
 * 全局异常处理器。
 *
 * 注意:php-di/slim-bridge 的 CallableResolver 不能像 Slim 内置的那样把
 * "可调用类名字符串"解析为 callable,因此本类完全接管 respond()/writeToErrorLog(),
 * 直接使用 ApiErrorRenderer 与注入的 Logger,绕开 callable 解析管道。
 *
 * - 状态码取自业务异常(ApiException)
 * - 日志记录完整堆栈(通过 Monolog exception context)
 * - 响应统一使用 ApiErrorRenderer,强制 JSON
 */
final class ApiErrorHandler extends ErrorHandler
{
    public function __construct(
        CallableResolverInterface $callableResolver,
        ResponseFactoryInterface $responseFactory,
        LoggerInterface $logger,
        private readonly ApiErrorRenderer $renderer,
    ) {
        parent::__construct($callableResolver, $responseFactory, $logger);
    }

    protected function determineStatusCode(): int
    {
        if ($this->exception instanceof ApiException) {
            return $this->exception->getStatusCode();
        }

        return parent::determineStatusCode();
    }

    /**
     * 覆盖 Slim 默认实现:不再通过 callable 解析 PlainTextErrorRenderer,
     * 直接构造错误摘要并交给 logError()。
     */
    protected function writeToErrorLog(): void
    {
        $exception = $this->exception;
        $this->logError(sprintf(
            '%s: %s in %s:%d',
            $exception::class,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
        ));
    }

    /**
     * 记录错误,并把异常对象作为 Monolog context,使其输出完整堆栈。
     */
    protected function logError(string $error): void
    {
        $this->logger->error($error, ['exception' => $this->exception]);
    }

    protected function respond(): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($this->statusCode);
        $body = $this->renderer->__invoke($this->exception, $this->displayErrorDetails);
        $response->getBody()->write($body);
        $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');

        if ($this->exception instanceof HttpMethodNotAllowedException) {
            $response = $response->withHeader('Allow', implode(', ', $this->exception->getAllowedMethods()));
        }

        return $response;
    }
}
