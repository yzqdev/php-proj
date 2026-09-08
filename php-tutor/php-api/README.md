# php-tutor-api

多用户博客 REST API（Slim 4 + PHP-DI + Eloquent + JWT v5 + Respect Validation）。

用户注册 / 登录 / 刷新令牌，文章与评论的增删改查，统一 JSON 响应与错误结构，request-id 全链路日志。

## 环境要求

- PHP >= 8.2（已在 PHP 8.5 验证；`composer.json` 中 `config.platform.php=8.4.0`，见「坑 1」）
- Composer 2.x
- MySQL 8.0 / MariaDB
- PHP 扩展：`pdo_mysql`、`mbstring`、`openssl`

## 安装

```bash
# 1. 安装依赖
composer install

# 2. 配置环境变量
cp .env.example .env
# 编辑 .env：DB_HOST / DB_PORT / DB_DATABASE / DB_USERNAME / DB_PASSWORD
# 以及 JWT_SECRET（生产环境务必换成足够长的随机串）

# 3. 建库（如尚未创建）
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS php_tutor_api CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci"

# 4. 迁移（幂等，可重复执行）
php scripts/migrate.php

# 5. 填充测试数据（Faker zh_CN，含 demo 账号）
php scripts/seed.php
```

## 启动

```bash
php -S 127.0.0.1:8099 -t public
# 或指定 php.ini：php -c /path/to/php.ini -S 127.0.0.1:8099 -t public
```

## 冒烟测试

```bash
# 先启动服务器（另一终端），然后：
php tests/smoke.php
# 覆盖：列表/详情/404/422中文校验/注册/登录/错误密码/伪造token/refresh/文章与评论CRUD/CORS预检
```

## 接口一览（前缀 /api/v1）

| Method | URI | 说明 | 鉴权 |
|--------|-----|------|------|
| POST | /auth/register | 注册，返回令牌对 | 否 |
| POST | /auth/login | 登录，返回令牌对 | 否 |
| POST | /auth/refresh | 用 refresh_token 换新令牌对 | 否（body 传 refresh_token） |
| GET | /auth/me | 当前用户信息 | Bearer |
| GET | /articles?page=&per_page= | 文章分页列表 | 否 |
| GET | /articles/{id} | 文章详情 | 否 |
| POST | /articles | 创建文章 | Bearer |
| PUT | /articles/{id} | 更新（仅作者） | Bearer |
| DELETE | /articles/{id} | 删除（仅作者） | Bearer |
| GET | /articles/{id}/comments | 评论列表 | 否 |
| POST | /articles/{id}/comments | 发表评论 | Bearer |
| DELETE | /comments/{id} | 删除评论（仅作者） | Bearer |

### 响应结构

成功：

```json
{ "data": { }, "request_id": "1a2b3c4d" }
```

错误（校验失败示例，422）：

```json
{
    "code": 422,
    "message": "参数校验失败",
    "request_id": "1a2b3c4d",
    "errors": {
        "username": ["用户名 must not be empty"],
        "email": ["邮箱必须为有效的邮箱地址"]
    }
}
```

### 快速上手示例

```bash
# 注册
curl -X POST http://127.0.0.1:8099/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{"username":"alice","email":"alice@test.com","password":"password123"}'

# 登录拿 token（或用种子账号 demo@example.com / demo1234）
curl -X POST http://127.0.0.1:8099/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"demo@example.com","password":"demo1234"}'

# 带 token 访问受保护路由
curl http://127.0.0.1:8099/api/v1/auth/me \
  -H "Authorization: Bearer <access_token>"
```

## 中间件执行顺序（LIFO）

注册顺序（代码书写）→ 实际执行顺序（先→后）：

```
CorsMiddleware        (最后 add)   → 1. 最先执行，拦 OPTIONS 预检
RequestIdMiddleware                → 2. 分配 request-id，贯穿日志
ErrorMiddleware                    → 3. 捕获一切异常 → 统一 JSON 错误
BodyParsingMiddleware              → 4. 解析 JSON body
[路由级 JwtAuthMiddleware]          → 5. 仅受保护路由
[Controller]                       → 6. 最后执行
```

## 本项目的坑

10 条硬约束的落地位置与踩坑实录：

1. **PHP 版本 vs lcobucci/jwt 5.5**
   `5.5.0` 只支持 PHP `~8.2 || ~8.3 || ~8.4`，在 PHP 8.5 上 `composer update` 会拒绝安装。
   解决：`composer.json` 里 `"config": {"platform": {"php": "8.4.0"}}` 锁平台版本。
   （实际代码运行于 8.5 正常；如你的 PHP 是 8.2~8.4 可删掉这段。）

