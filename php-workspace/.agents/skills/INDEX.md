# Skills 索引

按开发场景组织的项目开发指南。先读 `AGENTS.md` 的"AI Resources"路由表，再打开对应 Skill；需要深入细节时读其 `references/` 或 `.agents/docs/`。

| Skill | 用途 | 关键内容 |
|---|---|---|
| [php-language](php-language/SKILL.md) | 现代 PHP 8.x 语言使用 | 类型系统、OOP、枚举、Attributes、异常、生成器、反射、内存、文件流 |
| [composer](composer/SKILL.md) | 依赖与自动加载 | composer.json、版本约束、PSR-4、Scripts、私有仓库、冲突排查、包发布 |
| [laravel](laravel/SKILL.md) | Laravel 项目开发 | 生命周期、容器、路由中间件、Eloquent、迁移、队列、认证、测试、Octane |
| [slim-api](slim-api/SKILL.md) | Slim 4 轻量 API | 项目结构、PSR-7/15、PHP-DI、Monolog、错误处理、JWT、PDO 集成 |
| [data-access](data-access/SKILL.md) | 数据库访问层 | PDO、预处理、事务、Repository、ORM 选型、迁移、N+1、连接管理 |
| [cache-queue](cache-queue/SKILL.md) | 缓存与队列 | Redis 数据结构、缓存模式、分布式锁、队列选型、重试、死信、幂等、Worker |
| [observability](observability/SKILL.md) | 日志、调试与性能 | Monolog 渠道与格式化、异常日志、Xdebug、OPcache、内存分析、慢请求定位 |
| [quality-testing](quality-testing/SKILL.md) | 质量与测试 | PHPStan/Psalm、PHP-CS-Fixer、Rector、PHPUnit/Pest、Mockery、覆盖率、CI |
| [security](security/SKILL.md) | 安全开发 | OWASP 风险、注入、XSS、CSRF、SSRF、上传、路径穿越、认证授权、JWT、依赖安全 |
| [api-design](api-design/SKILL.md) | REST API 设计 | 资源与版本、状态码、响应结构、分页、校验、序列化、OpenAPI、幂等 |
| [runtime-deployment](runtime-deployment/SKILL.md) | 运行时与部署 | php.ini、内置服务器、PHP-FPM、Nginx/Apache/Caddy、OPcache、Docker 参考 |
| [async-services](async-services/SKILL.md) | 异步与常驻服务 | ReactPHP、Amp、事件循环、异步客户端、状态与内存管理、网关、Worker |

## 常见任务对应 Skill

| 任务 | Skill |
|---|---|
| 新写一个 PHP 类/值对象/枚举 | php-language |
| 安装或升级依赖、自动加载报错 | composer |
| 新增 Laravel 接口或后台功能 | laravel + api-design |
| 搭建轻量 API | slim-api + api-design |
| 写 SQL、加事务、优化慢查询 | data-access |
| 加缓存、加锁、异步任务 | cache-queue |
| 查日志、断点、定位慢请求 | observability |
| 配置静态分析、补测试 | quality-testing |
| 安全评审、修漏洞 | security |
| 部署到服务器、调 OPcache | runtime-deployment |
| 写常驻服务或网关 | async-services + runtime-deployment |

## 详细资料

- 版本矩阵：`../docs/php-version-matrix.md`
- PSR 标准：`../docs/psr-standards.md`
- 生态库地图：`../docs/ecosystem-map.md`
- 框架对比：`../docs/framework-comparison.md`
- 架构场景：`../docs/architecture-scenarios.md`
- 官方文档索引：`../docs/official-sources.md`
- 安全代码示例：`security/references/owasp-php.md`
