# slim-lab —— 原生 PHP 项目迁移 Slim 4 后端

后端已迁移到 **Slim 4**（统一入口 `public/index.php`），集成 **Swagger UI**（OpenAPI 文档运行时自动生成）。接口地址为无后缀的现代风格（`/api/clouddrive`），同时保留 `/api/clouddrive.php` 兼容别名；接口的参数名、响应 JSON 结构与迁移前保持一致。

## 目录结构

```
public/          # 唯一对外暴露目录（index.php 统一入口 + .user.ini + .htaccess）
app/routes.php   # 全部路由定义
src/             # 业务代码（PSR-4：Service\、Entity\）
  Service/Http/        # 控制器、响应工厂、错误处理
  Service/Http/OpenApi/ # OpenAPI 组件 schema（供文档注解引用）
  Service/Http/Docs/    # Swagger UI + openapi.json 控制器
  Service/Middleware/   # 会话 / 鉴权 / CORS 中间件
  Service/Docs/         # 文档相关工具
  Entity/               # Doctrine 实体
web/             # Vue 3 前端（vite，端口 5173）
storage/         # 全部运行时数据（.gitignore 整体忽略）
  cloud_file/    # 网盘文件
  share_temp/    # 分享记录（<token>.txt，含明文提取密码）
  zip_temp/      # 打包下载的中转目录（响应结束即清理）
  logs/          # 按天切分的日志 app-YYYY-MM-DD.log（前端「日志」页读取）
  users.json     # 平台账号（Bearer 认证，bcrypt 密码哈希）
  pwd.config.txt # 可选：网盘明文管理密码，缺省时用代码默认值 123456
```

## 本地运行

```powershell
# 1. 安装依赖（首次）
composer install

# 2. 启动后端（内置服务器，端口 5200）
.\boot.ps1
# 等价于：php -S localhost:5200 -t public -d upload_max_filesize=50M -d post_max_size=50M

# 3. 启动前端（另开终端）
cd web
pnpm dev
# 打开 http://localhost:5173
```

> PHP 内置服务器以 `-t public` 启动时，`/api/clouddrive.php` 等不存在的文件路径会自动回退到 `public/index.php`，由 Slim 路由接手，无需 router 脚本。

## 路由列表

| 方法 | 路径 | 说明 |
|---|---|---|
| GET/POST | `/api/php-demo?action=…`（别名 `/api/php-demo.php`） | PHP 演示模块（array_ops 等 9 个 action） |
| GET/POST | `/api/doctrine?action=…`（别名 `/api/doctrine.php`） | Doctrine 演示模块（init 等 12 个 action） |
| GET/POST | `/api/clouddrive?action=…`（别名 `/api/clouddrive.php`） | 网盘模块（login/check/list_dir/upload 等 18 个 action；login/logout/check/stream 免登录，其余需登录） |
| GET | `/api/logs/days` | 可查日志日期列表（需 Bearer Token） |
| GET | `/api/logs?date=YYYY-MM-DD&offset=&limit=` | 按天分页读取日志（需 Bearer Token） |
| GET | `/docs` | Swagger UI 文档页 |
| GET | `/docs/openapi.json` | OpenAPI 3.0 文档（每次访问实时生成） |
| ANY | `/?module=php\|doctrine\|clouddrive` | 旧版直连入口（行为与迁移前一致，`web` 旧代理依赖它） |
| ANY | 其他任意路径 | JSON 兜底 `{"code":404,"message":"未知模块","data":null}` |

## Swagger UI 使用说明

浏览器打开 <http://localhost:5200/docs>：

