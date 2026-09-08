<?php

declare(strict_types=1);

namespace Yzqde\DouyinSpider\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function env;

/**
 * CORS 跨域支持中间件
 */
class CorsMiddleware implements MiddlewareInterface
{
    private readonly string $allowedOrigins;
    private readonly string $allowedMethods;
    private readonly string $allowedHeaders;
    private readonly bool $allowCredentials;

    public function __construct()
    {
        $this->allowedOrigins = env('CORS_ALLOWED_ORIGINS') ?: '*';
        $this->allowedMethods = env('CORS_ALLOWED_METHODS') ?: 'GET,POST,PUT,PATCH,DELETE,OPTIONS';
        $this->allowedHeaders = env('CORS_ALLOWED_HEADERS') ?: 'Content-Type,Authorization';
        $this->allowCredentials = filter_var(env('CORS_ALLOW_CREDENTIALS'), FILTER_VALIDATE_BOOLEAN);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if ($this->allowedOrigins === '*') {
            $response = $response->withHeader('Access-Control-Allow-Origin', '*');
        } else {
            $origin = $request->getHeaderLine('Origin');
            $allowed = explode(',', $this->allowedOrigins);
            foreach ($allowed as $item) {
                $item = trim($item);
                if ($item === '' || $item === $origin) {
                    $response = $response->withHeader('Access-Control-Allow-Origin', $origin);
                    break;
                }
            }
        }

        $response = $response
            ->withHeader('Access-Control-Allow-Methods', $this->allowedMethods)
            ->withHeader('Access-Control-Allow-Headers', $this->allowedHeaders)
            ->withHeader('Access-Control-Max-Age', '86400');

        if ($this->allowCredentials) {
            $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        }

        return $response;
    }
}
