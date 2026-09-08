# php-learn

面向 **Java / Spring Boot 开发者** 的 PHP 8.x 学习项目。

后端使用 **Slim 4 + php-di + Monolog + PSR-15 + Doctrine ORM**，
鉴权为 **Bearer Token（JWT Access + Refresh Token 存表）**，
API 全部走 `/api/v1/*`，Swagger UI 在 `/api/v1/docs`。

> 对应 Java：这更像"Spring Boot + Spring Security + springdoc-openapi"的极简版；
> PHP 端每一段关键代码都配了中文注释，并标注 Java 生态对照（Composer ↔ Maven、PDO ↔ JDBC 等）。

---

## 快速开始

前置条件（本机已就绪）：

| 组件 | 版本 | 说明 |
| --- | --- | --- |
| PHP | 8.5 | `F:\programs\php-8.5.10-nts-Win32-vs17-x64\php.exe`（不在 PATH，用绝对路径或加到环境变量） |
| Composer | 已配置阿里云镜像 | `composer install` |
| MySQL | 8.4 | 127.0.0.1:3306，root / 123456 |

### 1. 安装依赖

```powershell
composer install
```

主要第三方依赖：`slim/slim`（Web 框架）、`php-di/php-di`（IoC 容器）、
`monolog/monolog`（PSR-3 日志）、`doctrine/orm`（ORM）、
`doctrine/dbal`（连接抽象）、`doctrine/migrations`（迁移）、
`symfony/cache`（PSR-6 缓存）、`symfony/console`（CLI）。

### 2. 准备配置

```powershell
Copy-Item config\config.example.php config\config.php
# 打开 config\config.php，把 db.password / api.jwt.secret 改成真实值
# 或用环境变量注入：$env:DB_PASSWORD='123456'; $env:JWT_SECRET='<base64>'
```

`config/config.php` 已被 `.gitignore` 忽略，不会被提交。
配置文件内的敏感字段（`db.password`、`api.jwt.secret` 等）优先从环境变量读取，
本地开发不用注入即用默认值，生产只需注入环境变量。

### 3. 初始化数据库

```powershell
mysql -uroot -p < docs\sql\schema.sql
mysql -uroot -p < docs\sql\alter_add_refresh_tokens.sql
```

或者用 Doctrine：

```powershell
composer schema:create    # 按 Entity 属性注解直接建表（学习/原型用）
composer migrate          # 走 migrations:migrate
```

### 4. 启动开发服务器

```powershell
php -S localhost:8000 -t public
# 或项目自带脚本（默认 6850 端口，含打印文档地址）：
.\run_learn.ps1
```

### 5. 冒烟测试（预期状态码）

| 端点 | 状态码 |
| --- | --- |
| `GET /api/v1/health` | 200 |
| `GET /api/v1/docs` | 200（Swagger UI HTML） |
| `GET /api/v1/docs/spec` | 200（OpenAPI 3.0 JSON） |
| `GET /api/v1/articles` | 200（分页列表） |
| `GET /api/v1/articles/{id}` | 200 / 404 |
| `GET /api/v1/activities` | 200 |
| `POST /api/v1/auth/login` | 200（签发双令牌） |
| `POST /api/v1/articles`（无 token） | 401 |
| `POST /api/v1/articles`（带 token） | 201 |
| `DELETE /api/v1/articles/{id}`（带 token） | 204 |
| `GET /nope` | 404 |

**种子账号**：

| 用户名 | 密码 | 角色 |
| --- | --- | --- |
| `admin` | `admin123` | 管理员 |
| `user` | `user123` | 普通用户 |

---

## 项目结构

