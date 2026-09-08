# AGENTS.md

本文件为 AI 编码代理提供项目上下文与规范，修改代码前请先阅读并遵守。

## 项目概述

- **项目性质**：面向 Java / Spring Boot 开发者的 PHP 8.x 学习项目
- **技术栈**：PHP 8.5、Composer、MySQL 8.4、Slim 4、php-di、Monolog、Doctrine ORM、PSR-15
- **业务范围**：文章管理（CRUD + 分页）、活动管理（JSON API）、Bearer Token 认证（JWT + Refresh Token）
- **API 前缀**：全部走 `/api/v1/*`；Swagger UI 在 `/api/v1/docs`
- **数据访问**：**全部走 Doctrine ORM**（不再有手写 PDO / BaseModel / QueryBuilder）
- **注释约定**：关键代码配中文注释，并标注 Java 生态对照概念（Composer ↔ Maven、doctrine/dbal ↔ JDBC、php-di ↔ Spring IoC 等）

## 环境信息（Windows 11 + PowerShell）

| 组件 | 说明 |
| --- | --- |
| PHP 8.5 | `F:\programs\php-8.5.10-nts-Win32-vs17-x64\php.exe`（不在 PATH，用绝对路径或加到环境变量） |
| Composer | 全局可用，已配置阿里云镜像 |
| MySQL | 127.0.0.1:3306，用户 `root`，密码 `123456` |

> ⚠️ PHP 8.5 本机**未加载 `mbstring` 扩展**（`php -m` 中无 mbstring）。
> 处理中文长度时用 `App\Helpers\Html::charLen()`（内部用 `preg_match_all('/./us')`）；
> **不要直接调 `mb_strlen` / `mb_substr`**。

## 常用命令

```powershell
# 用绝对路径调用 PHP（本环境 PATH 里没有 php）
$php = 'F:\programs\php-8.5.10-nts-Win32-vs17-x64\php.exe'

& $php -l <文件>                              # 语法检查
& $php -m                                     # 查看已加载扩展
& $php -S localhost:8000 -t public            # 启动开发服务器（端口可改）
.\run_learn.ps1                               # 等价启动脚本（6850 端口）

composer install                              # 安装依赖
composer dump-autoload --optimize             # 改完命名空间后刷新自动加载
composer schema:create                        # 按 Entity 属性注解建表
composer migrate                              # 执行 Doctrine migrations
composer migrate:status                       # 查看迁移状态
composer db:drop                              # 删库（危险，仅本地）

mysql -uroot -p < docs\sql\schema.sql         # 初始化数据库
mysql -uroot -p < docs\sql\alter_add_refresh_tokens.sql
```

## 项目结构

