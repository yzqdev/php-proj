<?php

declare(strict_types=1);

namespace Yzqde\Fox\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use Yzqde\Fox\Util\Cache;
use Yzqde\Fox\Util\RedisCache;

/**
 * 基于 #[Cache] 属性的响应缓存中间件。
 *
 * 当路由处理器方法标注了 #[Cache(ttl: N)] 时，
 * 本中间件在请求到达 handler 之前检查缓存，命中则直接返回；
 * 未命中则执行 handler 并将响应体缓存起来供下次使用。
 *
 * 缓存 key = HTTP method + URI path + query string + 请求体 hash，
 * POST / PUT / DELETE 也会按各自的请求内容独立缓存。
 */
class CacheMiddleware implements MiddlewareInterface
{
    public function __construct(
        private RedisCache $cache,
        private ResponseFactoryInterface $responseFactory,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 1. 从路由解析结果中获取 handler
        $route = $request->getAttribute('route');

        if ($route === null) {
            return $handler->handle($request);
        }

        $callable = $route->getCallable();

        if (!is_array($callable) || count($callable) !== 2) {
            return $handler->handle($request);
        }

        [$object, $method] = $callable;

        if (!is_object($object) || !is_string($method)) {
            return $handler->handle($request);
        }

        // 2. 检查目标方法是否标注了 #[Cache] 属性
        $reflection = new ReflectionClass($object);
        $methodReflection = $reflection->getMethod($method);
        $attributes = $methodReflection->getAttributes(Cache::class);

        if (empty($attributes)) {
            return $handler->handle($request);
        }

        /** @var Cache $cacheAttr */
        $cacheAttr = $attributes[0]->newInstance();

        // 3. 生成缓存 key
        $cacheKey = $this->buildCacheKey($request);

        // 4. 尝试读取缓存
        $cachedBody = $this->cache->get($cacheKey);

        if ($cachedBody !== null) {
            // 命中缓存 — 直接构造响应，不再调用 handler
            $response = $this->responseFactory->createResponse(200);
            $response->getBody()->write($cachedBody);

            return $response->withHeader('Content-Type', 'application/json')
                            ->withHeader('X-Cache', 'HIT');
        }

        // 5. 未命中 — 执行 handler
        $response = $handler->handle($request);

        // 6. 缓存响应体（只缓存 2xx 响应）
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $body = $response->getBody();

            if ($body->isSeekable()) {
                $body->rewind();
            }

            $content = $body->getContents();

            if ($content !== '') {
                $this->cache->set($cacheKey, $content, $cacheAttr->ttl);

                // 重置流位置，确保后续中间件/框架仍能读到内容
                if ($body->isSeekable()) {
                    $body->rewind();
                }
            }
        }

        return $response->withHeader('X-Cache', 'MISS');
    }

    /**
     * 根据请求的 method + path + query + body 生成唯一缓存 key。
     */
    private function buildCacheKey(ServerRequestInterface $request): string
    {
        $parts = [
            $request->getMethod(),
            $request->getUri()->getPath(),
            $request->getUri()->getQuery() ?? '',
        ];

        $method = $request->getMethod();

        if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            $body = (string) $request->getBody();
            $parts[] = hash('md5', $body);
        }

        return 'fox-cache:' . md5(implode("\n", $parts));
    }
}