```
php-learn/
├── public/                    ← Web 入口（对外暴露的唯一目录）
│   ├── index.php              唯一入口（Front Controller → Slim run）
│   ├── assets/                静态资源（CSS/JS）
│   ├── files/                 文件 IO 演示用文件
│   └── vendor/swagger-ui/     Swagger UI 静态资源（本地，不依赖 CDN）
│
├── app/                       ← 业务代码
│   ├── bootstrap.php          应用引导（错误处理、php-di 容器、Doctrine、Slim、中间件）
│   ├── api/routes.php         API v1 路由表（唯一的路由表）
│   ├── Controllers/Api/       控制器（对应 @Controller）
│   │   ├── ArticleController.php    文章 CRUD + 分页
│   │   ├── ActivityController.php   活动 CRUD
│   │   ├── AuthController.php       登录 / 刷新 / 登出 / me
│   │   ├── HealthController.php     健康检查
│   │   └── ApiDocsController.php    Swagger UI + OpenAPI spec
│   ├── Middleware/            中间件（PSR-15）
│   │   ├── Cors.php           CORS 白名单（支持 OPTIONS 预检）
│   │   ├── RequestLogger.php  请求前后日志
│   │   ├── ApiAuthenticate.php     Bearer Token 校验
│   │   └── ErrorRenderer.php  Slim 统一错误渲染（异常 → JSON）
│   ├── Entities/              Doctrine ORM 全部实体 + 仓储
│   │   ├── User.php           #[ORM\Entity] + 一对多 refreshTokens
│   │   ├── UserRepository.php     findByLogin / deleteById
│   │   ├── Article.php        #[ORM\Entity] + PrePersist/PreUpdate
│   │   ├── ArticleRepository.php  paginate + countFiltered
│   │   ├── Activity.php       #[ORM\Entity] + DateTimeImmutable 时间字段
│   │   ├── ActivityRepository.php     paginate(upcoming) + countFiltered
│   │   ├── RefreshToken.php   #[ORM\Entity] 存 Refresh Token
│   │   └── RefreshTokenRepository.php findValid / revoke / revokeAll
│   ├── Services/              业务服务
│   │   ├── Jwt.php            HS256 手写实现（学习期不引 firebase/php-jwt）
│   │   ├── TokenService.php   Access + Refresh 双令牌签发/校验/撤销
│   │   └── DoctrineServiceProvider.php  组装 EntityManager
│   ├── Exceptions/            业务异常
│   │   ├── BusinessException.php            携带 bizCode + httpStatus
│   │   └── ResourceNotFoundException.php    HTTP 404 子类
│   ├── Resources/             序列化层（Entity → API camelCase）
│   │   ├── ArticleResource.php
│   │   └── ActivityResource.php
│   ├── Helpers/               工具类
│   │   ├── Config.php         点号路径配置读取
│   │   ├── Request.php        PSR-7 → 便捷 input() / routeParam()
│   │   ├── RequestBody.php    从 PSR-7 解析 JSON body
│   │   ├── JsonResponse.php   统一 {code, message, data, errors?} 响应
│   │   ├── Logger.php         Monolog 静态门面
│   │   └── Html.php           输出转义 + 中文长度（无 mbstring 兼容）
│   ├── OpenApi/               OpenAPI 3.0 生成器
│   │   ├── Schemas.php        Schema 定义（单一数据源）
│   │   └── SpecGenerator.php  反射扫描 Controller PHPDoc → spec
│   ├── Console/               Doctrine CLI 入口
│   │   └── DoctrineConsole.php   schema:create / migrate / db:drop
│   └── Migrations/            Doctrine 迁移类目录（默认空）
│
├── client/                    ← Vue 3 + Vite 前端 SPA
├── config/                    ← 配置
│   ├── config.example.php     模板（可提交）
│   └── config.php             真实配置（.gitignore 忽略）
├── docs/sql/                  ← 数据库脚本
│   ├── schema.sql             建库建表 + 种子数据
│   └── alter_add_refresh_tokens.sql  Refresh Token 表增量脚本
├── storage/                   ← 运行时目录（不提交）
├── logs/                      ← Monolog 日志（按天轮转）
├── tests/                     ← 冒烟测试（CLI）
│   └── doctrine_smoke.php     Doctrine ORM 集成冒烟
├── composer.json              PSR-4 自动加载 + scripts
├── run_learn.ps1              启动脚本
├── AGENTS.md                  AI 代理工作规范
└── README.md                  本文件
```

