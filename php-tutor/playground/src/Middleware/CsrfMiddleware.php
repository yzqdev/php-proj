<?php

declare(strict_types=1);

namespace Yzqde\Playground\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;

/**
 * CSRF 防护(无状态版):非 GET 请求必须携带 X-Requested-With: XMLHttpRequest
 *
 * 当前 API 没有登录态、不使用 Cookie 会话,经典 CSRF(诱导浏览器自动提交表单)
 * 无法携带自定义请求头,因此校验自定义头即可阻断;未来引入 Cookie 认证后,
 * 应升级为一次性 Token 方案。
 */
final class CsrfMiddleware implements MiddlewareInterface
{
    private const string HEADER = 'X-Requested-With';
    private const string EXPECTED = 'XMLHttpRequest';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)
            && $request->getHeaderLine(self::HEADER) !== self::EXPECTED) {
            return $this->forbidden();
        }
        return $handler->handle($request);
    }

    private function forbidden(): ResponseInterface
    {
        $response = (new ResponseFactory())->createResponse(403);
        $response->getBody()->write((string)json_encode(
            ['success' => false, 'message' => '请求缺少必要的安全头,请刷新页面重试'],
            JSON_UNESCAPED_UNICODE,
        ));
        return $response->withHeader('Content-Type', 'application/json; charset=UTF-8');
    }
}
