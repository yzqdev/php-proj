<?php

declare(strict_types=1);

namespace App\Helpers;

use Psr\Http\Message\ServerRequestInterface;

/**
 * 请求封装
 *
 * PHP 没有 Java 那种 HttpServletRequest 对象，而是散落着
 * $_GET / $_POST / $_REQUEST / $_SERVER / $_FILES 一堆超全局数组。
 * 这里把它们收拢成一个类，读起来接近 Java 的 request.getParameter()。
 *
 * 安全约定：
 *   - input() 只读，不修改超全局变量；
 *   - 字符串默认经过 htmlspecialchars 的思路由视图层负责，这里只负责"取值"。
 *
 * Slim 4 集成：
 *   - 通过 fromPsr7() 工厂从 PSR-7 ServerRequest 构建，同时提取路由参数
 *   - 路由参数从 ServerRequest->getAttributes() 读取（Slim 的 {id} 占位符）
 */
final class Request
{
    /** 当前请求方法：GET / POST / ... */
    public readonly string $method;

    /** 去掉 query string 后的路径，例如 /articles?page=2 */
    public readonly string $path;

    /** 从 URL 路径里解析出的参数，例如 /articles/3 中的 id=3 */
    private readonly array $routeParams;

    public function __construct(array $routeParams = [])
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->path = self::resolvePath();
        $this->routeParams = $routeParams;
    }

    /**
     * 从 PSR-7 ServerRequest 构建 Request 助手
     *
     * Slim 路由的 {id} 占位符会放在 ServerRequest 的 attributes 里，
     * 这里提取出来传给 routeParamInt() 等方法。
     */
    public static function fromPsr7(ServerRequestInterface $psr7): self
    {
        $routeArgs = $psr7->getAttributes() ?? [];
        // Slim 把路由参数放在 'id' 等键名下，直接传入
        return new self($routeArgs);
    }

    private static function resolvePath(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = (string)explode('?', $uri, 2)[0];
        return rtrim($path, '/') ?: '/';
    }

    /** 取单个参数，依次查 route → GET → POST */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key]
            ?? $_GET[$key]
            ?? $_POST[$key]
            ?? $default;
    }

    /** 取所有参数（GET + POST 合并），用于表单整体处理 */
    public function all(): array
    {
        return [...$_GET, ...$_POST];
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    /**
     * 取路由占位符参数，例如 /articles/{id} 中的 id
     */
    public function routeParam(string $key): ?string
    {
        $value = $this->routeParams[$key] ?? null;
        return $value === null ? null : (string)$value;
    }

    public function routeParamInt(string $key): int
    {
        $value = $this->routeParams[$key] ?? null;
        return $value === null ? 0 : (int)$value;
    }
}