# PHP 生态库地图

本文档按"解决什么问题"组织常用 Composer 包，用于选型、判断替代方案和定位官方文档。包名与约束以 Packagist 为准，不在此硬编码版本号。

## 一、选型总原则

1. **先看标准接口**：能用 PSR 接口表达的能力（日志、HTTP、缓存、容器），业务代码依赖接口而非具体实现。
2. **优先维护活跃、星标高、有正式文档的包**：查看 Packagist 下载量、最近发布日期、issue 响应。
3. **避免重复能力**：同一项目里不要同时引入 Guzzle 与 Symfony HttpClient 做同一件事。
4. **注意扩展依赖**：部分包需要 PHP 扩展（redis、gd、amqp、sodium），先确认部署环境。
5. **框架内置优先**：Laravel/Symfony 已提供的组件，通常不需要再引入同功能第三方包。

## 二、日志

| 包 | 标准 | 用途 |
|---|---|---|
| `monolog/monolog` | PSR-3 | 通用日志实现，Handler/Formatter/Processor 体系完整 |
| `psr/log` | PSR-3 | 日志接口，业务代码依赖它 |

选择建议：独立项目用 Monolog；Laravel 已内置 Monolog，通过 `Log` 门面使用；不要把 Monolog 具体 Handler 泄漏到业务层。

官方：<https://github.com/Seldaek/monolog>

## 三、依赖注入

| 包 | 标准 | 用途 |
|---|---|---|
| `php-di/php-di` | PSR-11 | 自动装配容器，支持 Definition、Factory、Lazy |
| `league/container` | PSR-11 | 轻量容器，支持委托与缓存 |
| `symfony/dependency-injection` | PSR-11（适配） | 编译型容器，适合大型应用 |

选择建议：Slim 项目优先 PHP-DI；Symfony/Laravel 用框架自带容器；小型脚本可以手写工厂，不必引入容器。

官方：<https://php-di.org/>

## 四、HTTP 客户端与服务端

| 包 | 标准 | 用途 |
|---|---|---|
| `guzzlehttp/guzzle` | PSR-7 / PSR-18（适配） | 功能完整的 HTTP 客户端，中间件、重试、并发池 |
| `symfony/http-client` | PSR-18（适配） | Symfony 生态客户端，支持并发与流式 |
| `php-http/httplug` | HTTPlug | 客户端抽象，历史生态 |
| `nyholm/psr7` | PSR-7 / PSR-17 | 轻量 PSR-7 实现 + 工厂 |
| `slim/psr7` | PSR-7 / PSR-17 | Slim 官方 PSR-7 实现 |
| `guzzlehttp/psr7` | PSR-7 | Guzzle 的 PSR-7 实现 |
| `react/http` | 自有 | ReactPHP 异步 HTTP 服务端/客户端 |
| `amphp/http-client` | 自有 | Amp 异步 HTTP 客户端 |

选择建议：普通同步调用用 Guzzle 或 Symfony HttpClient；只要 PSR-18 能力时依赖 `psr/http-client`；异步/常驻进程场景用 ReactPHP 或 Amp。

官方：
- Guzzle：<https://docs.guzzlephp.org/>
- Symfony HttpClient：<https://symfony.com/doc/current/http_client.html>
- ReactPHP：<https://reactphp.org/>
- Amp：<https://amphp.org/>

## 五、数据库与 ORM

| 包/扩展 | 类型 | 用途 |
|---|---|---|
| `PDO`（内置） | 扩展 | 统一数据库访问，预处理语句 |
| `doctrine/dbal` | 库 | 数据库抽象层，Schema 管理 |
| `doctrine/orm` | ORM | Data Mapper 风格 ORM |
| `doctrine/migrations` | 迁移 | Doctrine 数据库迁移 |
| `illuminate/database` | ORM/Query Builder | Laravel Eloquent 独立使用 |
| `cycle/orm` | ORM | Data Mapper，支持多种数据库 |
| `catfan/medoo` | 轻量查询构建 | 小型项目快速访问 |
| `robmorgan/phinx` | 迁移 | 独立数据库迁移工具 |

选择建议：
- 小型 API/脚本：PDO + 手写 Repository，或 Medoo。
- 中大型领域模型：Doctrine ORM 或 Cycle ORM。
- Laravel 项目：Eloquent。
- 不要把 Eloquent 当作"PHP 标准 ORM"；它绑定 Laravel 生态。

官方：
- PDO：<https://www.php.net/manual/en/book.pdo.php>
- Doctrine：<https://www.doctrine-project.org/>
- Cycle：<https://cycle-orm.dev/>
- Medoo：<https://medoo.in/>

## 六、缓存

| 包/扩展 | 标准 | 用途 |
|---|---|---|
| `predis/predis` | 自有 | 纯 PHP Redis 客户端，无需扩展 |
| `ext-redis`（PhpRedis） | 扩展 | 性能更高的 Redis 客户端 |
| `symfony/cache` | PSR-6 / PSR-16 | 多后端缓存抽象（文件、Redis、APCu） |
| `illuminate/cache` | 自有（可适配 PSR-16） | Laravel 缓存 |

选择建议：无 redis 扩展时用 Predis；追求性能且能装扩展时用 PhpRedis；需要 PSR-6/16 抽象时用 Symfony Cache。

本地注意：本机未加载 `redis` 扩展，且无 Redis 服务端，Redis 相关内容只能作为参考方案。

官方：
- Redis：<https://redis.io/docs/>
- Predis：<https://github.com/predis/predis>
- Symfony Cache：<https://symfony.com/doc/current/components/cache.html>

## 七、队列与异步任务

