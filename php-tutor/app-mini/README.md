# 多用户博客 REST API

基于 **Slim 4 + PHP-DI + Eloquent + JWT** 实现的多用户博客**纯 REST API**(除 Swagger UI 文档页外无任何前端页面)。`composer install` 后配置 `.env`、执行迁移即可运行。

## 技术栈(禁止引入其他框架)

| 关注点 | 选型 |
|---|---|
| 框架 | Slim 4 + slim/psr7 |
| 容器 | PHP-DI,经 php-di/slim-bridge 接入 |
| ORM | illuminate/database(Eloquent),禁用 Laravel 其他组件 |
| 认证 | lcobucci/jwt ^5.5 + lcobucci/clock,无状态 Bearer Token(HS256) |
| 校验 | respect/validation |
| CORS | tuupola/cors-middleware |
| 日志 | monolog → `storage/logs/app.log` |
| 环境变量 | vlucas/phpdotenv |
| 文档 | zircote/swagger-php(PHP 8 attributes),Swagger UI 走 CDN |

PHP >= 8.2,PSR-12,所有文件 `declare(strict_types=1)`。

## 环境要求

- PHP >= 8.2(已在 PHP 8.5 验证)
- Composer 2
- 数据库:MySQL / PostgreSQL / SQLite 任一
- 必需扩展:`pdo_<driver>`、`mbstring`、`openssl`、`json`、`sodium`
  > `lcobucci/jwt` 5.6 硬依赖 `ext-sodium`;若 `php -m` 没有 sodium,请在 `php.ini` 启用 `extension=sodium`,或运行时加 `php -d extension=sodium`。
- 运行测试还需 `pdo_sqlite` + `sqlite3`(在 `php.ini` 启用 `extension=pdo_sqlite` / `extension=sqlite3`)。

## 目录结构

```
public/index.php            # 入口:.env → 容器 → 依赖 → 路由 → run
config/
  app.php                   # 路径/env/app_config 等配置读取函数
  database.php              # Eloquent 连接配置
  dependencies.php          # PHP-DI 定义
app/
  Controllers/              # Auth/Post/User/Docs Controller
  Middleware/               # AuthMiddleware, AccessLogMiddleware
  Models/                   # User, Post(Eloquent,含关联)
  Services/                 # AuthService, PostService, UserService
  Security/JwtService.php   # 签发/解析/校验 JWT
  Http/                     # ApiResponder, ApiErrorHandler, ApiErrorRenderer
  Exceptions/               # ApiException 及其子类
  Validation/               # Validator + Rules(Respect/Validation 封装)
  Database/                 # Manager(Capsule 封装), Migrator
  Enums/                    # PostStatus
  OpenApi/                  # OpenApiDefinition + Schemas/(组件 schema)
routes/api.php              # 路由与中间件注册
database/
  migrations/               # 001_create_users, 002_create_posts
  seeders/DatabaseSeeder.php
scripts/
  migrate.php               # 依次执行迁移,记录到 migrations 表(支持 rollback)
  seed.php                  # 5 用户 × 3~5 篇已发布文章
storage/logs/.gitkeep
tests/                      # JwtService 单元 + Auth/Post 鉴权功能测试
.env.example
```

## 安装与运行

```bash
# 1. 安装依赖(含新增的 zircote/swagger-php)
composer install

# 2. 配置环境
cp .env.example .env
#   编辑 .env:数据库连接、JWT_SECRET(务必改为随机长字符串)

# 3. 迁移 + 填充
php scripts/migrate.php
php scripts/seed.php

# 4. 启动(任选其一)
php -S localhost:8080 -t public
# 若 sodium 未在 php.ini 启用:
#   php -d extension=sodium -S localhost:8080 -t public
```

打开文档:http://localhost:8080/docs(Swagger UI,CDN 引入)。
规范 JSON:http://localhost:8080/docs/openapi.json

## API 概览

