<?php

declare(strict_types=1);

namespace Yzqde\DouyinSpider\Middleware;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Factory\ResponseFactory;

/**
 * 统一 JSON 响应格式中间件
 *
 * 将所有响应包装为 { code, message, data } 结构。
 * 正常响应：code=0；业务异常由 ExceptionMiddleware 处理，不经过此中间件。
 */
class ApiResponseMiddleware implements MiddlewareInterface, StatusCodeInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $body = $response->getBody()->__toString();

        $data = json_decode($body, true);

        // 非 JSON 或已有 code 字段则直接返回
        if (!is_array($data) || isset($data['code'])) {
            return $response;
        }

        $httpCode = $response->getStatusCode();
        $code = match ($httpCode) {
            200, 201, 204 => 0,
            400 => -1,
            401 => -2,
            403 => -3,
            404 => -4,
            default => -99,
        };
        $message = match ($httpCode) {
            200 => 'success',
            201 => 'created',
            204 => 'deleted',
            400 => '请求参数错误',
            401 => '未授权',
            403 => '禁止访问',
            404 => '资源不存在',
            default => '请求失败',
        };

        $wrapped = [
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ];

        $stream = (new StreamFactory())->createStream(json_encode($wrapped, JSON_UNESCAPED_UNICODE));

        return (new ResponseFactory())
            ->createResponse($httpCode)
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withBody($stream);
    }
}
