---
name: observability
description: Monolog 日志体系、异常追踪、Xdebug 调试与 PHP 性能分析的开发指南
---

# 可观测性与调试指南

## Overview

"能定位问题"比"没有报错"更重要。可观测性由三部分组成：

- **日志**：发生了什么，带上下文。
- **异常与错误处理**：失败如何被发现与上报。
- **调试与性能分析**：问题出在哪个函数、哪条 SQL、哪次调用。

## 一、日志（Monolog + PSR-3）

### 依赖接口而非实现

```php
use Psr\Log\LoggerInterface;

final class OrderService
{
    public function __construct(private readonly LoggerInterface $logger) {}

    public function pay(Order $order): void
    {
        $this->logger->info('order.pay.start', [
            'order_id' => $order->id,
            'amount' => $order->amount,
        ]);
    }
}
```

- 业务代码依赖 `LoggerInterface`。
- 只有基础设施层（容器配置）知道 Monolog。

### 渠道划分

```php
$logger = new Logger('app');

// 应用日志
$logger->pushHandler(new RotatingFileHandler(
    __DIR__ . '/../storage/logs/app.log', 14, Logger::INFO
));

// 审计日志（单独文件，长期保留）
$audit = new Logger('audit');
$audit->pushHandler(new RotatingFileHandler(
    __DIR__ . '/../storage/logs/audit.log', 90, Logger::INFO
));

// 队列日志
$queue = new Logger('queue');
$queue->pushHandler(new StreamHandler('php://stderr', Logger::WARNING));
```

建议渠道：

| 渠道 | 内容 | 级别 |
|---|---|---|
| app | 业务关键流程 | INFO 及以上 |
| api | 请求/响应摘要、耗时 | INFO |
| queue | 任务开始/结束/失败 | INFO / ERROR |
| audit | 登录、权限、资金变更 | INFO，长期保留 |
| error | 未捕获异常 | ERROR+ |

### 日志级别

| 级别 | 用途 |
|---|---|
| emergency | 系统不可用 |
| alert | 必须立即处理 |
| critical | 严重故障 |
| error | 运行时错误，需关注 |
| warning | 异常但可继续 |
| notice | 正常但重要 |
| info | 关键业务流程 |
| debug | 调试细节，生产关闭 |

### 结构化日志

```php
$logger->pushProcessor(new PsrLogMessageProcessor());
$logger->pushProcessor(function (LogRecord $record): LogRecord {
    $record->extra['request_id'] = RequestContext::id();
    $record->extra['user_id'] = RequestContext::userId();
    return $record;
});

$logger->pushHandler(
    (new StreamHandler('php://stdout'))
        ->setFormatter(new JsonFormatter(JsonFormatter::BATCH_MODE_JSON, true))
);
```

- 生产环境用 JSON，便于日志平台检索。
- 统一注入 `request_id`、`user_id`、`trace_id`。
- 消息用稳定的英文键（如 `order.pay.start`），变量放 context。

### 异常日志

```php
try {
    $this->gateway->charge($order);
} catch (PaymentException $e) {
    $this->logger->error('payment.failed', [
        'order_id' => $order->id,
        'exception' => $e, // 记录对象，Monolog 会输出堆栈
    ]);
    throw $e;
}
```

- 传 `exception` 键而不是 `$e->getMessage()`。
- 避免重复记录同一异常（中间件与业务都记）。
- 日志中不得出现密码、Token、身份证、银行卡。

### 日志轮转与保留

- 文件日志按天/大小轮转，保留 14~90 天。
- 容器环境输出到 stdout/stderr，由平台收集。
- 日志目录不可被 Web 直接访问。
- 监控日志量，防止异常循环写爆磁盘。

## 二、错误与异常处理

### 分层策略

```text
业务异常（可预期）  → 映射为 4xx，记录 warning
系统异常（不可预期）→ 映射为 5xx，记录 error + 堆栈 + 告警
```

