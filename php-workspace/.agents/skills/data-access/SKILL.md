---
name: data-access
description: PHP 数据库访问层设计：PDO、ORM 选型、事务、迁移、N+1 与性能优化指南
---

# 数据库访问开发指南

## Overview

PHP 没有跨请求的连接池概念（PHP-FPM 每个请求独立），连接管理与 ORM 选择直接影响性能与正确性。数据访问层通常分三类：

1. **PDO + 手写 SQL**：可控、轻量，适合中小项目和复杂查询。
2. **查询构建器**（Laravel Query Builder、Doctrine DBAL）：兼顾可读性与控制力。
3. **ORM**（Eloquent、Doctrine ORM、Cycle ORM）：领域模型映射，适合复杂业务。

同一个项目不要混用多套数据访问方式做同一件事。

## PDO 基础

```php
<?php
declare(strict_types=1);

$dsn = 'mysql:host=127.0.0.1;port=3306;dbname=acme;charset=utf8mb4';
$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false, // 使用真实预处理
    PDO::ATTR_PERSISTENT         => false, // 谨慎使用持久连接
]);
```

- `ERRMODE_EXCEPTION`：错误抛异常，避免静默失败。
- `EMULATE_PREPARES=false`：使用数据库真实预处理，类型更严格。
- `charset=utf8mb4`：支持完整 Unicode（含 emoji）。
- 持久连接在 FPM 下可能累积状态，默认关闭。

## 预处理语句与 SQL 注入

```php
// 正确：占位符
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email AND status = :status');
$stmt->execute(['email' => $email, 'status' => 'active']);
$user = $stmt->fetch();

// 错误：拼接
$sql = "SELECT * FROM users WHERE email = '{$email}'"; // 禁止
```

- 值用占位符；表名、列名、排序方向这类标识符不能参数化，必须白名单映射。
- `LIMIT`/`OFFSET` 用 `(int)` 强转或 `bindValue(..., PDO::PARAM_INT)`。
- `IN (...)` 需要动态生成占位符，占位符数量与数组长度一致。
- 不用 `PDO::quote()` 代替预处理。

## 事务

```php
$pdo->beginTransaction();
try {
    $orders->insert($order);
    $stock->decrement($order->items());
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    throw $e;
}
```

- 事务边界放在 Service 层，Repository 只执行单条操作。
- 事务内避免 HTTP 调用、文件 IO、长时间计算。
- 嵌套事务用 Savepoint 或框架封装，不要重复 `beginTransaction()`。
- 死锁要捕获并重试（有限次数）。

## Repository 模式

```php
interface OrderRepository
{
    public function find(int $id): ?Order;
    public function save(Order $order): void;
}

final class PdoOrderRepository implements OrderRepository
{
    public function __construct(private readonly PDO $pdo) {}

    public function find(int $id): ?Order
    {
        $stmt = $this->pdo->prepare('SELECT * FROM orders WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row === false ? null : Order::fromRow($row);
    }

    public function save(Order $order): void
    {
        // INSERT / UPDATE
    }
}
```

- Repository 是领域层接口，基础设施层实现。
- 返回领域对象或数组，不返回 `PDOStatement`。
- 复杂查询可以单独建 Query 对象，避免 Repository 膨胀。

## ORM 选择

| ORM | 风格 | 适用 |
|---|---|---|
| Eloquent | Active Record | Laravel 项目、CRUD 密集 |
| Doctrine ORM | Data Mapper | 复杂领域模型、长期维护 |
| Cycle ORM | Data Mapper | 需要 ORM 但不想用 Doctrine |
| Medoo | 轻量查询 | 小项目快速访问 |

- Active Record 模型直接对应表，简单但领域逻辑容易混入模型。
- Data Mapper 把实体与持久化分离，更适合复杂领域。
- 不要在 Doctrine 项目中混用 Eloquent。

## 迁移

- Laravel：`php artisan make:migration` / `migrate`。
- Doctrine：`doctrine/migrations`。
- 独立工具：Phinx。

规则：

- 迁移文件一旦进入生产不再修改，变更用新迁移。
- 每个迁移可回滚（`down()`），并验证回滚。
- 大表加索引用在线 DDL 或分批，避免长时间锁表。
- 数据迁移与结构迁移分开，便于失败重试。

## N+1 与性能

```php
// N+1：每条订单再查一次用户
foreach (Order::all() as $order) {
    echo $order->user->name;
}

// 预加载
foreach (Order::with('user')->get() as $order) {
    echo $order->user->name;
}
```

常见优化：

- 列表查询预加载关联（`with` / `JOIN FETCH`）。
- 只查需要的列，避免 `SELECT *`。
- 分页用 `LIMIT/OFFSET`；深分页用游标（`WHERE id > ?`）。
- 批量插入/更新用一条多值语句或事务。
- 给高频查询条件建索引，用 `EXPLAIN` 验证。
- 避免在 SQL 中对索引列使用函数。

## 连接管理

- PHP-FPM 每请求建立连接，请求结束释放；短连接开销可通过持久连接分摊，但要评估状态污染。
- 常驻进程（Octane/Worker/ReactPHP）连接长期存活，必须处理断线重连与心跳。
- 读写在分离时，主从延迟需要业务容忍或强制读主。
- 连接数受数据库 `max_connections` 限制，Worker 数量要匹配。

## 多数据库

- 业务库与分析库分离，避免 OLAP 查询拖慢 OLTP。
- 跨库事务不要依赖数据库层，改为最终一致性或补偿。
- 连接配置集中管理，不在代码中硬编码 DSN。

## 数据安全

- 所有输入走参数化查询。
- 数据库账号最小权限，应用账号不授予 `DROP`/`GRANT`。
- 敏感字段加密存储（如手机号、身份证），密钥独立管理。
- 备份与恢复演练属于部署流程，不只依赖迁移。

## 常见坑

- `EMULATE_PREPARES=true` 下整数被当字符串，影响索引与类型比较。
- 事务未提交或未回滚，连接被占用。
- 分页 `OFFSET` 过大导致慢查询。
- 软删除后唯一索引冲突（已删除记录仍占用唯一值）。
- 时区不一致导致时间字段偏移。
- 长事务 + 外部调用造成锁等待。

## References

- PDO：<https://www.php.net/manual/en/book.pdo.php>
- Doctrine ORM：<https://www.doctrine-project.org/projects/orm.html>
- Laravel Eloquent：<https://laravel.com/docs/eloquent>
- 索引与 EXPLAIN 以所用数据库官方文档为准（MySQL / PostgreSQL / SQLite）
