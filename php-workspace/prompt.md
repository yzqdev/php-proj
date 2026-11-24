# 使用 gen-skill 生成 PHP 开发 AI 工作区

请使用当前环境中已经安装的 `gen-skill` Skill，在当前工作目录生成一套完整、实用、可持续维护的 **PHP Development AI Workspace**。

## 一、总体目标

我需要一套面向现代 PHP 开发的 AI 工作区，主要用于：

- 学习 PHP 语言及其生态。
- 开发 PHP Web 应用、REST API、后台服务。
- 使用 Laravel 等主流框架进行项目开发。
- 使用轻量级框架构建微服务、API 网关及小型服务。
- 进行数据库、缓存、日志、队列、HTTP 客户端等基础设施开发。
- 开发 Composer 包、CLI 工具和可复用组件。
- 排查 PHP 运行时、Composer 依赖、Web Server 和生产环境问题。
- 阅读、分析、重构和维护现有 PHP 项目。
- 为 AI Coding Agent 提供可靠的技术文档、代码示例和开发规范。

不要只生成一堆简单的 Markdown 介绍文档，而是要构建真正能辅助 AI 编程的知识体系。

## 二、PHP 基础与语言特性

建立 PHP 语言基础知识库，重点覆盖现代 PHP 8.x 及后续稳定版本。

包括：

- PHP 基础语法、变量、常量、类型系统。
- 严格类型声明、类型转换。
- 数组、字符串、日期时间处理。
- 函数、闭包、匿名函数、箭头函数。
- 面向对象编程。
- Trait、Interface、Abstract Class。
- Namespace 和 Autoload。
- Enum 枚举。
- Attributes 注解。
- Exception 和 Error 异常体系。
- Generator 和 Fiber。
- Reflection 反射机制。
- Anonymous Class。
- Readonly Properties。
- Constructor Property Promotion。
- Match Expression。
- First-class Callable Syntax。
- PHP 8.x 各版本的重要新特性。
- PHP 内存管理、垃圾回收、引用机制。
- PHP-FPM 工作机制。
- OPcache。
- Session、Cookie。
- 文件处理、上传、流操作。
- JSON、XML、CSV 处理。
- PHP 安全开发基础。

需要区分不同 PHP 版本的语法和 API，不能将新版本特性错误地应用于旧版本项目。

## 三、Composer 与依赖管理

重点学习 Composer 2.x。

包括：

- Composer 安装与配置。
- composer.json、composer.lock。
- require、require-dev。
- install、update、remove。
- PSR-4 自动加载。
- 自定义 Autoload。
- Composer Scripts。
- Private Repository。
- Path Repository。
- 依赖版本约束。
- 依赖冲突排查。
- Composer Package 开发与发布。
- Packagist。
- Composer 插件和脚本安全。
- 项目依赖升级与兼容性检查。

重点理解 PSR 标准：

- PSR-1：基础编码规范。
- PSR-3：日志接口。
- PSR-4：自动加载。
- PSR-7：HTTP 消息接口。
- PSR-11：容器接口。
- PSR-12：编码风格。
- PSR-15：HTTP Server Middleware。
- PSR-17：HTTP Factory。
- PSR-18：HTTP Client。

同时了解 PSR-6、PSR-16 等缓存规范。

## 四、主流 PHP 框架

建立独立的框架知识文档，重点覆盖以下框架。

### 1. Laravel

重点研究 Laravel 官方文档及核心组件：

- 项目创建与目录结构。
- Artisan CLI。
- Routing 路由系统。
- Controller。
- Middleware。
- Request / Response。
- Service Container。
- Service Provider。
- Facades。
- Eloquent ORM。
- Query Builder。
- Database Migration。
- Seeder / Factory。
- Validation。
- Authentication / Authorization。
- Session / Cookie。
- Cache。
- Queue / Job。
- Event / Listener。
- Task Scheduling。
- Notification / Mail。
- File Storage。
- Blade Template。
- API Resource。
- Exception Handling。
- Logging。
- Configuration。
- Testing。
- Laravel Octane。
- Laravel Sanctum。
- Laravel Horizon。
- Laravel Telescope。

