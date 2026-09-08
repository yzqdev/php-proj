<?php

declare(strict_types=1);

namespace Yzqde\DouyinSpider\Middleware;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpInternalServerErrorException;
use Slim\Psr7\Factory\ResponseFactory;
use Throwable;
use Yzqde\DouyinSpider\Exception\AppException;
use Yzqde\DouyinSpider\Exception\AuthenticationException;
use Yzqde\DouyinSpider\Exception\AuthorizationException;

use function Yzqde\DouyinSpider\Support\json;

/**
 * 全局异常处理中间件
 *
 * 捕获所有异常并返回统一 JSON 格式错误响应。
 */
class ExceptionMiddleware implements MiddlewareInterface, StatusCodeInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly bool $debug,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (AppException $e) {
            $this->logger->warning($e->getMessage(), [
                'exception_class' => get_class($e),
                'code' => $e->getCode(),
                'trace' => $this->debug ? $e->getTraceAsString() : null,
            ]);

            return json(new ResponseFactory()->createResponse(), [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'data' => null,
            ], $e->getCode());
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning($e->getMessage());
            return json(new ResponseFactory()->createResponse(), [
                'code' => -1,
                'message' => $e->getMessage(),
                'data' => null,
            ], 400);
        } catch (HttpBadRequestException $e) {
            return json(new ResponseFactory()->createResponse(), [
                'code' => -1,
                'message' => $e->getMessage(),
                'data' => null,
            ], 400);
        } catch (HttpUnauthorizedException $e) {
            return json(new ResponseFactory()->createResponse(), [
                'code' => -2,
                'message' => $e->getMessage(),
                'data' => null,
            ], 401);
        } catch (HttpForbiddenException $e) {
            return json(new ResponseFactory()->createResponse(), [
                'code' => -3,
                'message' => $e->getMessage(),
                'data' => null,
            ], 403);
        } catch (HttpNotFoundException $e) {
            return json(new ResponseFactory()->createResponse(), [
                'code' => -4,
                'message' => $e->getMessage(),
                'data' => null,
            ], 404);
        } catch (HttpInternalServerErrorException $e) {
            $this->logger->error($e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return json(new ResponseFactory()->createResponse(), [
                'code' => -99,
                'message' => $this->debug ? $e->getMessage() : '服务器内部错误',
                'data' => null,
            ], 500);
        } catch (Throwable $e) {
            $this->logger->error('未处理异常: ' . $e->getMessage(), [
                'class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            $message = $this->debug ? $e->getMessage() : '服务器内部错误';
            return json(new ResponseFactory()->createResponse(), [
                'code' => -99,
                'message' => $message,
                'data' => null,
            ], 500);
        }
    }
}
