# PSR 标准速查

PSR（PHP Standards Recommendations）由 PHP-FIG 制定，是 PHP 生态互操作的基础。理解这些接口能帮助判断"某个库能否替换、某个类应该实现什么"。

## 一、编码与自动加载

### PSR-1：基础编码标准

- 文件只用 `<?php` 或 `<?php ... ?>`，纯 PHP 文件不写结束标签。
- 文件必须使用 UTF-8 无 BOM。
- 一个文件只做一件事：声明符号或产生副作用，二者不混用。
- 命名空间与类名遵循 PSR-4；类名使用 `StudlyCaps`；方法名使用 `camelCase`；常量使用 `UPPER_CASE`。

### PSR-4：自动加载

命名空间前缀映射到目录，类名与文件名一致：

```json
{
  "autoload": {
    "psr-4": {
      "App\\": "src/",
      "App\\Tests\\": "tests/"
    }
  }
}
```

- 一个命名空间前缀对应一个基础目录。
- 前缀后的子命名空间对应子目录，类名对应 `ClassName.php`。
- 项目代码用 PSR-4；历史遗留目录可用 `classmap` 或 `files` 过渡。

### PSR-12：编码风格

PSR-12 扩展并取代 PSR-2。关键点：

- 文件使用 Unix 换行，结尾留一个空行。
- 4 个空格缩进，不使用 Tab。
- 类、方法的花括号另起一行；控制结构的花括号同行。
- 可见性必须显式声明；`abstract`/`final` 在可见性之前。
- 每行软限制 120 字符，不设硬限制。
- `use` 语句按字母排序，每组之间空行。
- 类型声明与返回类型之间、参数逗号后有空格。

实际执行交给 PHP-CS-Fixer 或 PHP_CodeSniffer，不手工维护。

## 二、日志

### PSR-3：LoggerInterface

```php
namespace Psr\Log;

interface LoggerInterface
{
    public function emergency(string|\Stringable $message, array $context = []): void;
    public function alert(string|\Stringable $message, array $context = []): void;
    public function critical(string|\Stringable $message, array $context = []): void;
    public function error(string|\Stringable $message, array $context = []): void;
    public function warning(string|\Stringable $message, array $context = []): void;
    public function notice(string|\Stringable $message, array $context = []): void;
    public function info(string|\Stringable $message, array $context = []): void;
    public function debug(string|\Stringable $message, array $context = []): void;
    public function log($level, string|\Stringable $message, array $context = []): void;
}
```

- 业务代码依赖 `LoggerInterface`，不直接依赖 Monolog 具体类。
- 上下文数组的 `exception` 键用于传递异常对象。
- 实现：Monolog、Laravel 的日志门面（底层仍是 Monolog）。

## 三、HTTP 消息

### PSR-7：HTTP Message Interfaces

定义 `RequestInterface`、`ResponseInterface`、`StreamInterface`、`UriInterface` 等不可变消息对象。

- 消息对象不可变：`withHeader()` 返回新实例。
- 实现：Nyholm PSR-7、Laminas Diactoros、Slim PSR-7、Guzzle PSR-7。
- 注意：PSR-7 只描述消息，不包含服务端运行方式。

### PSR-17：HTTP Factories

为 PSR-7 对象提供工厂接口，便于框架解耦具体实现：

- `ResponseFactoryInterface`
- `RequestFactoryInterface`
- `StreamFactoryInterface`
- `UploadedFileFactoryInterface`
- `UriFactoryInterface`

实现：Nyholm PSR-7、Laminas Diactoros、Guzzle。

### PSR-15：HTTP Server Middleware

```php
interface MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface;
}
```

- 中间件接收请求与下一处理器，返回响应。
- 实现：Slim 4 原生、Laminas Stratigility、Relay。
- 典型顺序：错误处理 → 请求 ID → 认证 → 限流 → 路由。

### PSR-18：HTTP Client

```php
interface ClientInterface
{
    public function sendRequest(RequestInterface $request): ResponseInterface;
}
```

- 只做同步请求，不含并发、重试等扩展能力。
- 实现：Guzzle（通过适配器）、Symfony HttpClient（通过适配器）、php-http/curl-client。
- 业务代码可依赖 `ClientInterface`，测试时替换为 mock。

## 四、容器

### PSR-11：ContainerInterface

```php
interface ContainerInterface
{
    public function get(string $id);
    public function has(string $id): bool;
}
```

- 实现：PHP-DI、Laravel Container、Symfony DependencyInjection（适配）、League Container。
- 业务代码可依赖 `ContainerInterface` 做延迟解析，但更推荐构造器注入。

## 五、缓存

### PSR-6：Caching Interface

- 面向"缓存池 + 缓存项"的对象模型：`CacheItemPoolInterface`、`CacheItemInterface`。
- 支持过期时间、命中/未命中、延迟保存。
- 适合需要精细控制的场景。

### PSR-16：Simple Cache

```php
interface CacheInterface
{
    public function get(string $key, mixed $default = null): mixed;
    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool;
    public function delete(string $key): bool;
    public function clear(): bool;
    public function getMultiple(iterable $keys, mixed $default = null): iterable;
    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool;
    public function deleteMultiple(iterable $keys): bool;
    public function has(string $key): bool;
}
```

- 简单键值接口，适合业务缓存。
- 实现：Symfony Cache（`Psr16Cache` 适配）、Laravel Cache（可适配）。

## 六、选择建议

| 场景 | 接口 |
|---|---|
| 记录日志 | PSR-3 `LoggerInterface` |
| 构造 HTTP 请求/响应对象 | PSR-17 工厂 + PSR-7 消息 |
| 发 HTTP 请求 | PSR-18 `ClientInterface` |
| 写 Web 中间件 | PSR-15 `MiddlewareInterface` |
| 从容器取服务 | PSR-11 `ContainerInterface` |
| 简单缓存 | PSR-16 `CacheInterface` |
| 需要缓存项精细控制 | PSR-6 |
| 自动加载 | PSR-4 |

## References

- PHP-FIG 标准列表：<https://www.php-fig.org/psr/>
- PSR-12：<https://www.php-fig.org/psr/psr-12/>
- PSR-15：<https://www.php-fig.org/psr/psr-15/>
- PSR-18：<https://www.php-fig.org/psr/psr-18/>
