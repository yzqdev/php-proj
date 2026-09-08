<?php

declare(strict_types=1);

namespace App\Middleware;

use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * CORS 跨域中间件（PSR-15 风格，适配 Slim 4）
 */
final class CorsMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly Logger $logger,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $origin = $request->getHeaderLine('Origin');
        $this->logger->info(sprintf('Origin: %s', $origin));
        $corsHeaders = [
            'Access-Control-Allow-Origin'      => $origin ?: '*',
            'Access-Control-Allow-Methods'     => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With, Accept',
            'Access-Control-Allow-Credentials' => 'false',
            'Access-Control-Max-Age'           => '86400',
            'Access-Control-Expose-Headers'    => 'Content-Length, Content-Type',
        ];

        if ($request->getMethod() === 'OPTIONS') {
            $this->logger->info('CORS 预检请求', ['origin' => $origin, 'path' => $request->getUri()->getPath()]);
            $response = $handler->handle($request);
            foreach ($corsHeaders as $name => $value) {
                $response = $response->withHeader($name, $value);
            }
            return $response->withStatus(204);
        }

        $response = $handler->handle($request);
        foreach ($corsHeaders as $name => $value) {
            $response = $response->withHeader($name, $value);
        }
        return $response;
    }
}
