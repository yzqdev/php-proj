# 常见架构场景参考

本文档把"要做一个什么东西"映射到"用哪些组件、怎么组织、注意什么"。每个场景给出目标、组件、流程、关键决策与风险，详细实现见对应 skill。

## 1. Laravel 完整 REST API

**目标**：业务系统对外的 JSON API，含认证、校验、分页、错误格式。

**组件**：Laravel 路由 + Controller + FormRequest + API Resource + Eloquent + Sanctum + 队列 + 日志。

**流程**：路由 → 中间件（认证/限流）→ FormRequest 校验 → Service → Repository/Model → API Resource → JSON。

**关键决策**：
- 用 Service 层承载业务，Controller 保持薄。
- 输出统一用 API Resource，不直接返回 Model。
- 错误统一由 `Handler` 转换为固定结构。

**风险**：N+1 查询、Mass Assignment、把业务逻辑写进 Controller。

参考：`.agents/skills/laravel/SKILL.md`、`.agents/skills/api-design/SKILL.md`。

## 2. Slim + PHP-DI + Monolog 轻量 REST API

**目标**：依赖少、启动快、可控的 API 服务。

**组件**：Slim 4、PHP-DI、Monolog、Nyholm PSR-7、PDO 或 Doctrine DBAL、Firebase JWT。

**流程**：`public/index.php` 构建容器 → 注册中间件 → 路由 → Action/Controller → Service → Repository → Response。

**关键决策**：
- 路由文件与依赖配置分离。
- 中间件顺序：错误处理 → 请求 ID → CORS → 认证 → 路由。
- 每个 Action 只做参数转换与调用 Service。

**风险**：缺少统一错误格式、容器配置散落、没有输入校验层。

参考：`.agents/skills/slim-api/SKILL.md`。

## 3. 原生 PHP + Composer 模块化项目

**目标**：不引入完整框架，保持模块边界清晰。

**组件**：Composer PSR-4、前端控制器 `public/index.php`、轻量路由、PSR-11 容器、PSR-3 日志。

**结构**：

```text
src/
├── Http/           请求处理与响应
├── Domain/         领域模型与服务
├── Infrastructure/ 数据库、外部服务
└── Support/        通用工具
```

**关键决策**：模块之间通过接口通信；基础设施依赖领域接口，而非反向依赖。

**风险**：自研路由/容器失控，最终变成低质量框架。

参考：`.agents/skills/php-language/SKILL.md`、`.agents/skills/composer/SKILL.md`。

## 4. Symfony Console CLI 工具

**目标**：批处理、数据迁移、运维脚本。

**组件**：Symfony Console、Symfony Process、Flysystem、Monolog、Doctrine DBAL。

**流程**：`bin/console` → Command 定义参数/选项 → `execute()` → 调用 Service → 输出与退出码。

**关键决策**：
- 退出码：0 成功，非 0 失败，便于 CI/计划任务判断。
- 长任务输出进度并支持中断。
- 命令只做编排，业务放 Service。

**风险**：在命令里直接写 SQL 和业务逻辑，导致无法复用与测试。

## 5. API 网关 / 反向代理

**目标**：统一入口、鉴权、限流、路由转发、日志。

**组件**：Slim 或 ReactPHP、Guzzle/Symfony HttpClient、Redis（限流）、Monolog。

**流程**：请求 → 认证 → 限流 → 路由匹配 → 转发上游 → 响应改写 → 记录耗时。

**关键决策**：
- 网关只做横切关注点，不承载业务逻辑。
- 上游超时、重试、熔断必须有明确策略。
- 透传必要的头部（`X-Request-Id`、`Authorization`），过滤敏感头。

**风险**：同步阻塞导致并发能力差；PHP-FPM 每请求模型不适合长连接代理。

## 6. HTTP 转发服务

**目标**：把请求转发到内部服务，处理签名、鉴权、响应聚合。

**组件**：PSR-18 客户端、PSR-7 消息、PSR-15 中间件。

**关键决策**：流式转发大响应；设置连接/读取超时；限制重定向；校验上游 TLS。

**风险**：SSRF（未限制目标地址）、响应体一次性读入内存、超时缺失。

## 7. Redis 缓存与分布式锁

**目标**：降低数据库压力，保证并发操作互斥。

**组件**：Predis/PhpRedis、Symfony Cache、Redis `SET NX PX`、Lua 脚本释放锁。

**关键决策**：
- 缓存键设计：`业务:实体:ID:版本`。
- 缓存失效优先"更新数据库后删缓存"，避免双写不一致。
- 锁必须带过期时间与持有者标识，释放时校验持有者。

**风险**：缓存穿透/雪崩、锁误删、锁过期后业务未完成。

参考：`.agents/skills/cache-queue/SKILL.md`。

## 8. RabbitMQ 消息队列

**目标**：异步解耦、削峰、可靠投递。

**组件**：php-amqplib 或 Symfony Messenger/Laravel Queue 的 AMQP 驱动。

**关键决策**：
- 交换机/队列/路由键设计。
- 手动 ACK，处理失败进入重试队列或死信队列。
- 消费端幂等：用业务唯一键去重。

