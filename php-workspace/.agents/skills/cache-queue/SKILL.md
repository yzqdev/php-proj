---
name: cache-queue
description: Redis 缓存、分布式锁与消息队列在 PHP 项目中的设计、实现与故障处理指南
---

# 缓存与队列开发指南

## Overview

缓存解决"读多写少"的性能问题，队列解决"响应快"与"削峰解耦"的问题。两者都引入最终一致性与运维复杂度，设计时必须先明确失效策略和失败处理。

本机没有 Redis 服务端与 `redis` 扩展，相关内容为参考方案；本地可用文件缓存或 SQLite 验证逻辑。

## 一、缓存

### 客户端选择

| 方案 | 说明 |
|---|---|
| Predis | 纯 PHP 实现，无需扩展，部署简单，性能略低 |
| PhpRedis（ext-redis） | C 扩展，性能更好，需要安装 |
| Symfony Cache | PSR-6/16 抽象，可切换文件/Redis/APCu |
| Laravel Cache | 框架封装，支持多驱动 |

本地未加载 `redis` 扩展，如需 Redis 优先 Predis。

### 缓存模式

**Cache-Aside（最常用）**

```php
function getUserProfile(int $id): array
{
    $key = "user:{$id}:profile";
    $cached = $cache->get($key);
    if ($cached !== null) {
        return $cached;
    }

    $profile = $this->repository->loadProfile($id);
    $cache->set($key, $profile, 600);
    return $profile;
}
```

**写时更新策略**

```text
更新数据库 → 删除缓存（而不是直接覆盖）
```

- 先更新数据库，再删缓存，降低脏数据窗口。
- 延迟双删用于高并发场景，但仍不能完全保证一致。
- 缓存值带版本号，便于整体失效。

### 键设计

```text
业务域:实体:标识:字段:版本
user:42:profile:v2
order:2026-11:stats
```

- 统一前缀，便于批量清理与监控。
- 包含版本号，结构变更时切换版本即可失效旧数据。
- 避免超长键和用户可控的键片段（防注入）。

### 常见问题

| 问题 | 现象 | 处理 |
|---|---|---|
| 缓存穿透 | 查询不存在的数据，每次都打数据库 | 缓存空值（短 TTL）或布隆过滤器 |
| 缓存雪崩 | 大量键同时过期 | TTL 加随机抖动，分批预热 |
| 缓存击穿 | 热点键过期瞬间大量请求 | 互斥锁重建，或逻辑过期 |
| 热 Key | 单个键访问过高 | 本地缓存 + 分片 |
| 大 Key | 单键体积过大 | 拆分、压缩、异步删除 |

### TTL 设置

- 按数据变更频率设置，不要所有键同一 TTL。
- 加 0~10% 随机抖动，避免同时失效。
- 关键数据可设置较长 TTL + 主动失效。

## 二、分布式锁

### 基本实现

```php
final class RedisLock
{
    public function __construct(private readonly Predis\Client $redis) {}

    public function acquire(string $key, string $owner, int $ttlMs = 5000): bool
    {
        $result = $this->redis->set($key, $owner, 'PX', $ttlMs, 'NX');
        return $result !== null;
    }

    public function release(string $key, string $owner): bool
    {
        $script = <<<'LUA'
        if redis.call("GET", KEYS[1]) == ARGV[1] then
            return redis.call("DEL", KEYS[1])
        else
            return 0
        end
        LUA;

        return (bool) $this->redis->eval($script, 1, $key, $owner);
    }
}
```

要点：

- `SET key value NX PX ttl` 是原子加锁。
- 释放必须校验持有者，用 Lua 保证原子性。
- 锁必须有 TTL，防止进程崩溃后死锁。
- 业务执行时间可能超过 TTL 时，需要续期（watchdog）或拆分任务。
- 单 Redis 实例的锁在故障切换时可能失效；强一致场景需要 Redlock 或数据库唯一约束。

### 适用与不适用

