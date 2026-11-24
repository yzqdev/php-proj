# PHP 框架对比与选型

选框架的核心不是"哪个最好"，而是"项目规模、团队能力、部署环境、生命周期"是否匹配。本文档给出各框架的定位与决策依据。

## 一、决策维度

| 维度 | 要考虑的问题 |
|---|---|
| 项目类型 | 单体 Web、REST API、微服务、CLI、网关、后台管理 |
| 规模与生命周期 | 一次性脚本、长期维护的业务系统、平台型产品 |
| 团队经验 | 是否熟悉 Laravel/Symfony 生态 |
| 依赖偏好 | 全栈框架 vs 按需组合组件 |
| 性能要求 | 普通请求、长驻进程、高并发 |
| 部署环境 | 传统 PHP-FPM、容器、Serverless |
| 学习成本 | 新人上手速度、文档质量 |

## 二、Laravel

**定位**：全栈 Web 框架，约定优于配置，生态最完整。

**架构特点**：
- 请求生命周期：`public/index.php` → Kernel → 中间件栈 → 路由 → Controller → Response。
- 服务容器贯穿全局，Facade 提供静态代理。
- Eloquent 是 Active Record 风格 ORM，模型直接对应表。
- 内置认证、队列、缓存、事件、调度、邮件、存储、测试等能力。

**适用场景**：
- 业务后台、SaaS、内容站点、需要快速交付的 CRUD 系统。
- 团队熟悉 Laravel，或需要大量现成组件。
- 单体应用优先，不追求极致轻量。

**不适用/慎用**：
- 只需要一个极小 API，引入全栈框架会带来不必要的启动开销与依赖。
- 需要极致可控的请求管道或非标准运行时。

**关键组件**：Artisan、Eloquent、Blade、Sanctum、Horizon、Octane、Telescope。

详见 `.agents/skills/laravel/SKILL.md`。

## 三、Symfony

**定位**：企业级组件化框架，稳定、规范、可长期维护。

**架构特点**：
- 基于 Bundle 的模块化结构，组件可独立使用（HttpFoundation、HttpKernel、Routing、Console、Mailer 等）。
- 编译型 DI 容器，配置与编译器 pass 带来较强约束。
- Doctrine ORM 是主流数据访问方式。
- 事件驱动内核，请求/响应/异常都是事件。

**适用场景**：
- 大型、长期维护、多人协作的业务系统。
- 需要严格分层与可测试性。
- 团队愿意承担更高的学习成本。

**不适用/慎用**：
- 小型项目或快速原型，配置成本偏高。
- 只需要几个组件时，可以直接用 Symfony 组件而不引入完整框架。

## 四、Slim 4

**定位**：轻量级 PSR-7/PSR-15 微框架，只提供路由与中间件管道。

**架构特点**：
- 应用核心是 `App` + 路由 + 中间件栈。
- 请求/响应遵循 PSR-7；中间件遵循 PSR-15。
- 不内置 ORM、模板、认证，需要自行组合 PHP-DI、Monolog、Doctrine DBAL、JWT 等。

**适用场景**：
- 轻量 REST API、微服务、内部服务。
- API 网关、反向代理、BFF。
- 需要精确控制依赖与请求流程的项目。

**不适用/慎用**：
- 需要大量开箱即用功能（认证、后台、队列 UI）的项目。
- 团队不具备组件组合能力时，容易演变成"自己造框架"。

详见 `.agents/skills/slim-api/SKILL.md`。

## 五、Mezzio

**定位**：基于 PSR-7/PSR-15 的中间件框架，Laminas 生态的一部分。

**特点**：
- 严格遵循 PSR 标准，路由、模板、容器均可替换。
- 适合需要标准化、可替换组件的企业项目。
- 生态与文档相对 Laravel/Symfony 小。

**适用场景**：重视 PSR 兼容与组件可替换性的 API 服务。

## 六、Flight

**定位**：极简单文件风格微框架。

**特点**：
- API 简洁，路由与回调直观，学习成本极低。
- 适合原型、内部小工具、教学示例。
- 大型项目缺少结构化支撑。

**适用场景**：小型脚本化 Web 服务、快速验证想法。

## 七、CodeIgniter 4

**定位**：轻量全栈框架，介于 Slim 与 Laravel 之间。

**特点**：
- MVC 结构清晰，内置数据库、验证、Session、上传等常用能力。
- 配置与目录结构比 Laravel 更轻。
- 生态规模小于 Laravel。

**适用场景**：中小型 Web 应用、传统 MVC 团队迁移。

## 八、横向对比

| 维度 | Laravel | Symfony | Slim 4 | Mezzio | Flight | CodeIgniter 4 |
|---|---|---|---|---|---|---|
| 类型 | 全栈 | 全栈/组件 | 微框架 | 中间件框架 | 微框架 | 轻量全栈 |
| 标准遵循 | 自有 + PSR | 自有 + PSR | PSR-7/15 | PSR-7/15 | 自有 | 自有 + PSR |
| 内置 ORM | Eloquent | 无（Doctrine） | 无 | 无 | 无 | Query Builder/Model |
| 学习成本 | 中 | 高 | 低-中 | 中 | 低 | 低-中 |
| 开箱能力 | 强 | 强（需配置） | 弱 | 弱 | 很弱 | 中 |
| 适合规模 | 中-大 | 大 | 小-中 | 中 | 小 | 小-中 |
| 生态活跃度 | 很高 | 很高 | 中 | 中 | 低 | 中 |

## 九、选型建议

- **要最快交付业务系统**：Laravel。
- **要长期可维护、严格分层**：Symfony。
- **只要 API、依赖可控**：Slim 4 + 精选组件。
- **要 PSR 标准化与组件替换**：Mezzio。
- **原型/小工具**：Flight 或原生 PHP + Composer。
- **传统 MVC 团队**：CodeIgniter 4 过渡成本低。

不确定时，按以下顺序排除：

1. 项目是否真的需要全栈框架？只有 API 时优先 Slim。
2. 团队是否已有框架经验？沿用熟悉生态能显著降低维护成本。
3. 是否需要长期演进？长期项目优先 Symfony/Laravel。
4. 部署是否受限？常驻进程场景要评估 Octane/ReactPHP/Amp 的适配成本。

## References

- Laravel：<https://laravel.com/docs>
- Symfony：<https://symfony.com/doc/current/>
- Slim 4：<https://www.slimframework.com/docs/v4/>
- Mezzio：<https://docs.mezzio.dev/>
- Flight：<https://flightphp.com/>
- CodeIgniter 4：<https://codeigniter.com/user_guide/>