**风险**：消息重复、消息丢失、消费阻塞、没有死信处理。

## 9. PDO 数据库访问层

**目标**：不引入 ORM 也能安全、可维护地访问数据库。

**组件**：PDO、预处理语句、Repository、事务。

**关键决策**：
- 所有 SQL 使用占位符，禁止拼接。
- Repository 返回领域对象或数组，不暴露 PDO。
- 事务边界在 Service 层。

**风险**：N+1、长事务、连接未复用、错误吞掉。

参考：`.agents/skills/data-access/SKILL.md`。

## 10. JWT 身份认证

**目标**：无状态 API 认证。

**组件**：firebase/php-jwt、中间件、密钥管理。

**关键决策**：
- 短期 Access Token + 长期 Refresh Token。
- 校验 `alg`、`exp`、`iss`、`aud`，不接受 `none`。
- 密钥从环境变量读取，不入库不入仓。

**风险**：算法混淆、令牌泄露无法吊销、把敏感信息放进 payload。

参考：`.agents/skills/security/SKILL.md`、`.agents/skills/api-design/SKILL.md`。

## 11. Monolog 统一日志体系

**目标**：结构化日志、上下文、分级、多渠道。

**组件**：Monolog、PSR-3、JSON Formatter、Processor。

**关键决策**：
- 业务日志与访问日志分渠道。
- 统一注入 `request_id`、`user_id`、`trace_id`。
- 生产用 JSON，开发用可读格式。

**风险**：日志包含密码/令牌、日志量失控、异常只记 message 不记堆栈。

参考：`.agents/skills/observability/SKILL.md`。

## 12. Docker 部署 PHP 服务（参考）

**目标**：可复现的运行环境。

**组件**：PHP-FPM 镜像、Nginx 镜像、Compose、多阶段构建。

**关键决策**：生产镜像不装 Xdebug；`opcache.validate_timestamps=0`；配置与密钥通过环境变量注入。

**本地限制**：本机未安装 Docker，本节内容仅作参考，不能在本机直接执行。本地验证使用 `php -S` 或 PHP-FPM。

参考：`.agents/skills/runtime-deployment/SKILL.md`。

## 13. Nginx + PHP-FPM 生产部署

**目标**：稳定的传统 PHP 运行方式。

**组件**：Nginx、PHP-FPM、OPcache、Supervisor（队列 Worker）。

**关键决策**：
- `root` 指向 `public/`，禁止访问源码与 `.env`。
- `fastcgi_pass` 指向 FPM socket/TCP。
- `try_files $uri /index.php?$query_string`。
- OPcache 生产开启且关闭时间戳校验。

**本地限制**：本机无 Nginx/PHP-FPM，配置仅作参考。

## 14. 文件上传、下载与存储服务

**目标**：安全地接收、存储、分发文件。

**组件**：PSR-7 UploadedFile、Flysystem、对象存储 SDK（按需）。

**关键决策**：
- 校验 MIME 与扩展名，重命名存储，不信任原始文件名。
- 存储目录不可执行；下载通过应用鉴权后流式输出。
- 限制大小与类型白名单。

**风险**：路径穿越、任意文件上传、目录遍历下载、SSRF（远程抓取）。

## 15. ReactPHP / Amp 异步服务

**目标**：常驻进程、高并发 I/O、WebSocket、代理。

**组件**：ReactPHP（event-loop、http、socket）或 Amp（amp、http-client）。

**关键决策**：
- 全链路异步，避免在事件循环中做阻塞 I/O。
- 长驻进程注意内存泄漏与状态污染。
- 配合进程管理器重启。

**风险**：阻塞调用拖垮整个循环；无 `pcntl` 时进程管理受限；调试复杂。

参考：`.agents/skills/async-services/SKILL.md`。

## 16. 定时任务与后台 Worker

**目标**：周期任务、异步消费、失败重试。

**组件**：Symfony Console / Laravel Scheduler、队列驱动、系统计划任务（Windows 任务计划程序 / Linux cron）、Supervisor。

**关键决策**：
- 任务幂等，可安全重跑。
- 加锁避免重复执行。
- 记录开始/结束/耗时/结果。
- 失败重试与告警。

**风险**：任务重叠、内存增长、失败静默、时区错误。

## PHP 的适用边界

| 场景 | 适配度 | 说明 |
|---|---|---|
| 传统 Web/API（短请求） | 高 | PHP-FPM 模型成熟，生态完善 |
| 后台管理/CRUD | 高 | 框架支持充分 |
| CLI/批处理 | 高 | Symfony Console 足够 |
| 高并发长连接（WebSocket） | 中 | 需 ReactPHP/Amp/Octane，运维复杂度上升 |
| API 网关/反向代理 | 中 | 可以做，但纯转发场景 Nginx/Envoy 更合适 |
| CPU 密集型计算 | 低 | 受运行时限制，考虑其他语言或扩展 |
| 超大规模微服务 | 中 | 需容器化、可观测性与服务治理配套 |

选择 PHP 做常驻进程或网关时，必须评估团队是否具备相应运维能力，并明确超时、内存、重启、监控策略。