| 包 | 用途 |
|---|---|
| `illuminate/queue` | Laravel 队列，支持 Redis、database、SQS 等驱动 |
| `symfony/messenger` | Symfony 消息总线与队列 |
| `php-amqplib/php-amqplib` | RabbitMQ AMQP 客户端 |
| `pda/pheanstalk` | Beanstalkd 客户端 |
| `react/event-loop` / `react/async` | ReactPHP 事件循环与异步 |
| `amphp/amp` | Amp 协程/异步 |

选择建议：Laravel 用 Queue + Horizon；Symfony 用 Messenger；跨语言消息用 RabbitMQ；轻量后台任务可用数据库表 + Worker。

官方：
- Laravel Queue：<https://laravel.com/docs/queues>
- Symfony Messenger：<https://symfony.com/doc/current/messenger.html>
- php-amqplib：<https://github.com/php-amqplib/php-amqplib>

## 八、数据验证与序列化

| 包 | 用途 |
|---|---|
| `symfony/validator` | 基于约束注解/属性的验证器 |
| `respect/validation` | 链式验证库 |
| `cuyz/valinor` | 把数组映射为强类型对象并验证 |
| `symfony/serializer` | 对象与 JSON/XML/CSV 互转，支持 Normalizer |
| `jms/serializer` | 注解驱动的序列化库 |

选择建议：需要 DTO 强类型映射时 Valinor 很合适；Symfony/Laravel 项目优先框架内置验证；对外 API 的输入校验统一在一层完成，不要在 Controller 内散落 `if`。

官方：
- Symfony Validator：<https://symfony.com/doc/current/validation.html>
- Respect Validation：<https://respect-validation.readthedocs.io/>
- Valinor：<https://valinor.cuyz.io/>
- Symfony Serializer：<https://symfony.com/doc/current/serializer.html>

## 九、常用工具组件

| 包 | 用途 | 说明 |
|---|---|---|
| `nesbot/carbon` | 日期时间 | 基于 DateTime 的增强，Laravel 内置 |
| `ramsey/uuid` | UUID | 生成 v1/v4/v6/v7 等 UUID |
| `brick/math` | 高精度计算 | 金额、汇率等避免浮点误差 |
| `symfony/console` | CLI | 命令、参数、输入输出、进度条 |
| `symfony/process` | 进程管理 | 安全执行外部命令 |
| `symfony/finder` | 文件查找 | 按规则遍历文件 |
| `symfony/filesystem` | 文件系统 | 文件/目录操作 |
| `symfony/event-dispatcher` | 事件系统 | PSR-14 事件分发 |
| `league/flysystem` | 文件存储抽象 | 本地、S3、FTP 统一接口 |
| `league/fractal` | 数据转换 | API 输出 Transformer |
| `firebase/php-jwt` | JWT | 签发与校验 JWT |
| `phpmailer/phpmailer` | 邮件 | SMTP 发送邮件 |
| `intervention/image` | 图片处理 | 依赖 gd 或 imagick |
| `vlucas/phpdotenv` | 环境变量 | 从 `.env` 加载配置 |
| `fakerphp/faker` | 测试数据 | 生成假数据 |
| `zircote/swagger-php` | OpenAPI | 从 PHP Attributes 生成 OpenAPI 文档 |

本地注意：

- `intervention/image` 在本机不可直接运行：未加载 `gd`/`imagick`。
- `league/flysystem` 的 S3 适配器需要额外 AWS SDK，按需安装。
- 金额计算优先 `brick/math`，不要用 `float` 累加。

官方：
- Carbon：<https://carbon.nesbot.com/docs/>
- Ramsey UUID：<https://uuid.ramsey.dev/>
- Brick Math：<https://github.com/brick/math>
- Symfony Console：<https://symfony.com/doc/current/console.html>
- Flysystem：<https://flysystem.thephpleague.com/docs/>
- Firebase JWT：<https://github.com/firebase/php-jwt>
- PHPMailer：<https://github.com/PHPMailer/PHPMailer>

## 十、开发与质量工具

| 包 | 用途 |
|---|---|
| `phpunit/phpunit` | 单元/集成测试 |
| `pestphp/pest` | 基于 PHPUnit 的表达式测试框架 |
| `mockery/mockery` | Mock 对象 |
| `infection/infection` | 变异测试 |
| `brianium/paratest` | 并行测试 |
| `phpstan/phpstan` | 静态分析 |
| `vimeo/psalm` | 静态分析（类型推断强） |
| `friendsofphp/php-cs-fixer` | 代码风格自动修复 |
| `squizlabs/php_codesniffer` | 编码规范检查 |
| `rector/rector` | 自动重构与版本升级 |
| `phpmd/phpmd` | 代码坏味道检测 |
| `nunomaduro/phpinsights` | 综合质量报告 |

这些工具应在 `require-dev`，并在 CI 中固定命令。

## 十一、按场景的推荐组合

| 场景 | 推荐组合 |
|---|---|
| 轻量 REST API | Slim 4 + PHP-DI + Monolog + PDO/Doctrine DBAL + Nyholm PSR-7 |
| 完整 Web 应用 | Laravel + Eloquent + Queue + Sanctum |
| 企业级应用 | Symfony + Doctrine ORM + Messenger + Validator |
| CLI 工具 | Symfony Console + Symfony Process + Flysystem |
| API 网关/代理 | Slim 或 ReactPHP + Guzzle/Symfony HttpClient |
| 异步服务 | ReactPHP 或 Amp + 事件循环 |
| 定时任务 | Symfony Console 命令 + 系统计划任务 / Laravel Scheduler |