```
php-learn/
├── public/                    Web 入口（唯一对外暴露的目录）
│   ├── index.php              唯一入口（Front Controller → Slim run）
│   ├── assets/                静态资源（app.css / app.js）
│   ├── files/                 文件 IO 演示用文件
│   └── vendor/swagger-ui/     Swagger UI 静态资源（本地，不依赖 CDN）
├── app/                       业务代码
│   ├── bootstrap.php          应用引导（错误处理、php-di 容器、Doctrine、Slim、中间件）
│   ├── api/routes.php         API v1 路由表（唯一的路由表）
│   ├── Controllers/Api/       Article / Activity / Auth / Health / ApiDocs
│   ├── Middleware/            Cors / RequestLogger / ApiAuthenticate / ErrorRenderer
│   ├── Entities/              Doctrine ORM 全部实体 + 仓储
│   │   ├── User.php           #[ORM\Entity] + 一对多 refreshTokens
│   │   ├── UserRepository.php
│   │   ├── Article.php        #[ORM\Entity] + 生命周期回调
│   │   ├── ArticleRepository.php      paginate + countFiltered
│   │   ├── Activity.php       #[ORM\Entity] + DateTimeImmutable 时间字段
│   │   ├── ActivityRepository.php     paginate(upcoming) + countFiltered
│   │   ├── RefreshToken.php   #[ORM\Entity] 存 Refresh Token
│   │   └── RefreshTokenRepository.php findValid / revoke / revokeAll
│   ├── Services/              Jwt / TokenService / DoctrineServiceProvider
│   ├── Exceptions/            BusinessException / ResourceNotFoundException
│   ├── Resources/             ArticleResource / ActivityResource（Entity → API）
│   ├── Helpers/               Config / Request / RequestBody / JsonResponse / Logger / Html
│   ├── OpenApi/               Schemas / SpecGenerator（OpenAPI 3.0 生成器）
│   ├── Console/               DoctrineConsole.php（schema/migrate CLI 入口）
│   └── Migrations/            Doctrine 迁移类目录
├── config/
│   ├── config.example.php     模板（可提交）
│   └── config.php             真实配置（.gitignore 忽略）
├── client/                    Vue 3 + Vite 前端 SPA
├── docs/sql/
│   ├── schema.sql             建库建表 + 种子数据
│   └── alter_add_refresh_tokens.sql  Refresh Token 表增量脚本
├── storage/                   运行时目录（不提交）
├── logs/                      Monolog 日志（按天轮转）
├── tests/
│   └── doctrine_smoke.php     Doctrine ORM 集成冒烟
├── composer.json              PSR-4 自动加载配置
├── run_learn.ps1              启动脚本
├── AGENTS.md                  本文件
└── README.md                  项目文档（结构、知识点、学习顺序）
```

## 代码规范

- 所有 PHP 文件开头声明 `declare(strict_types=1);`
- 使用 PHP 8.x 现代特性：属性类型声明、构造器属性提升、`readonly`、`match`、命名参数、属性注解
- 遵循 PSR-4 自动加载（命名空间 `App\` 对应 `app/` 目录）与 PSR-12 风格
- **禁止残留 `var_dump` / `print_r` 调试代码**
- 注释用中文，说明业务意图与用到的 PHP 特性，并适当与 Java 概念类比
- **控制器不直接 `echo` / `exit`**，统一返回 `JsonResponse::ok()` / `JsonResponse::error()` / `JsonResponse::validation()` / `JsonResponse::noContent()`
- 业务异常用 `throw new BusinessException(...)` 或 `throw new ResourceNotFoundException(...)`，由 `ErrorRenderer` 统一映射为 JSON
- 控制器通过构造器注入 `EntityManager`（php-di 自动装配）

## 数据库规范（全部走 Doctrine）

- **禁止再手写 SQL / 直调 PDO**——所有数据访问通过 Doctrine EntityManager + Repository
- Entity 用 `#[ORM\Entity]` 属性注解，不用 docblock 注解
- 时间字段用 `datetime_immutable`（`\DateTimeInterface`），不用 `datetime`
- `created_at` / `updated_at` 通过 `#[ORM\PrePersist]` / `#[ORM\PreUpdate]` 生命周期回调补齐（DB 不会用列 DEFAULT 覆盖）
- 集合/关联用 `#[ORM\OneToMany]` + `cascade: ['persist', 'remove']` + `orphanRemoval: true`
- 仓储方法命名：`count()` 会冲突（`EntityRepository::count(array $criteria)` 已存在），用 `countFiltered()` / `countAll()` 等
- 表结构变更必须同步更新 `docs/sql/schema.sql` + 对应 Entity 属性注解
- 数据库连接配置**只维护一份**（`config/db`），DoctrineServiceProvider 从这里读
- 表规范：自增主键 `id`，包含 `created_at`、`updated_at` 字段
- 字符集 `utf8mb4`，排序规则 `utf8mb4_0900_ai_ci`（MySQL 8 默认）

## 路由与中间件

- 路由表集中在 `app/api/routes.php`，用 Slim 的 `$app->get()` / `post()` / `put()` / `delete()` 注册
- URL 占位符用 `{id}` 形式，Slim 内部转正则命名捕获组
- 所有 API 路由统一前缀 `/api/v1/*`（版本号在路径里）
- 中间件是 PSR-15 `MiddlewareInterface` 实现（`__invoke($request, $handler)`）：
  ```php
  $app->post('/api/v1/articles', [ApiArticleController::class, 'store'])
       ->add(new ApiAuthenticate());   // 需登录
  ```