需要讲解 Laravel 的生命周期、服务容器、依赖注入、Middleware 执行流程及请求处理机制。

### 2. Slim Framework

重点覆盖 Slim 4：

- 项目初始化。
- Routing。
- Request / Response。
- Middleware。
- PSR-7。
- PSR-15。
- Error Handling。
- Dependency Injection。
- Container 集成。
- 数据库集成。
- REST API 开发。
- JWT 认证。
- 项目结构设计。

重点研究 Slim 与 PHP-DI、Monolog 等组件的组合使用。

### 3. 其他框架

根据实际使用价值补充：

- Symfony。
- Mezzio。
- Flight PHP。
- CodeIgniter。

重点介绍各框架的定位、适用场景、架构特点和选择依据，不需要对所有框架进行同等深度的研究。

## 五、常用 PHP 库与组件

这是工作区的重要部分，需要覆盖以下生态。

### 1. 日志

- Monolog。
- PSR-3 LoggerInterface。
- 日志 Handler。
- 日志 Formatter。
- 日志 Processor。
- 日志分级。
- 文件日志。
- 日志轮转。
- JSON 日志。
- 日志上下文。
- 异常日志。
- 多渠道日志输出。

重点研究 Monolog 的实际生产配置和与 Laravel、Slim 的集成。

### 2. 依赖注入

- PHP-DI。
- PSR-11。
- Container。
- Autowiring。
- Definition。
- Factory。
- Interface Binding。
- Lazy Injection。
- Scope 与生命周期。
- 循环依赖问题。

重点研究 PHP-DI 在独立 PHP 项目和 Slim 项目中的使用方式。

### 3. HTTP 客户端与服务端

- Guzzle。
- Symfony HttpClient。
- HTTPlug。
- PSR-18。
- Nyholm PSR-7。
- Slim PSR-7。
- ReactPHP HTTP。
- Amp HTTP。

覆盖 HTTP 请求、连接池、超时、重试、代理、TLS、异步请求和流式响应等内容。

### 4. 数据库与 ORM

- PDO。
- Eloquent ORM。
- Doctrine ORM。
- Cycle ORM。
- Medoo。

重点覆盖：

- MySQL。
- PostgreSQL。
- SQLite。
- 数据库连接池。
- 事务。
- 预处理语句。
- SQL 注入防护。
- Migration。
- ORM 性能优化。
- N+1 查询。
- 数据库连接管理。

### 5. 缓存

- Redis。
- Predis。
- PhpRedis。
- Symfony Cache。
- PSR-6。
- PSR-16。

包括缓存设计、缓存失效、分布式缓存、Redis 数据结构、锁和缓存穿透等。

### 6. 队列与异步任务

- Laravel Queue。
- Symfony Messenger。
- php-amqplib。
- RabbitMQ。
- Redis Queue。
- Beanstalkd。
- ReactPHP。
- Amp。

了解消息队列、消费者、生产者、重试、死信队列、幂等性和异步任务设计。

### 7. 数据验证与序列化

- Symfony Validator。
- Respect Validation。
- Valinor。
- Symfony Serializer。
- JMS Serializer。

覆盖数据校验、DTO、对象序列化、JSON 转换和 API 数据结构设计。

### 8. 其他常用组件

根据实际价值补充：

- Carbon：日期时间处理。
- Ramsey UUID：UUID。
- Brick Math：高精度计算。
- Symfony Console：CLI。
- Symfony Process：进程管理。
- Symfony Finder：文件查找。
- Symfony Filesystem：文件系统。
- Symfony EventDispatcher：事件系统。
- League Flysystem：文件存储抽象。
- League Fractal：数据转换。
- Firebase JWT：JWT。
- PHPMailer：邮件。
- Intervention Image：图片处理。
- Respect Validation：数据验证。

