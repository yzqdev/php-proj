# PHP 版本特性与兼容性矩阵

本文档用于在写代码前判断"这个语法/API 能不能用"。PHP 生态的版本差异同时体现在语言语法、标准库函数和扩展行为上，误用会直接导致语法错误或运行时异常。

## 一、先确定目标版本

不要用本机 PHP 版本推断项目版本。按以下顺序确认：

1. `composer.json` 的 `require.php` 约束。
2. `composer.json` 的 `config.platform.php`（部署环境模拟）。
3. 部署环境的 `php -v`。
4. CI 配置中的 PHP matrix。

三者不一致时，以**实际运行环境**为准，并在 `composer.json` 中用 `config.platform.php` 固定，避免本地安装高版本依赖却在低版本服务器报错。

```json
{
  "require": {
    "php": "^8.1"
  },
  "config": {
    "platform": {
      "php": "8.1.31"
    }
  }
}
```

## 二、版本支持状态（截至 2026-11）

| 版本 | 状态 | 说明 |
|---|---|---|
| PHP 7.4 | 已停止支持 | 不得用于新项目；存量项目应规划升级 |
| PHP 8.0 | 已停止支持 | 仅维护旧项目 |
| PHP 8.1 | 安全支持已结束 | 不建议新项目使用 |
| PHP 8.2 | 安全维护中 | 仍可用，但应规划升级 |
| PHP 8.3 | 安全维护中 | 存量项目常见基线 |
| PHP 8.4 | 安全维护中 | 可使用 8.4 新特性 |
| PHP 8.5 | 当前版本 | 本机 CLI 版本，可使用 8.5 新特性 |

具体日期以 <https://www.php.net/supported-versions.php> 为准。生产项目应至少保持在安全维护期内。

## 三、按版本划分的语言特性

### PHP 7.4

- 类型化属性：`public int $id;`
- 箭头函数：`fn ($x) => $x * 2`
- 空合并赋值：`$a ??= 'default'`
- 数组展开：`[...$a, ...$b]`
- 数值分隔符：`1_000_000`

### PHP 8.0

| 特性 | 示例 |
|---|---|
| 联合类型 | `int\|string $id` |
| 命名参数 | `foo(bar: 1)` |
| Attributes | `#[Route('/api')]` |
| match 表达式 | `match($x) { 1 => 'a', default => 'b' }` |
| 构造器属性提升 | `public function __construct(private int $id) {}` |
| nullsafe 运算符 | `$user?->profile?->name` |
| throw 表达式 | `$x ?? throw new RuntimeException()` |
| 字符串函数 | `str_contains()`、`str_starts_with()`、`str_ends_with()` |
| 静态返回类型 | `public function with(): static` |
| mixed 类型 | `mixed $value` |
| JIT | 通过 OPcache 配置启用，非默认 |

### PHP 8.1

| 特性 | 示例 |
|---|---|
| 枚举 | `enum Status: string { case Active = 'active'; }` |
| readonly 属性 | `public readonly int $id` |
| Fiber | `new Fiber(fn () => ...)` |
| 一等可调用语法 | `strlen(...)`、`$obj->method(...)` |
| never 返回类型 | `function fail(): never` |
| 交叉类型 | `Countable&Iterator $x` |
| new 用于初始化器 | `function __construct(private Logger $l = new NullLogger())` |
| 纯交集之外的常量 | 类常量可为 `final` |
| 数组解包支持字符串键 | `[...$map]` |

### PHP 8.2

| 特性 | 示例 |
|---|---|
| readonly 类 | `readonly class Point {}` |
| DNF 类型 | `(A&B)\|C $x` |
| true / false / null 独立类型 | `function f(): true` |
| Trait 常量 | `trait T { public const X = 1; }` |
| `#[SensitiveParameter]` | 避免敏感参数进入堆栈 |
| 动态属性弃用 | 未声明属性赋值触发弃用警告，需 `#[AllowDynamicProperties]` 或改用数组/值对象 |

### PHP 8.3

| 特性 | 示例 |
|---|---|
| 类型化类常量 | `public const string NAME = 'x';` |
| `json_validate()` | 只校验 JSON 合法性，不生成值 |
| `#[\Override]` | 标记方法必须覆盖父类方法 |
| 动态类常量获取 | `$class::{$name}` |
| 任意静态变量初始化 | `static $x = foo();` |
| 只读属性深克隆修正 | `clone` 后可重新初始化 readonly 属性 |
| `Randomizer::getBytesFromString()` | 安全随机字符串 |