- Slim 自动通过 php-di 容器实例化控制器（`[Class::class, 'method']` 数组形式的 action）
- 控制器需要注入依赖时，只需在构造器写类型提示，`bootstrap.php` 里的容器会自动装配
- 全局中间件在 `app/bootstrap.php` 里 `$app->add(...)`：CORS + RequestLogger
- 错误渲染由 `ErrorRenderer`（`Slim\Interfaces\ErrorRendererInterface`）统一处理

## 安全要求

- 密码存储用 `password_hash($pw, PASSWORD_BCRYPT)`，比对用 `password_verify()`
- 登录失败统一返回"账号或密码错误"（模糊化，防账号枚举），并跑一次 dummy bcrypt 防时序攻击
- JWT 校验：`hash_equals()` 常量时间比对签名；`Jwt::verify()` 显式校验 `alg === 'HS256'`，拒绝 `none` / RS256
- Refresh Token 每次 refresh **轮转**：旧令牌立即失效
- Access Token 用 JWT 无状态签名（15 分钟），Refresh Token 存表可撤销（7 天）
- 敏感配置（DB 密码、JWT 密钥）优先从环境变量读取（`DB_PASSWORD` / `JWT_SECRET`），不提交到 Git
- 输出到 HTML 前用 `App\Helpers\Html::e()`（= `htmlspecialchars(ENT_QUOTES)`）转义，防 XSS
- CORS 用白名单严格匹配 Origin，禁止 `*` 配合 credentials

## 变更要求

- 每次改动后运行 `php -l` 语法检查；涉及页面改动时启动服务器实际验证
- 保持改动最小化，不做需求之外的"顺手重构"
- 新增 Composer 依赖前先说明理由，优先选择官方维护、下载量高的包
- 不修改 `vendor/` 目录与 `php.ini` 等全局环境配置
- **禁止使用 `mb_*` 系列函数**（本机 PHP 8.5 未加载 mbstring），用 `Html::charLen()` 替代
- 修改响应格式 / 异常映射前，同步更新 `app/OpenApi/Schemas.php`（避免 spec 漂移）
- 新增 Entity 后必须同步：Repository + Resource（如需要） + `Schemas.php`（如 API 暴露）

## 验证清单（改动路由/模型/中间件后必跑）

```powershell
# 1. 语法检查全部 PHP 文件
Get-ChildItem -Recurse -Include *.php -Exclude '*vendor*' | ForEach-Object {
  & 'F:\programs\php-8.5.10-nts-Win32-vs17-x64\php.exe' -l $_.FullName 2>&1
}

# 2. Composer
composer install
composer dump-autoload --optimize

# 3. 启动服务器
& 'F:\programs\php-8.5.10-nts-Win32-vs17-x64\php.exe' -S localhost:8000 -t public

# 4. 冒烟测试（预期状态码）
#    GET  /api/v1/health            → 200  健康检查
#    GET  /api/v1/docs              → 200  Swagger UI
#    GET  /api/v1/docs/spec         → 200  OpenAPI JSON
#    GET  /api/v1/articles          → 200  文章列表（分页）
#    GET  /api/v1/articles/{id}     → 200 / 404
#    GET  /api/v1/activities        → 200
#    POST /api/v1/auth/login        → 200  登录成功（签发双令牌）
#    POST /api/v1/auth/refresh      → 200  轮转 Refresh Token
#    POST /api/v1/auth/logout       → 204  登出
#    GET  /api/v1/auth/me           → 200（需 token）/ 401
#    POST /api/v1/articles 无 token → 401
#    POST /api/v1/articles 带 token → 201
#    PUT  /api/v1/articles/{id}     → 200（带 token）
#    DELETE /api/v1/articles/{id}   → 204（带 token）
#    GET  /nope                     → 404
```