---

## 各模块对应的知识点

### 1. 入口与容器

**文件**：`public/index.php` → `app/bootstrap.php` → `composer.json`

| 知识点 | Java 对照 |
| --- | --- |
| Front Controller 模式 | `DispatcherServlet` |
| php-di 容器 + Autowiring | Spring IoC 容器 |
| `declare(strict_types=1)` | Java 没有对应（PHP 类型是"软"的） |
| PSR-4 自动加载 | Maven / Gradle 的 classpath |
| PSR-15 中间件 | Spring MVC 的 Filter / HandlerInterceptor |
| Slim 的 ErrorRendererInterface | `@ControllerAdvice + @ExceptionHandler` |

### 2. API 路由

**文件**：`app/api/routes.php`

```php
$app->group('/api/v1', function ($api) {
    $api->get('/articles',       [ArticleController::class, 'index']);
    $api->post('/articles',      [ArticleController::class, 'store'])
        ->add(new ApiAuthenticate());   // 需登录
    // ...
});
```

- 版本号在路径里（`/api/v1/*`），未来 `v2` 可并存。
- 需鉴权的路由挂 `ApiAuthenticate`（Bearer Token）。
- 对比 Spring：`@RequestMapping("/api/v1/...")` 类级别前缀。

### 3. 请求 / 响应

**文件**：`app/Helpers/Request.php`、`app/Helpers/RequestBody.php`、`app/Helpers/JsonResponse.php`

- 请求：`Request::fromPsr7($psr7)` → `input()` / `routeParam()`；`RequestBody::json($psr7)` 读 JSON body。
- 响应：`JsonResponse::ok($data, 'msg', 200)` / `validation($errors)` / `error($code, 'msg', 4xx)` / `noContent()`。
- 统一格式：`{code, message, data, errors?}`，HTTP 状态码与业务 code 分离。

对应 Java：`ResponseEntity<T>` + `@RestController` + `@RequestBody`。

### 4. 数据库层（全部走 Doctrine ORM）

**文件**：`app/Entities/*`、`app/Services/DoctrineServiceProvider.php`

项目统一使用 Doctrine ORM，没有手写 PDO / SQL：

- **Entity**：`#[ORM\Entity]` + `#[ORM\Column]` 属性注解，与 JPA `@Entity` 一一对应。
- **Repository**：`extends EntityRepository`，手写 DQL / Criteria（`createQueryBuilder`）。
- **EntityManager**：由 `DoctrineServiceProvider::createEntityManager()` 组装，注入到 php-di 容器。
- **Unit of Work**：`$em->persist($entity)` → `$em->flush()` 自动 diff 后 INSERT/UPDATE，事务默认包裹。
- **生命周期**：`#[ORM\PrePersist]` / `#[ORM\PreUpdate]` 补齐 `created_at` / `updated_at`。

代码示例：

```php
// ArticleController@index
$repo = $this->em->getRepository(Article::class);
$list  = $repo->paginate($page, $pageSize, $category, $status);
$total = $repo->countFiltered($category, $status);

// ArticleController@store
$article = new Article();
$article->setTitle(trim($body['title']))->setBody(trim($body['body']));
$this->em->persist($article);
$this->em->flush();          // 事务提交，Identity Map 同步
```

数据库迁移：

```powershell
composer schema:create      # 按 Entity 属性注解直接建表（学习/原型）
composer migrate            # 走 migrations:migrate（生产）
composer migrate:status     # 查看迁移状态
```

### 5. 认证（Bearer Token 双令牌）

**文件**：`app/Services/Jwt.php`、`app/Services/TokenService.php`、`app/Middleware/ApiAuthenticate.php`

- Access Token：JWT（HS256），15 分钟，无状态签名。
- Refresh Token：32 字节随机 hex（64 字符），7 天，存表可撤销。
- 每次刷新**轮转** Refresh Token：旧令牌立即失效，防泄露后被继续利用。
- 登出撤销所有 Refresh Token；Access Token 靠自然过期。