### PHP 8.4

| 特性 | 示例 |
|---|---|
| Property Hooks | `public string $name { set => ucfirst($value); }` |
| 非对称可见性 | `public private(set) string $name` |
| 惰性对象 | `ReflectionClass::newLazyGhost()`、`newLazyProxy()` |
| `#[\Deprecated]` | 标记函数/方法/常量弃用 |
| 无括号 new 链式调用 | `new Foo()->bar()` |
| `request_parse_body()` | 解析非 POST 的 multipart 请求 |
| `Dom\` 命名空间 | HTML5 兼容的新 DOM 类（旧 `DOMDocument` 仍可用） |
| `#[\Override]` 扩展 | 可用于属性（8.5 起，见下） |

### PHP 8.5

| 特性 | 示例 / 说明 |
|---|---|
| 管道运算符 | `$result = "Hello" \|> strtoupper(...)` |
| `clone` 带属性覆盖 | `clone($obj, ['name' => 'new'])`，可重新赋值 readonly 属性 |
| `#[\NoDiscard]` | 标记返回值必须被使用；`(void)` 显式忽略 |
| 常量上的 Attributes | `#[\Deprecated] const X = 1;` |
| `#[\Override]` 用于属性 | 属性覆写校验 |
| 静态属性非对称可见性 | `public private(set) static string $x` |
| 致命错误包含堆栈 | 超时等 Fatal Error 附带 backtrace |
| `array_first()` / `array_last()` | 取数组首尾值 |
| URI 扩展 | 默认启用的 `uri` 扩展，支持 RFC 3986 与 WHATWG URL |
| `#[\DelayedTargetValidation]` | 延迟 Attribute 目标校验到运行时 |

8.5 的完整清单以 <https://www.php.net/manual/en/migration85.new-features.php> 为准。

## 四、常见破坏性变更（升级时重点检查）

| 升级路径 | 重点风险 |
|---|---|
| 7.4 → 8.0 | 默认参数顺序、`match` 严格比较、内部函数参数类型更严格、未定义常量报错 |
| 8.0 → 8.1 | 隐式 float→int 转换弃用、`${var}` 字符串插值弃用、`Serializable` 弃用、返回类型暂变 |
| 8.1 → 8.2 | 动态属性弃用、字符串插值弃用、`utf8_encode()`/`utf8_decode()` 弃用、部分 `${}` 行为移除 |
| 8.2 → 8.3 | 负数组索引行为、`range()` 边界、`assert()` 行为、部分弃用项移除 |
| 8.3 → 8.4 | 隐式 nullable 参数弃用（`function f(string $x = null)` 应写 `?string`）、`E_STRICT` 常量弃用、部分 session/过滤器行为变化 |
| 8.4 → 8.5 | 以 migration85.incompatible 为准；重点检查非标准扩展与自定义错误处理器 |

完整破坏性变更见各版本 `migrationXY.incompatible.php`。

## 五、版本兼容策略

1. **代码层面**：新项目优先 8.3+；需要兼容 8.1/8.2 时，8.4/8.5 新特性一律不用。
2. **依赖层面**：`composer update` 前先看目标库的 `require.php`；用 `composer why-not php 8.1` 定位阻塞包。
3. **静态分析层面**：PHPStan/Psalm 配置 `phpVersion`，让分析器按目标版本报告不可用 API。
4. **CI 层面**：按支持范围跑 matrix，例如 `8.2`、`8.3`、`8.4`、`8.5`。
5. **文档层面**：示例代码标注最低版本，例如"PHP 8.1+"。
6. **运行时层面**：用 `config.platform.php` 固定部署版本，避免本地解析出高版本依赖。

## 六、快速判断清单

写代码前问四个问题：

- 项目 `require.php` 最低版本是多少？
- 我要用的函数/语法是哪一版引入的？
- 依赖库是否支持该最低版本？
- 部署环境是否与 `config.platform.php` 一致？

无法确认时，优先选择在最低支持版本上可用的写法，或明确向用户说明版本假设。

## References

- PHP 手册：<https://www.php.net/manual/en/>
- 版本支持：<https://www.php.net/supported-versions.php>
- 各版本迁移指南：`https://www.php.net/manual/en/migration80.php` 至 `migration85.php`
- Composer platform 配置：<https://getcomposer.org/doc/06-config.md#platform>