- 适合：防止重复任务、避免缓存重建风暴、短时互斥。
- 不适合：跨系统强一致、长事务、金融级扣减（应使用数据库行锁/唯一约束）。

## 三、队列

### 队列选型

| 方案 | 特点 | 适用 |
|---|---|---|
| Laravel Queue | 框架集成，驱动多（Redis/database/SQS） | Laravel 项目 |
| Symfony Messenger | 消息总线 + 传输，支持重试与失败队列 | Symfony 项目 |
| RabbitMQ（php-amqplib） | 功能完整，支持路由、确认、死信 | 跨语言、复杂路由 |
| Redis List/Stream | 轻量，部署简单 | 中小项目 |
| Beanstalkd | 简单可靠 | 轻量任务 |

### 生产者/消费者模型

```text
Producer → Exchange/Queue → Consumer → ACK/NACK → 重试或死信
```

必须明确：

- 消息体结构（JSON + 版本号）。
- 投递语义：至少一次（at-least-once）最常见，因此消费端必须幂等。
- 失败重试次数、退避策略、死信队列。
- 消息大小上限与超时。

### 幂等设计

```php
final class ProcessPayment
{
    public function handle(PaymentMessage $message): void
    {
        $key = "payment:{$message->paymentId}";
        if (! $this->store->markProcessed($key)) {
            return; // 已处理，直接返回
        }

        $this->gateway->charge($message);
    }
}
```

- 用业务唯一键去重（订单号、支付单号）。
- 去重记录与业务操作尽量在同一事务，或使用状态机。
- 不要依赖"消息只投递一次"。

### 重试与死信

```php
// Laravel 任务
public int $tries = 5;
public array $backoff = [10, 30, 60, 120];

public function failed(Throwable $e): void
{
    Log::channel('queue')->error('job failed', ['exception' => $e]);
}
```

- 指数退避，避免雪崩式重试。
- 永久失败进入死信队列并告警。
- 区分可重试错误（网络、锁冲突）与不可重试错误（参数错误）。

### Worker 管理

```powershell
php artisan queue:work --queue=high,default --tries=3 --timeout=60
```

- 生产用 Supervisor（Linux）或 Windows 服务/任务计划程序托管。
- `queue:work` 常驻，`queue:listen` 每次重启，不适合生产。
- 设置 `--timeout` 小于 `retry_after`，避免重复消费。
- Worker 数量与数据库连接数、Redis 连接数匹配。
- 部署后重启 Worker 加载新代码。

### 延迟任务与定时任务

- 延迟任务用队列的延迟投递或 Redis ZSET。
- 定时任务用框架 Scheduler 或系统计划任务，避免用 `sleep` 循环。
- 任务加重入锁，防止重叠执行。

## 四、可观测性

- 记录入队、出队、成功、失败、耗时、重试次数。
- 队列积压告警：长度超过阈值。
- 死信队列数量告警。
- 为消息带 `trace_id`，串联生产与消费日志。

## 五、常见坑

- 锁的 TTL 短于业务耗时，导致锁提前释放。
- 释放锁不校验持有者，误删他人锁。
- 消费端不幂等，重试造成重复扣款/重复发货。
- 队列任务序列化了不可序列化的对象（如 PDO 连接、闭包）。
- Worker 未重启，部署后仍执行旧代码。
- `retry_after` 小于任务超时，同一消息被多个 Worker 消费。
- 缓存与数据库双写不一致，未定义失效顺序。
- 用 Redis 做唯一数据源却没有持久化策略。

## References

- Redis 文档：<https://redis.io/docs/>
- Redis 分布式锁：<https://redis.io/docs/latest/develop/use/patterns/distributed-locks/>
- Laravel 队列：<https://laravel.com/docs/queues>
- Symfony Messenger：<https://symfony.com/doc/current/messenger.html>
- RabbitMQ：<https://www.rabbitmq.com/docs>
- Predis：<https://github.com/predis/predis>
