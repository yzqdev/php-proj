---
name: php-language
description: 现代 PHP 8.x 语言特性、类型系统、OOP、异常、生成器与运行时机制的使用指南
---

# PHP 语言开发指南

## Overview

PHP 8.x 已从"脚本语言"演进为带类型系统、枚举、属性、只读属性、Fiber 的现代语言。写这个工作区的代码时，先确认目标版本（见 `.agents/docs/php-version-matrix.md`），再选择语法。

核心原则：

- 新代码开启 `declare(strict_types=1);`，类型声明尽量完整。
- 优先使用语言原生能力（enum、readonly、match、FCC），而不是约定或魔术字符串。
- 业务边界用异常表达失败，不用返回 `false`/`null` 混合语义。
- 不把 Laravel 特有行为当作 PHP 语言能力。

## 类型系统

```php
<?php
declare(strict_types=1);

final class Money
{
    public function __construct(
        public readonly int $amount,   // 8.1+
        public readonly string $currency,
    ) {
    }

    public function add(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Currency mismatch');
        }
        return new self($this->amount + $other->amount, $this->currency);
    }
}
```

要点：

- `strict_types=1` 只影响当前文件，需在每个 PHP 文件声明。
- 联合类型 `int|string`（8.0）、交叉类型 `A&B`（8.1）、DNF `(A&B)|C`（8.2）。
- `mixed`（8.0）表示任意类型；能写具体类型就不要用 `mixed`。
- `never`（8.1）用于必定抛异常或 `exit` 的函数。
- 可空参数写 `?string`，不要写 `string $x = null`（8.4 起弃用隐式 nullable）。
- 只读属性 8.1 可用，只读类 8.2 可用；只读属性不能在 `__clone` 外重新赋值。

## 数组与字符串

```php
$ids = array_map(fn (array $row): int => (int) $row['id'], $rows);
$indexed = array_column($rows, null, 'id');
$unique  = array_values(array_unique($ids));
$chunk   = array_chunk($ids, 500);

// 8.0+ 字符串函数
if (str_starts_with($path, '/api/') && !str_contains($path, '..')) {
    // ...
}
```

- 数组是有序哈希表，`array_*` 函数大多保留键，注意 `array_values()` 重排。
- 大数组遍历用 `foreach` 引用后必须 `unset($v)`，或改用索引访问。
- 多字节字符串用 `mb_*`；本机已加载 `mbstring` 与 `intl`。
- 金额、汇率用 `brick/math` 或整数分，不用 `float`。

## 日期时间

```php
$now = new DateTimeImmutable('now', new DateTimeZone('Asia/Shanghai'));
$later = $now->modify('+1 day');
$iso = $later->format(DATE_ATOM);
```

- 优先 `DateTimeImmutable`，避免隐式修改。
- 数据库存 UTC，展示时按用户时区转换。
- 复杂操作可用 Carbon，但不要把 Carbon 泄漏到领域模型。

## 函数、闭包与一等可调用

```php
$double = fn (int $x): int => $x * 2;          // 箭头函数自动捕获
$upper = strtoupper(...);                       // 8.1 一等可调用
usort($items, static fn ($a, $b) => $a->id <=> $b->id);
```

- 回调尽量 `static fn`，避免意外绑定 `$this`。
- 一等可调用语法比 `'strtoupper'` 字符串更安全，可被静态分析。
- 闭包捕获大对象时注意生命周期，避免长驻进程内存增长。

## 面向对象

```php
interface Notifier
{
    public function send(string $to, string $message): void;
}

abstract class BaseNotifier implements Notifier
{
    public function __construct(protected readonly LoggerInterface $logger) {}
}

final class MailNotifier extends BaseNotifier
{
    public function send(string $to, string $message): void
    {
        // ...
        $this->logger->info('mail sent', ['to' => $to]);
    }
}
```

- 组合优于继承；继承层级控制在两层以内。
- 接口表达能力，抽象类承载共享实现。
- Trait 只放无状态、可复用的行为片段；不要在 Trait 里访问未声明的属性。
- `final` 用于不希望被继承的类，减少意外耦合。
- 构造器属性提升（8.0）减少样板代码；只读 + 提升是常见组合。

## 枚举

```php
enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function canCancel(): bool
    {
        return $this === self::Pending;
    }

    public static function fromInput(string $value): self
    {
        return self::tryFrom($value) ?? throw new ValueError("Invalid status: {$value}");
    }
}
```

- 用 backed enum 代替字符串常量集合。
- `tryFrom()` 返回 `?self`，`from()` 失败抛 `ValueError`。
- 枚举可带方法，但不应依赖外部服务。