2. **Eloquent 独立使用，无 artisan**
   `bootstrap/dependencies.php` 用 `Capsule::Manager` + `setAsGlobal()` + `bootEloquent()` 初始化。
   迁移/种子是手写脚本 `scripts/migrate.php` / `scripts/seed.php`；
   幂等靠 `Schema::hasTable()` 前置判断 + `migrations` 表登记，支持 `rollback` / `refresh`。

3. **JWT v5 API**
   `app/Services/TokenService.php`：
   - 构建：`new Token\Builder(new JoseEncoder(), ChainedFormatter::default())`
   - 序列化：仅 `$token->toString()`，绝不 `(string) $token`
   - 校验：`Token\Parser` + `Validator` + `SignedWith` / `StrictValidAt(new SystemClock(new DateTimeZone('UTC')))` / `PermittedFor`

4. **v5 Builder 是不可变对象（本次实际踩坑）**
   `$builder->issuedBy(...)` 之后不接收返回值 → claims 全部丢失，签出的 JWT payload 是 `[]`。
   必须**链式调用**（或逐步重新赋值）。见 `TokenService::issue()`。

5. **参数校验捕获 NestedValidationException**
   `app/Validation/Validator.php` 捕获 `Respect\Validation\Exceptions\NestedValidationException`，
   用 `->getMessages()` 取字段级错误；每个规则先 `->setName('用户名')` 等中文名。
   `ValidationFailedException` 携带 `field => messages` 数组 → Renderer 输出 422。

6. **CORS v1 配置键是 `headers.allow`**
   `bootstrap/middleware.php`。写成 v0 的 `allow.headers` 不会报错、只会静默失效——预检直接挂。

7. **中间件 LIFO**
   CORS 必须**最后** `add()` 才**最先**执行。`bootstrap/app.php` + `middleware.php` 有完整注释图。

8. **Monolog v3 枚举日志级别 + UidProcessor**
   `app/Support/LoggerFactory.php`：`Monolog\Level::Debug` 枚举（不再是 `Logger::DEBUG` 整数），
   `StreamHandler` 第三参 `bool $bubble = true`。
   注意：v3 的 `UidProcessor` 构造参数是 uid **长度**，不能注入自定义 id；
   要让外部 request-id 生效，需 `$processor->reset()` + 反射改写 `uid` 属性，
   见 `app/Middleware/RequestIdMiddleware.php`。

9. **日志相对路径被内置服务器坑（本次实际踩坑）**
   `php -S ... -t public` 会把 CWD 切到 `public/`，`.env` 里 `LOG_PATH=./storage/logs/app.log`
   实际写到了 `public/storage/logs/`。
   `LoggerFactory` 现在把相对路径锚定到项目根（`dirname(__DIR__, 2)`），相对路径安全。

10. **dotenv 不可变模式 + Env::get() 统一入口**
    `public/index.php`：`Dotenv::createImmutable()`。业务代码一律 `App\Support\Env::get()`，
    禁止散落 `getenv()`。（放弃全局 `env()` 函数写法：类方法不会被 Composer classmap 重复加载坑到。）

11. **Faker 显式 zh_CN**
    `database/seeders/DatabaseSeeder.php`：`Faker\Factory::create('zh_CN')`，否则造出英文数据。

12. **illuminate/pagination 需要显式安装**
    `illuminate/database` 的 `paginate()` 依赖 `illuminate/pagination`，但它只是 composer 建议（suggest），
    不装的话运行时才报错。已在 `composer.json` 显式 require `^11`。

13. **Facade 全部禁用**
    `illuminate/support` 的 `Schema` / `DB` Facade 在无容器初始化时会抛
    `A facade root has not been set`。迁移文件全部改为接收 `Schema\Builder` 实例参数（见 `database/migrations/*`）。

## 项目结构

```
├── public/index.php            # 唯一入口
├── bootstrap/app.php           # 容器 + 中间件 + 路由装配
├── bootstrap/dependencies.php  # PHP-DI 定义
├── bootstrap/middleware.php    # 中间件注册（LIFO）
├── routes/index.php            # /api/v1 路由表
├── app/
│   ├── Controllers/            # 单动作控制器（__invoke + match 分派）
│   ├── Middleware/             # JwtAuthMiddleware / RequestIdMiddleware
│   ├── Models/                 # Eloquent 模型
│   ├── Services/               # AuthService / TokenService / ArticleService / CommentService
│   ├── Validation/             # Respect 规则集合 + 校验器
│   └── Support/                # Response / Logger / Env / 异常体系
├── config/                     # database.php / jwt.php / app.php
├── database/
│   ├── migrations/             # 001_xxx.php 顺序命名
│   └── seeders/
├── scripts/                    # migrate.php / seed.php
├── storage/logs/
├── tests/smoke.php             # 冒烟测试（27 项断言）
├── .env.example
└── README.md
```