对应 Java：Spring Security 的 OAuth2 Resource Server + RefreshToken 存表。

| 安全点 | 实现 |
| --- | --- |
| 密码哈希 | `password_hash($pw, PASSWORD_BCRYPT)` |
| 密码比对 | `password_verify($pw, $hash)` 常量时间 |
| 时序攻击 | JWT 签名比对用 `hash_equals()` |
| alg 混淆 | `Jwt::verify()` 显式校验 `alg === 'HS256'`，拒绝 `none` / RS256 |
| 账号枚举 | 登录失败统一返回"账号或密码错误"，同时跑一次 dummy bcrypt |

### 6. 错误处理

**文件**：`app/Exceptions/BusinessException.php`、`app/Middleware/ErrorRenderer.php`、`app/bootstrap.php`

- 所有异常统一由 Slim 的 ErrorRendererInterface 转 JSON，格式与 `JsonResponse::error()` 一致。
- `BusinessException` 携带 `bizCode` + `httpStatus`，可直接映射；其他异常一律 500（debug 模式返回细节）。
- `ResourceNotFoundException` 是 `BusinessException` 的 404 子类。
- 生产环境（`app.debug = false`）不暴露内部 message / trace，只记到日志。

### 7. 日志

**文件**：`app/Helpers/MyLogger.php`（Monolog 封装）

- `Logger::info('用户登录', ['userId' => 1])` → PSR-3 结构化日志。
- `RotatingFileHandler` 按天轮转，`max_files = 30` 保留最近 30 天。
- 异常全部由 `ErrorRenderer` 记录，不用手动 `Logger::error(...)`。

对应 Java：`@Autowired Logger`（SLF4J + Logback）。

### 8. XSS 输出转义

**文件**：`app/Helpers/Html.php`

`Html::e($value)` = `htmlspecialchars(ENT_QUOTES)`，输出到 HTML 前必须过。
`Html::charLen($s)` 处理中文长度（本机 PHP 8.5 未加载 mbstring，内部走 `preg_match_all('/./us')`）。

### 9. OpenAPI 文档

**文件**：`app/OpenApi/SpecGenerator.php`、`app/OpenApi/Schemas.php`

- Controller 方法用 PHPDoc 声明 `@summary / @description / @tag / @parameter / @requestBody / @response`。
- `SpecGenerator` 反射扫描 → 拼成 OpenAPI 3.0 JSON。
- Swagger UI 页面本地静态资源（`public/vendor/swagger-ui/`），不依赖 CDN。

对应 Java：`springdoc-openapi-ui`（本项目手写等价物）。

---

## Java ↔ PHP 概念对照表

| Java / Spring Boot | PHP / 本项目 |
| --- | --- |
| `pom.xml` / `build.gradle` | `composer.json`（PSR-4 是"命名空间 → 目录"映射规则） |
| `DispatcherServlet` | `public/index.php`（Front Controller → Slim run） |
| IoC 容器 | `php-di`（`useAutowiring(true)`） |
| `@RequestMapping` | `$app->get('/api/v1/...')` 显式注册 |
| `@ControllerAdvice` | `Slim\Interfaces\ErrorRendererInterface` |
| JDBC / `Connection` | doctrine/dbal `Connection` | 底层同样封装 PDO |
| `PreparedStatement` | Doctrine DQL / Criteria | ORM 生成参数化 SQL |
| `HttpServletRequest` | `ServerRequestInterface`（PSR-7） |
| `@RequestBody Map` | `RequestBody::json($psr7)` |
| `ResponseEntity<T>` | `JsonResponse::ok(...)`（返回 PSR-7 `ResponseInterface`） |
| `@Entity` + JPA | `#[ORM\Entity]` + Doctrine |
| Spring Data Repository | `EntityRepository`（`findByLogin` 等） |
| `@Valid` / Hibernate Validator | 控制器内的字段校验（项目保持"看得到 SQL 和校验"的手写风格） |
| Spring Security + JWT | `Jwt::verify()`（HS256 手写） + `TokenService` |
| `application.yml` + `@Value` | `config/config.php`（环境变量优先） |
| `@PreAuthorize` | `ApiAuthenticate`（PSR-15 中间件） |
| `Page<T>` | `JsonResponse::paginate(...)` 返回 `{list, total, page, pageSize, totalPage}` |
| `springdoc-openapi-ui` | `SpecGenerator` + 本地 Swagger UI |
| SLF4J + Logback | Monolog（PSR-3） |
| Flyway / Liquibase | Doctrine Migrations |