## Attributes

```php
#[Attribute(Attribute::TARGET_METHOD)]
final class Route
{
    public function __construct(
        public readonly string $path,
        public readonly string $method = 'GET',
    ) {}
}

final class UserController
{
    #[Route('/api/users', 'GET')]
    public function index(): array { return []; }
}
```

- Attributes 只是元数据，需配合反射读取。
- 自定义 Attribute 用 `#[Attribute(target)]` 声明作用目标。
- 8.5 起 `#[\NoDiscard]` 可标记返回值必须使用。

## 异常体系

```php
try {
    $this->service->handle($input);
} catch (ValidationException $e) {
    $this->logger->warning('validation failed', ['errors' => $e->errors()]);
    throw $e;
} catch (Throwable $e) {
    $this->logger->error('unexpected', ['exception' => $e]);
    throw new RuntimeException('Internal error', previous: $e);
}
```

- `Throwable` 是 `Exception` 与 `Error` 的共同父接口。
- 不要捕获后静默忽略；至少记录并决定是否重新抛出。
- 自定义异常继承 `RuntimeException` 或项目基类，携带上下文。
- 捕获顺序从具体到宽泛。
- 生产环境关闭 `display_errors`，错误写日志。

## 生成器与 Fiber

```php
function readLines(string $file): Generator
{
    $fh = fopen($file, 'rb');
    try {
        while (($line = fgets($fh)) !== false) {
            yield trim($line);
        }
    } finally {
        fclose($fh);
    }
}

foreach (readLines($path) as $line) {
    // 逐行处理，内存恒定
}
```

- 生成器适合大文件、分页拉取、流式处理。
- Fiber（8.1）用于协作式并发，通常由异步框架封装，业务代码不直接调度。
- 不要在生成器里做阻塞的外部调用而误以为"异步"。

## 反射

```php
$ref = new ReflectionClass(UserController::class);
foreach ($ref->getMethods() as $method) {
    foreach ($method->getAttributes(Route::class) as $attr) {
        $route = $attr->newInstance();
        // 注册路由
    }
}
```

- 反射用于框架、容器、序列化等基础设施，业务代码少用。
- 反射有性能开销，结果可缓存。
- 8.4 起可用惰性对象延迟初始化。

## 内存、引用与垃圾回收

- PHP 使用引用计数 + 循环垃圾回收；循环引用需 `gc_collect_cycles()` 或打破引用。
- `unset()` 只减少引用计数，不保证立即释放内存给操作系统。
- 长驻进程（Worker/Octane/ReactPHP）要避免全局静态缓存无界增长。
- `memory_get_usage(true)` 观察内存；Xdebug 可辅助定位。
- 不要滥用引用 `&`，容易产生难以追踪的副作用。

## 文件、流与上传

```php
$fh = fopen($path, 'rb');
if ($fh === false) {
    throw new RuntimeException("Cannot open {$path}");
}
try {
    while (!feof($fh)) {
        $chunk = fread($fh, 8192);
        // 处理分块
    }
} finally {
    fclose($fh);
}
```

- 大文件用流式读写，不要 `file_get_contents()` 全量读入。
- 上传文件校验 MIME、扩展名、大小，重命名后存储到不可执行目录。
- 路径拼接用 `realpath()` 校验，防止路径穿越。
- `tmpfile()` / `php://temp` 适合临时数据。

## JSON、XML、CSV

```php
$json = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
$data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
```

- 始终传 `JSON_THROW_ON_ERROR`，不要依赖 `json_last_error()`。
- 8.3+ 可用 `json_validate()` 只校验不解析。
- CSV 用 `fgetcsv()`/`fputcsv()`，注意 BOM、分隔符、转义。
- XML 用 `SimpleXML` 或 `DOMDocument`；解析外部 XML 时禁用外部实体（XXE）。

## 常见坑

- 忘记 `declare(strict_types=1)`，导致字符串数字被隐式转换。
- `array_map` 与 `array_filter` 保留键，前端拿到非连续数组变成对象。
- 浮点数比较用 `abs($a - $b) < PHP_FLOAT_EPSILON`，不要 `==`。
- `empty()` 把 `'0'` 判为空，业务判断用 `=== ''` / `=== null`。
- 时区未设置导致日期偏差，启动时统一 `date_default_timezone_set()`。
- 在长驻进程中累积静态数组，内存持续上涨。

## References

- 版本特性：`.agents/docs/php-version-matrix.md`
- PSR 标准：`.agents/docs/psr-standards.md`
- 安全：`.agents/skills/security/SKILL.md`
- PHP 手册：<https://www.php.net/manual/en/>
