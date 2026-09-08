
# AGENTS.md — PHP 项目协作约定

本文件供 AI 编码助手（Codex / Cursor / Copilot 等）在本仓库中生成或修改 PHP 代码时遵循。
只包含 PHP 语言层面的语法与风格规则，不涉及业务与部署。

## 1. 基础环境

- PHP 版本：**8.2+**（可用 8.3/8.4/8.5 特性，不使用 8.0 以下写法）
- 遵循 **PSR-12** 代码风格 + **PSR-4** 自动加载
- 所有文件使用 `UTF-8 without BOM`、`LF` 换行、UNIX 风格结尾换行

## 2. 文件与标签

- 纯 PHP 文件：**省略结束标签 `?>`**，防止尾部输出
- 短标签 `<?` 和 ASP 标签 `<%`：**禁止使用**，只用 `<?php`
- 每个文件只做一件事：一个文件一个类/接口/枚举，文件名与类名完全一致（含大小写）

```php
<?php

declare(strict_types=1);

namespace App\Service;

final class UserService
{
}
```

- 每个 PHP 文件第二行必须有 `declare(strict_types=1);`（在命名空间之前）

## 3. 命名规范

| 对象 | 规则 | 示例 |
|---|---|---|
| 类 / 接口 / Trait / 枚举 | `UpperCamelCase` | `OrderRepository` |
| 方法 / 函数 | `lowerCamelCase` | `getById()` |
| 变量 / 属性 | `lowerCamelCase` | `$userName` |
| 常量 | `UPPER_SNAKE_CASE` | `MAX_RETRY` |
| 命名空间 | `UpperCamelCase` 分段 | `App\Domain\Order` |
| 私有成员 | **不加**下划线前缀 | `$this->db` |

## 4. 类型与声明

- 能写类型就写：**参数类型、返回类型、属性类型**全部显式声明
- 属性必须有类型，禁止裸 `public $name;`
- 能用 `?int` 表示可空，不要用 `int $x = null` 隐式可空
- 优先使用 PHP 8 联合类型与构造器属性提升

```php
public function __construct(
    private readonly PDO $pdo,
    private readonly LoggerInterface $logger,
) {
}

public function find(int $id): ?array
{
    return null;
}
```

- 不需要修改的类加 `final`，不需要修改的属性加 `readonly`
- 字面量类型：优先 `int|float|string|bool|null`，避免 `mixed`；确需时用 `mixed` 并立刻收窄

## 5. 控制结构

- 大括号：**控制结构另起一行**（PSR-12），流程关键字后保留一个空格
- 禁止省略大括号的单行 `if / for / while`
- `elseif` 不用 `else if`
- 模板文件（`.phtml`）中可使用替代语法 `if: ... endif;`

```php
if ($user === null) {
    return null;
}

foreach ($items as $key => $item) {
    // ...
}

match ($status) {
    1 => 'a',
    2 => 'b',
    default => 'c',
};
```

- 简单多分支赋值用 `match`（严格比较）替代 `switch`
- 早返回优先，减少嵌套层级（最多 2 层）

## 6. 比较与运算符

- 比较一律使用 `===` / `!==`，**禁止 `==` / `!=`**（除显式需要松散比较并加注释）
- 字符串比较用 `strcmp()` 或 `===`；不要用 `==` 比较数字字符串
- 使用 `??`（null 合并）替代 `isset($a) ? $a : $b`；PHP 7.4+ 用 `??=`
- 使用 `?->`（nullsafe）访问可能为 null 的链式对象
- 拼接字符串用 `.` 与 `.=`；双引号内变量插值用 `{$var}` 包裹，避免裸 `$var[0]` 歧义
- 用 `str_contains()` / `str_starts_with()` / `str_ends_with()`（PHP 8+），不用 `strpos(...) !== false`

## 7. 数组与字符串

- 数组一律短语法 `[]`，**禁止 `array()`**
- 数组元素末尾保留尾逗号（多行数组）
- 字符串：无变量插值用单引号，有插值用双引号
- 多行文本优先 heredoc / nowdoc，结束标识符必须顶格

```php
$sql = <<<SQL
    SELECT * FROM users WHERE id = :id
    SQL;
```

## 8. 函数与类

- 函数/方法职责单一，参数不超过 5 个；超出用 DTO 或数组（并写 `@param` 形状）
- 参数顺序：必填在前，可选在后
- 尽量纯函数；有副作用的写在 Service 层
- 静态方法仅用于工厂或与实例无关的工具，**不要用 `self::` 做隐式全局状态**
- trait 仅作最后手段；优先组合（composition）

```php
interface UserRepositoryInterface
{
    public function find(int $id): ?User;
}

enum Status: string
{
    case Active = 'active';
    case Disabled = 'disabled';
}
```

## 9. 错误与异常

- 业务错误用异常，不用 `die()` / `exit()` / `trigger_error()`
- 捕获具体异常类型，**禁止空 `catch`**；至少要记录日志或重新抛出
- 不要用 `@` 抑制错误符号
- 开发环境开启 `E_ALL` 并显示错误，生产环境记录日志不输出

```php
try {
    $this->pdo->beginTransaction();
    // ...
    $this->pdo->commit();
} catch (PDOException $e) {
    $this->pdo->rollBack();
    throw new UserSaveException('保存失败', previous: $e);
}
```

## 10. 安全与常见坑

- 数据库一律用 **PDO 预处理**（`prepare()` + 绑定参数），禁止字符串拼接 SQL
- 输出转义：`htmlspecialchars($s, ENT_QUOTES, 'UTF-8')`
- 禁止 `extract()`、`eval()`、`$GLOBALS` 动态变量、`register_globals` 式写法
- 用 `random_bytes()` / `random_int()` 生成随机值，不用 `rand()` / `mt_rand()`
- 密码用 `password_hash()` / `password_verify()`，不用 `md5()` / `sha1()`
- 文件包含用 `require_once` / `include_once` 绝对路径，禁止用户可控路径
- 引用 `&` 除非明确必要，否则不用

## 11. 注释与文档

- 用 `//` 单行、`/* */` 多行，注释解释 **为什么** 而不是 **做什么**
- 类与方法上写 PHPDoc（`@param` / `@return` / `@throws`），类型已声明时不必重复标量类型
- 不保留被注释掉的代码块，用版本控制管理历史
- 使用 `@deprecated` 标记废弃方法并指明替代方案

## 12. 代码组织

- 缩进 4 空格，**禁止 Tab**
- 单行长度建议 ≤ 120 字符
- `use` 导入放文件顶部，按字母序分组（类 / 函数 / 常量）
- 类内顺序：常量 → 属性 → 构造器 → 公有方法 → 保护方法 → 私有方法
- 方法之间一个空行，类之间不留多余空行，行尾无空白字符

## 13. 自检清单（生成代码前确认）

- [ ] `declare(strict_types=1);` 已加，文件无结束标签
- [ ] 所有参数 / 返回 / 属性有类型声明
- [ ] 比较用 `===`，无 `==` / `!=`
- [ ] 数组用 `[]`，无 `array()`
- [ ] SQL 用预处理，输出已转义
- [ ] 无 `die()` / `eval()` / `@` 抑制符 / 空 `catch`
- [ ] 命名符合 UpperCamelCase / lowerCamelCase 约定