---

## 建议的阅读顺序

1. **`composer.json` + `public/index.php` + `app/bootstrap.php`** — 看"请求如何进来，容器如何组装，错误如何统一处理"
2. **`app/api/routes.php`** — 看路由表 + 中间件挂法
3. **`app/Middleware/ApiAuthenticate.php` + `app/Services/Jwt.php` + `app/Services/TokenService.php`** — 看鉴权全链路
4. **`app/Entities/User.php` + `app/Entities/UserRepository.php`** — 看"Entity + Repository 怎么配对"
5. **`app/Services/DoctrineServiceProvider.php`** — 看 EntityManager 怎么组装
6. **`app/Controllers/Api/ArticleController.php` + `app/Entities/ArticleRepository.php`** — 完整 CRUD + 分页流程
7. **`app/Helpers/JsonResponse.php` + `app/Exceptions/BusinessException.php` + `app/Middleware/ErrorRenderer.php`** — 看"统一响应与异常"
8. **`app/OpenApi/SpecGenerator.php`** — 看 OpenAPI 怎么自动生成
9. **对照文档**：`app/Controllers/Api/SwaggerController.php` → `GET /api/v1/docs`

---

## 常见错误排查

| 现象 | 原因 | 解决 |
| --- | --- | --- |
| `找不到配置文件 config/config.php` | 没复制配置文件 | `Copy-Item config\config.example.php config\config.php` |
| `SQLSTATE[HY000] [1045] Access denied` | 数据库密码错 | 改 `config/config.php` 的 `db.password` 或 `$env:DB_PASSWORD` |
| `SQLSTATE[HY000] [1049] Unknown database 'php_learn'` | 没建库 | `mysql -uroot -p < docs\sql\schema.sql` |
| `JWT secret 未配置` | 密钥是占位符 | 改 `config.php` 的 `api.jwt.secret` 或 `$env:JWT_SECRET` |
| CORS 拒绝 | Origin 不在白名单 | 改 `config.php` 的 `api.cors.allowed_origins` |
| `401 未提供访问令牌` | 请求头没带 `Authorization: Bearer xxx` | 前端加 token |
| `401 令牌无效或已过期` | Access Token 过期 | 用 Refresh Token 调 `/api/v1/auth/refresh` |
| 中文乱码 | 连接字符集不对 | `config.php` 里 `db.charset = 'utf8mb4'` |
| `class not found: App\...` | Composer 自动加载没跑 | `composer dump-autoload` |

---

## 扩展练习

1. **给 Activity 加搜索条件**（title LIKE），复用 ArticleController 的 `ArticleRepository::countFiltered` 模式
2. **实现事务**：`$em->wrapInTransaction(fn () => ...)` 或 `$em->getConnection()->beginTransaction()`
3. **实现文件上传**：`$_FILES` + `move_uploaded_file()` + 扩展名白名单
4. **给 Article 加"标签"多对多关系**（用 Doctrine `#[ORM\ManyToMany]`）
5. **迁移到 firebase/php-jwt**：把 `Jwt` 类换成库实现，看手写与库的差距
6. **加 Prometheus 指标**：`RequestLogger` 里统计 QPS / P95
7. **加 rate limiting**：Redis + 滑动窗口中间件
8. **给 Article 加全文搜索**：MySQL FULLTEXT 或 Elasticsearch

---

## 许可证

MIT（学习用途）