```php
set_exception_handler(function (Throwable $e): void {
    error_log((string) $e);
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error']);
});
```

- 生产 `display_errors=Off`、`log_errors=On`。
- 注册 `set_error_handler()` 把警告转为异常，便于统一处理。
- 致命错误用 `register_shutdown_function()` 记录。
- 不把堆栈返回给客户端。

## 三、调试（Xdebug）

本机已加载 Xdebug 3.5.3。常用能力：

| 能力 | 用途 |
|---|---|
| Step Debug | 断点、单步、变量查看 |
| Develop | `var_dump()` 美化 |
| Coverage | 测试覆盖率 |
| Profiler | 函数级性能分析 |

`php.ini` 或环境变量配置（Xdebug 3）：

```ini
xdebug.mode=debug,develop
xdebug.start_with_request=trigger
xdebug.client_host=127.0.0.1
xdebug.client_port=9003
xdebug.log_level=0
```

- 生产环境关闭 Xdebug，性能开销明显。
- IDE 监听 9003 端口，浏览器/CLI 触发调试。
- CLI 调试：`php -dxdebug.mode=debug script.php`。

## 四、性能分析

### 先定位瓶颈

```text
慢在哪一层？网络 → Web Server → PHP → 数据库 → 外部服务
```

手段：

- 应用日志记录关键阶段耗时。
- 数据库慢查询日志 + `EXPLAIN`。
- PHP-FPM slowlog 捕获慢请求堆栈。
- Xdebug Profiler 或 Blackfire 生成函数级报告。
- `microtime(true)` 打点做粗粒度测量。

### OPcache

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0   ; 生产关闭，部署后重启 FPM
opcache.jit=disable              ; 按需开启，先测收益
```

- 生产关闭时间戳校验，减少 stat 调用。
- 部署后必须重启 PHP-FPM 或 reload，否则仍执行旧字节码。
- 本机 CLI 下 `opcache.enable_cli=0`，CLI 性能分析不代表 FPM。

### 内存分析

- `memory_get_usage(true)` / `memory_get_peak_usage(true)` 打点。
- `memory_limit` 不足时报 "Allowed memory size exhausted"，先找大数组/大文件读取。
- 长驻进程内存持续上涨：检查静态缓存、事件监听器、闭包引用。
- 用 Xdebug 或 `meminfo` 扩展辅助。

### PHP-FPM 状态

```ini
pm.status_path = /status
request_slowlog_timeout = 5s
slowlog = /var/log/php-fpm/slow.log
```

- `pm.status` 查看活跃进程、队列、慢请求。
- slowlog 抓取慢请求的 PHP 堆栈。
- `pm.max_children` 根据内存与并发调整。

## 五、慢请求定位流程

1. 看访问日志与 FPM slowlog，确认慢在哪个 URL。
2. 看应用日志的阶段耗时（鉴权、DB、外部调用）。
3. 看数据库慢查询与执行计划。
4. 看外部 HTTP 超时与重试。
5. 用 profiler 深入函数级热点。
6. 修复后压测对比。

## 六、常见坑

- 日志里打印了整个请求体，包含密码与 Token。
- 只记录 `$e->getMessage()`，丢失堆栈。
- 循环内打日志，日志量暴增。
- 生产开启 Xdebug 或 `display_errors`。
- OPcache 开启时间戳校验，频繁 stat 影响性能。
- 部署后未重启 FPM，代码未生效。
- 长驻进程未清理静态缓存，内存泄漏。

## References

- Monolog：<https://github.com/Seldaek/monolog>
- PSR-3：<https://www.php-fig.org/psr/psr-3/>
- Xdebug：<https://xdebug.org/docs/>
- OPcache：<https://www.php.net/manual/en/book.opcache.php>
- Blackfire：<https://blackfire.io/docs>
- PHP-FPM：<https://www.php.net/manual/en/install.fpm.php>