## 六、PHP 开发工具链

重点覆盖以下工具。

### 1. 开发环境

- PHP CLI。
- PHP-FPM。
- PHP 内置开发服务器。
- Composer。
- PHP 扩展管理。
- PHP 配置文件 php.ini。
- 环境变量。
- Docker。
- Docker Compose。
- Nginx。
- Apache。
- Caddy。

了解 Windows、Linux 环境下的 PHP 安装、配置和部署差异。

### 2. 代码质量与静态分析

- PHPStan。
- Psalm。
- PHP_CodeSniffer。
- PHP-CS-Fixer。
- Rector。
- PHP Mess Detector。
- PHP Insights。

覆盖静态分析、代码格式化、代码规范、自动重构和类型检查。

### 3. 测试工具

- PHPUnit。
- Pest。
- Mockery。
- Infection。
- ParaTest。

包括单元测试、集成测试、Mock、数据提供器、覆盖率和变异测试。

### 4. 调试与性能分析

- Xdebug。
- Blackfire。
- XHProf。
- Tideways。
- PHP OPcache。
- PHP 内存分析。
- PHP-FPM 状态监控。

重点讲解断点调试、调用栈、性能分析、内存泄漏排查和慢请求定位。

### 5. API 开发工具

- OpenAPI / Swagger。
- Swagger UI。
- Stoplight。
- Postman。
- Bruno。
- Insomnia。

覆盖 API 文档生成、接口调试、认证测试和接口自动化测试。

## 七、常见开发场景与架构设计

不要只介绍单个库，还要建立实际开发场景的解决方案。

重点包括：

1. 使用 Laravel 开发完整 REST API。
2. 使用 Slim + PHP-DI + Monolog 构建轻量级 REST API。
3. 使用原生 PHP + Composer 构建模块化项目。
4. 使用 Symfony Console 开发 CLI 工具。
5. 使用 PHP 开发 API 网关和反向代理。
6. 使用 PHP 开发 HTTP 转发服务。
7. 使用 Redis 实现缓存和分布式锁。
8. 使用 RabbitMQ 实现消息队列。
9. 使用 PDO 实现数据库访问层。
10. 使用 JWT 实现 API 身份认证。
11. 使用 Monolog 建立统一日志体系。
12. 使用 Docker 部署 PHP 服务。
13. 使用 Nginx + PHP-FPM 部署生产环境。
14. 使用 PHP 实现文件上传、下载和存储服务。
15. 使用 ReactPHP 或 Amp 开发异步服务。
16. 使用 PHP 构建定时任务和后台 Worker。

特别关注 PHP 在高并发 API 服务、长驻进程、反向代理和网关场景中的适用性及限制。

## 八、安全开发

建立 PHP 安全知识库：

- SQL Injection。
- XSS。
- CSRF。
- SSRF。
- 文件上传漏洞。
- 路径穿越。
- 命令注入。
- 反序列化漏洞。
- Session 安全。
- 密码哈希。
- JWT 安全。
- CORS。
- HTTPS / TLS。
- API 限流。
- 认证与授权。
- Composer 依赖供应链安全。
- 环境变量和密钥管理。

结合 OWASP Top 10 和实际 PHP 代码给出安全与错误示例。

## 九、官方文档与资料来源

优先使用官方文档建立知识库，确保内容准确。

重点资料：

