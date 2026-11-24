---
name: async-services
description: 使用 ReactPHP / Amp 开发常驻异步服务、API 网关、Worker 与定时任务的指南
---

# 异步服务开发指南

## Overview

PHP 的传统模型是"请求-响应后进程回收"。异步框架让 PHP 进程常驻、事件驱动，适合：

- 高并发 I/O 服务（代理、网关、WebSocket）。
- 常驻 Worker、消息消费。
- 长轮询、流式响应。

代价：内存与状态需自行管理，阻塞调用会拖垮整个事件循环，调试与部署更复杂。使用前先读 `.agents/docs/architecture-scenarios.md` 的"PHP 的适用边界"。

## 一、ReactPHP 基础

```php
<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use React\EventLoop\Loop;
use React\Http\HttpServer;
use React\Http\Message\Response;
use Psr\Http\Message\ServerRequestInterface;

$server = new HttpServer(function (ServerRequestInterface $request): Response {
    return Response::json(['path' => $request->getUri()->getPath()]);
});

$socket = new React\Socket\SocketServer('0.0.0.0:8080');
$server->listen($socket);

echo "Listening on http://0.0.0.0:8080\n";
Loop::run();
```

要点：

- 所有 I/O 必须异步：用 `react/http` 客户端，不用 `file_get_contents()`、同步 PDO。
- 事件循环单线程，任何阻塞都会阻塞全部请求。
- 回调链可用 `react/async` 的 `async/await` 语法改善可读性。

## 二、Amp 基础

```php
<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Amp\Http\Server\HttpServer;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler\ClosureRequestHandler;
use Amp\Http\Server\Response;
use Amp\Socket\InternetAddress;

$handler = new ClosureRequestHandler(function (Request $request): Response {
    return new Response(200, ['content-type' => 'application/json'], '{"ok":true}');
});

$server = new HttpServer(
    [Amp\Socket\listen(new InternetAddress('0.0.0.0', 8080))],
    $handler,
    new Amp\Http\Server\DefaultErrorHandler(),
);

$server->start();
```

- Amp 使用 Fiber 与协程风格，代码更接近同步写法。
- 仍需使用 Amp 的异步客户端，不能混用阻塞调用。
- 与 ReactPHP 不要在同一服务中混用事件循环。

## 三、异步 HTTP 客户端

```php
// ReactPHP
$browser = new React\Http\Browser();
$browser->get('https://api.example.com/users')->then(
    fn (Psr\Http\Message\ResponseInterface $response) => print($response->getBody()),
    fn (Throwable $e) => error_log($e->getMessage()),
);

// Amp
$client = Amp\Http\Client\HttpClientBuilder::buildDefault();
$response = $client->request(new Amp\Http\Client\Request('https://api.example.com/users'));
```

- 设置超时：连接超时、总超时。
- 限制并发：连接池 + 信号量。
- 处理失败：重试需有限次数并带退避。
- 流式处理大响应，避免一次性读入内存。

## 四、常驻进程的陷阱

### 状态污染

```php
// 错误：请求间共享可变状态
final class Context
{
    public static ?User $currentUser = null;
}

// 正确：每次请求创建独立上下文，通过参数传递
```

- 全局变量、静态属性、单例缓存会跨请求保留。
- 请求级数据必须显式传递或使用请求作用域容器。

### 内存泄漏

- 事件监听器注册后不注销。
- 闭包捕获大对象。
- 无界数组/缓存累积。
- 连接未关闭。

监控：定期输出 `memory_get_usage(true)`，设置阈值告警；进程达到上限后优雅重启。

### 阻塞调用

| 阻塞 | 替代 |
|---|---|
| `PDO` / `mysqli` 同步查询 | 异步数据库客户端（如 `react/mysql`）或独立 Worker |
| `file_get_contents()` | 异步文件系统（`react/filesystem` / Amp） |
| `curl_exec()` | 异步 HTTP 客户端 |
| `sleep()` | `Loop::addTimer()` / `delay()` |
| CPU 密集计算 | 子进程 / 队列 / 其他语言服务 |

## 五、API 网关与反向代理

```php
$server = new HttpServer(function (ServerRequestInterface $request) use ($browser): PromiseInterface {
    $upstream = 'https://internal.example.com' . $request->getUri()->getPath();

    return $browser->request(
        $request->getMethod(),
        $upstream,
        $request->getHeaders(),
        (string) $request->getBody()
    )->then(function (ResponseInterface $response): Response {
        return new Response($response->getStatusCode(), $response->getHeaders(), $response->getBody());
    });
});
```

网关职责：

- 认证、限流、路由、请求 ID、日志、响应改写。
- 不透传 `Host`、`Cookie` 等敏感头，按需白名单。
- 上游超时与熔断策略。
- 大响应流式转发。

## 六、Worker 与定时任务

```php
Loop::addPeriodicTimer(60, function () {
    // 每分钟执行
});

Loop::addTimer(5, fn () => doOnce());
```

- 周期任务加重入保护，避免上一轮未完成又启动。
- 任务异常必须捕获，否则可能终止循环。
- 记录开始/结束/耗时/结果。
- 优雅停止：监听信号（Linux）或提供管理接口（Windows 无 `pcntl`）。

Windows 注意：本机未加载 `pcntl`，信号处理与多进程能力受限；可用任务计划程序启动/停止进程，或部署到 Linux。

## 七、部署

- 使用进程管理器（Supervisor / systemd / Windows 服务）托管，异常退出自动拉起。
- 设置最大请求数或内存阈值，定期重启。
- 健康检查端点。
- 日志输出到 stdout/stderr，由平台收集。
- 滚动重启，避免中断。

## 八、测试

- 事件循环代码可用 `Loop::run()` 驱动测试。
- 异步客户端用 mock 实现，不依赖真实网络。
- 超时与失败路径要覆盖。
- 压力测试验证并发与内存。

## 九、何时不用 PHP 异步

| 场景 | 更合适的选择 |
|---|---|
| 纯反向代理/负载均衡 | Nginx / Envoy |
| 超高性能网络服务 | Go / Rust / Node.js |
| CPU 密集型 | Go / Rust / C++ |
| 简单定时任务 | 系统 cron + 短生命周期 CLI |
| 团队无异步运维经验 | 传统 FPM + 队列 |

PHP 异步适合"已有 PHP 生态、需要常驻 I/O 服务"的场景，不是所有高并发的默认答案。

## 十、常见坑

- 在事件循环中调用同步 PDO，整个服务卡住。
- 请求上下文放静态属性，用户数据串号。
- 忘记关闭连接或文件句柄，内存与句柄泄漏。
- 未捕获异常导致进程退出。
- Windows 上依赖 `pcntl` 的信号处理不可用。
- 没有进程守护，崩溃后服务停止。
- 压测只测成功路径，未测超时与重试。

## References

- ReactPHP：<https://reactphp.org/>
- ReactPHP HTTP：<https://github.com/reactphp/http>
- Amp：<https://amphp.org/>
- Amp HTTP Server：<https://amphp.org/http-server/>
- Laravel Octane：<https://laravel.com/docs/octane>