| 方法 | 路径 | 鉴权 | 说明 |
|---|---|---|---|
| POST | /api/v1/auth/register | 否 | 注册 |
| POST | /api/v1/auth/login | 否 | 登录,返回 access_token |
| GET  | /api/v1/auth/me | 是 | 当前用户 |
| GET  | /api/v1/posts | 否 | 分页(page/per_page),?author_id=、?q= 标题模糊 |
| GET  | /api/v1/posts/{id} | 否 | 详情(仅已发布) |
| POST | /api/v1/posts | 是 | 创建 |
| PUT  | /api/v1/posts/{id} | 是(作者) | 更新 |
| DELETE | /api/v1/posts/{id} | 是(作者) | 删除 |
| GET  | /api/v1/users/{id} | 否 | 公开资料 + 已发布文章 |

### 统一响应信封

```json
// 成功
{"success": true, "data": {...}, "meta": {"total": 25, "page": 1, "per_page": 15, "last_page": 2}}
// 失败
{"success": false, "error": {"code": "validation_failed", "message": "参数校验失败", "details": {"email": ["该邮箱已被注册"]}}}
```

状态码语义:201 创建成功、422 校验失败、401 未登录、403 无权限、404 不存在、500 服务器错误。

## curl 验证流程

```bash
BASE=http://localhost:8080

# 1) 注册
curl -s -X POST $BASE/api/v1/auth/register \
  -H 'Content-Type: application/json' \
  -d '{"name":"张三","email":"zhangsan@example.com","password":"password123"}'

# 2) 登录,取 access_token
TOKEN=$(curl -s -X POST $BASE/api/v1/auth/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"zhangsan@example.com","password":"password123"}' \
  | python -c "import sys,json;print(json.load(sys.stdin)['data']['token'])")
echo $TOKEN

# 3) 带 token 创建文章
curl -s -X POST $BASE/api/v1/posts \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Content-Type: application/json' \
  -d '{"title":"我的第一篇文章","content":"正文","status":"published"}'

# 4) 匿名列表(无需 token)
curl -s "$BASE/api/v1/posts?per_page=5"

# 5) 当前用户
curl -s $BASE/api/v1/auth/me -H "Authorization: Bearer $TOKEN"

# 6) 越权:用另一用户改别人的文章 → 403
# (注册 bob 后,用 bob 的 token PUT /api/v1/posts/{id})
```

## 运行测试

测试使用 SQLite 内存库,每个用例独立迁移,互不依赖。

```bash
vendor/bin/phpunit
# 若 sqlite/sodium 未在 php.ini 启用:
#   php -d extension=pdo_sqlite -d extension=sqlite3 -d extension=sodium vendor/phpunit/phpunit/phpunit
```

覆盖:
- `tests/Unit/JwtServiceTest`:签发/校验/过期/篡改/异密钥/空密钥
- `tests/Feature/AuthTest`:注册/重复邮箱/非法邮箱/短密码/登录成功/错误密码 401/me 401/me 200/篡改 token 401
- `tests/Feature/PostAuthorizationTest`:非作者改/删 403、作者可改 200、不存在 404、未登录创建 401、公开列表仅已发布、slug 唯一

## 实现要点

- **容器**:PHP-DI,业务代码一律构造函数注入,依赖在 `config/dependencies.php` 集中声明;`Manager` 单例。
- **Eloquent**:`App\Database\Manager` 封装 Capsule,全部查询走 Eloquent,不写原生 SQL。
- **JWT**:`App\Security\JwtService` 使用 lcobucci/jwt 5.x 的 `Configuration`/`Signer`/`Validator`,时钟注入 `lcobucci/clock`。
- **错误处理**:`ApiErrorHandler` 覆盖 `determineStatusCode`/`writeToErrorLog`/`logError`/`respond`,所有异常(含 500)渲染为统一 JSON;Monolog 记录完整堆栈。
- **中间件顺序**(LIFO):CORS(最外)→ Error → AccessLog → Routing → BodyParsing(最内)。CORS 在最外确保错误响应也带 CORS 头;AccessLog 捕获并记录异常后继续抛出。
- **OpenAPI**:用 PHP 8 attributes(`#[OA\...]`)写注解,`GET /docs/openapi.json` 扫描 `app/` 生成规范并动态注入 server;`GET /docs` 返回 CDN 版 Swagger UI。
