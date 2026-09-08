<?php

declare(strict_types=1);

namespace Yzqde\Playground\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;

/**
 * 日志接口访问控制:仅允许本机来源访问
 *
 * 日志内容含文件路径、完整堆栈与访问 IP,比图床业务接口敏感,
 * 但本项目没有登录态,用请求来源 IP 划出边界是最轻量的做法。
 * 需要放行局域网或生产部署时,把 .env 的 LOG_VIEW_ALLOW_ANY 设为 1。
 *
 * REMOTE_ADDR 由 socket 层给出,不受请求头影响;反代同机部署时该值为
 * 127.0.0.1,仍可正常访问。
 */
final class LogAccessGuard implements MiddlewareInterface
{
    public function __construct(private readonly bool $allowAny)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->allowAny || $this->isLoopback((string)($request->getServerParams()['REMOTE_ADDR'] ?? ''))) {
            return $handler->handle($request);
        }

        return $this->forbidden();
    }

    private function isLoopback(string $ip): bool
    {
        return $ip === '127.0.0.1' || $ip === '::1' || $ip === 'localhost';
    }

    private function forbidden(): ResponseInterface
    {
        $response = (new ResponseFactory())->createResponse(403);
        $response->getBody()->write((string)json_encode(
            ['success' => false, 'message' => '日志接口仅允许本机访问'],
            JSON_UNESCAPED_UNICODE,
        ));
        return $response->withHeader('Content-Type', 'application/json; charset=UTF-8');
    }
}