- 文档由 [swagger-php](https://github.com/zircote/swagger-php) 扫描 `app/` 与 `src/` 的 PHP Attributes **在请求时实时生成**（`/docs/openapi.json`），改完代码注解刷新页面即生效，无需构建步骤。
- 页面右上角 **Authorize** 支持填入 PHPSESSID Cookie（`withCredentials: true` 已开启），即可在文档页内直接调试需登录的接口。

## Redis

认证与热点数据使用 Redis（本机服务 127.0.0.1:6379，Windows 服务方式常驻）。客户端为 [predis/predis](https://github.com/predis/predis) ^3.6（纯 PHP 实现，MIT，无需安装 phpredis 扩展）。

连接配置在 `src/Service/Redis/RedisClient.php`：默认指向本机，支持环境变量覆盖 `REDIS_HOST` / `REDIS_PORT` / `REDIS_PASSWORD` / `REDIS_DATABASE`（生产环境勿用代码内默认密码）。

三个实践场景：

| 场景 | 键设计 | 说明 |
|---|---|---|
| 登录 Token 存储 | `auth:token:{sha256(token)}` → user_id，SETEX 7 天 | 过期靠 Redis 原生 TTL；键存 token 摘要而非原文；登出即 DEL |
| 登录/注册限流 | `ratelimit:{ip}`，INCR + 首次 EXPIRE 60s | 同 IP 60 秒 10 次，超出返回 429 |
| Doctrine stats 缓存 | `doctrine:stats`，SETEX 300s | cache-aside：读 miss 则查库回填；写操作（init/创建/更新/删除）立即 DEL 失效 |

Redis 故障降级语义：stats 缓存读写失败自动降级直查数据库（只读接口不因缓存故障不可用）；限流中间件故障会阻断请求（fail fast，安全控制不静默降级）。

## 日志系统

基于 PSR-3 + Monolog。同一条日志同时写两处：`php://stderr`（`php -S` 启动终端的控制台，便于本地观察）与按天切分的文件 `storage/logs/app-YYYY-MM-DD.log`（前端「日志」页读取，文件锁防并发写串行化竞争）：

```
[2026-09-06 21:26:00] app.INFO: GET /api/clouddrive 200 {"rid":"44b19f38","ms":7.2,"ip":"127.0.0.1"}
[2026-09-06 21:26:00] app.WARNING: biz_exception {"rid":"3d4e5623","req":"POST /api/clouddrive","code":400,"exception":"Service\\ApiException","at":".../CloudDriveService.php:175","msg":"密码错误"}
[2026-09-06 21:26:00] app.ERROR: uncaught_exception {"rid":"61e8a4cd","code":500,"exception":"...","at":"...","trace":["#0 ..."],"msg":"..."}
```

- **访问日志**（`INFO`，≥500 时 `ERROR`）：每个请求一行，方法/路径/状态码/耗时/请求ID/IP。
- **异常日志**：业务 4xx（如密码错误、未登录）→ `WARNING`；未捕获 5xx → `ERROR` 并带 4 帧堆栈。
- **请求 ID（rid）**：异常日志与访问日志通过 rid 关联，方便定位同一请求。
- 敏感信息不进日志：不记录请求体/密码；日志器在 `Service/Log/LoggerFactory.php`，访问日志在 `Service/Middleware/RequestLoggerMiddleware.php`。
- **按天落盘**：文件名 `storage/logs/app-YYYY-MM-DD.log`，日期在 `LoggerFactory::create()` 时确定（`php -S` 每请求重新 include 入口，切天即切文件）；Monolog 不建目录，工厂内自行 `mkdir`。
- **文件查询接口**（`Service/Log/LogStore.php`，经 `ApiAuthMiddleware` 鉴权）：`/api/logs/days` 只取 `filesize()` 不读内容；`/api/logs` 单次遍历文件，统计总行数的同时只收集 `[offset, offset+limit)` 窗口，内存占用与文件总行数无关（`limit` 上限 2000）。
- **前端「日志」页**（`web/src/views/LogsView.vue`，路由 `/logs`）：按天切换、级别 / 关键词过滤、行号分页、跳到最新、5 秒自动刷新。日志含内部路径与异常信息，故未登录不可访问。

## Web 服务器部署（重写规则）

所有请求（不存在的文件）都应转发到 `public/index.php`。

### nginx

```nginx
server {
    listen 80;
    server_name localhost;
    root /path/to/slim-lab/public;
    index index.php;

    location / {
        # 文件不存在时全部回退到统一入口（含 /api/*.php、/docs）
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Apache（public/.htaccess 已内置）

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```

注意：Apache 部署时 `AllowOverride All` 需开启，且不要把项目根目录直接作为站点根，应指向 `public/`。

## 与迁移前的差异说明

- 接口行为逐分支一致（58 个新旧后端用例比对通过，详见交付报告）；唯一有意差异：`zip` 对不存在目录从「HTTP 200 + PHP Fatal HTML」改为「HTTP 500 + 统一 JSON 信封」。
- 修复了旧版 `batch_delete` 空列表项会误删整个存储目录的缺陷（现跳过存储根目录自身）。
- `create_users` 改用 [Faker](https://github.com/fakerphp/faker) 随机生成中文姓名 + 唯一邮箱（旧版固定插入"张三/李四"，重复点击必撞 email 唯一约束而报错），可任意重复点击。
- 迁移前的旧入口文件、`api/` 目录与 `src/bootstrap.php`（其 `createEntityManager()` 已搬到 `Service/EntityManagerFactory.php`）已删除；如需旧版行为对照，见 Git 历史 `8622fe9`。