- PHP：https://www.php.net/docs.php
- Composer：https://getcomposer.org/doc/
- Laravel：https://laravel.com/docs
- Slim：https://www.slimframework.com/docs/v4/
- Symfony：https://symfony.com/doc/current/
- Monolog：https://github.com/Seldaek/monolog
- PHP-DI：https://php-di.org/
- Guzzle：https://docs.guzzlephp.org/
- Doctrine：https://www.doctrine-project.org/
- PHPUnit：https://docs.phpunit.de/
- Pest：https://pestphp.com/docs/
- PHPStan：https://phpstan.org/user-guide/getting-started
- Rector：https://getrector.com/documentation
- Xdebug：https://xdebug.org/docs/
- Redis：https://redis.io/docs/
- OpenAPI：https://spec.openapis.org/oas/latest.html
- OWASP：https://owasp.org/www-project-top-ten/

如果某个库的官方文档不完整，可以补充 GitHub README、官方示例和可靠的社区资料。

## 十、文档质量要求

每个技术主题尽可能采用统一的文档组织方式：

- 技术简介与适用场景。
- 安装与环境要求。
- 基础使用方法。
- 核心 API 和重要概念。
- 常见配置。
- 实际代码示例。
- 与其他组件的集成方式。
- 常见错误及排查方法。
- 性能与安全注意事项。
- 官方文档链接。

代码示例应尽量完整，确保能够在对应 PHP 版本下运行。

对于不同版本之间存在差异的 API，需要明确标记版本，不能混用。

避免只复制官方文档，应当整理成适合 AI Coding Agent 快速检索和实际开发的知识。

## 十一、AI 工作区使用要求

生成的工作区需要能够被后续 AI Coding Agent 直接使用。

请重点考虑：

- 工作区的统一入口和索引。
- Agent 应当如何发现和使用相关 Skill。
- 不同技术主题之间的关联。
- 开发任务中的技术选型参考。
- 官方文档的快速定位。
- 常见开发场景的参考实现。
- 版本兼容性提醒。
- 代码质量与安全检查流程。
- 文档更新机制。

不要让 AI 每次处理 PHP 项目都读取所有文档，而应根据任务类型检索对应的知识。

例如：

- Laravel 项目：优先查找 Laravel 对应知识。
- Slim API 项目：优先查找 Slim、PHP-DI、PSR-7、Monolog。
- CLI 项目：优先查找 Symfony Console。
- 性能问题：优先查找 Xdebug、Blackfire、PHP-FPM、OPcache。
- 依赖问题：优先查找 Composer、Packagist 和相关库文档。

## 十二、生成与维护要求

请严格按照 `gen-skill` 的工作流程执行：

1. 首先阅读并理解 gen-skill 的完整使用说明。
2. 分析当前工作目录，避免覆盖已有文件。
3. 根据实际需求自行设计合理的工作区结构。
4. 合理拆分 Skill、参考文档、索引和示例。
5. 优先建立高价值、使用频率高的知识模块。
6. 通过官方资料验证重要技术信息。
7. 检查文档之间是否存在重复、冲突和错误引用。
8. 检查 Skill 是否具备实际可执行的指导能力。
9. 建立后续维护和扩展机制。

请注意：

- 不要为了数量而生成大量低质量文档。
- 不要将所有内容塞进一个超大的 Markdown 文件。
- 不要把每个 PHP 库都机械地拆成一个 Skill，应根据实际使用场景合理划分。
- 不要虚构 API、配置项和代码示例。
- 不要默认所有项目都使用 Laravel。
- 不要把 Laravel 特有能力误认为 PHP 语言本身的能力。
- 不要硬编码过时的 PHP 版本、依赖版本和 API。
- 不要覆盖当前目录已有的项目文件。
- 生成的临时文件、一次性脚本和中间产物应按 gen-skill 的规范处理。

最终目标是生成一套真正实用的 PHP AI 开发工作区，让 AI 不仅知道 PHP 怎么写，还能根据不同场景选择合适的框架、库和架构，并完成可靠的工程开发。

完成后，请输出工作区生成报告，包括：

- 工作区整体结构。
- 创建的 Skill 及其用途。
- 主要技术文档及覆盖范围。
- 官方资料来源。
- 版本兼容性策略。
- 尚未覆盖的内容。
- 后续建议扩展的技术方向。