# 简单图床(前后端分离)

- **后端**:PHP 8.2 + [Slim Framework 4](https://www.slimframework.com/) + PHP-DI + Monolog + [swagger-php](https://github.com/zircote/swagger-php),纯 JSON API,不含任何 HTML 渲染(接口文档页除外)
- **前端**:Vue 3(Composition API + `<script setup>`)+ TypeScript + Vite + Element Plus + Tailwind CSS 4,位于 `web/`
- 前后端**完全独立部署**:跨域请求由 PHP 后端通过 CORS 放行(`Access-Control-Allow-Origin: *`),**不使用 Vite proxy**

## 环境要求

- PHP >= 8.2(需启用 `fileinfo` 扩展,默认开启)
- Composer 2.x
- Node.js 20.19+ / 22.12+(Vite 8 要求)
- npm 10+

## 后端

### 安装与配置

```bash
composer install
cp .env.example .env   # Windows PowerShell: Copy-Item .env.example .env
```

| 变量 | 说明 | 默认值 |
|---|---|---|
| `APP_NAME` | 应用名称(响应 message 前缀) | 简单图床 |
| `APP_DEBUG` | 调试模式,开启后错误响应含异常原文、日志级别为 DEBUG | true |
| `UPLOAD_MAX_SIZE` | 单文件上传上限(字节) | 10485760(10 MB) |
| `STORAGE_PATH` | 图片存储目录(相对项目根目录) | storage/uploads |
| `LOG_VIEW_ALLOW_ANY` | `/api/logs` 是否允许非本机访问(内网/生产调试置 1) | 0(仅 127.0.0.1 / ::1) |

> 本项目没有数据库,文件系统即数据层(图片 + 逐文件 `.json` 元数据)。

### 启动

```bash
composer start
# 等价于:php -S localhost:8080 -t public public/index.php
```

Windows 下也可以直接运行 `runApp.ps1`。API 基地址 <http://localhost:8080>。

日志写入 `storage/logs/`:`app-YYYY-MM-DD.log`(应用日志,按 `APP_DEBUG` 决定级别)+ `error-YYYY-MM-DD.log`(仅 ERROR 及以上),
**按天分文件**,已 gitignore。

访问日志由 `AccessLogMiddleware` 统一记录(挂在中间件最外层,响应由谁生成都能拿到最终状态码):
每个请求一条,含方法、路径、状态码、耗时、来源 IP 与 User-Agent;
级别按状态码分档 —— 2xx/3xx 记 `info`,4xx 记 `warning`(不进 error 通道),5xx 记 `error`(同时进 error 通道)。

日志可在前端「日志」页直接查看(见下),对应 `GET /api/logs` 与 `GET /api/logs/{name}`。

## 前端

```bash
cd web
npm install
npm run dev      # http://localhost:5173
npm run build    # 类型检查(vue-tsc)+ 生产构建到 web/dist
```

前端通过 `VITE_API_BASE_URL` 指定 API 地址,不在代码中硬编码:

- `.env.development` / `.env.production` 中均已配置为 `http://localhost:8080`
- 生产部署时把 `.env.production` 改为线上 API 域名(如 `https://api.example.com`)后重新构建
- **Vite 不配置 server.proxy**,跨域完全依赖后端 CORS 中间件(见 `src/Middleware/CorsMiddleware.php`)

页面在顶栏用 `el-radio-group` 切换**图库 / 日志**两个视图,没有引入 vue-router:
图库是上传与画廊,日志页左侧是按天分文件的列表,右侧是选中文件的末尾记录,
支持按级别过滤、按需调整条数(100 / 200 / 500 / 1000)、每 5 秒自动刷新,
以及展开行查看该条日志的上下文 JSON(异常堆栈等)。
视图用 `v-if` / `v-else` 切换,切走后图库视图会销毁,顺带清掉它的 Ctrl+V 粘贴监听。

## 目录结构

```
playground/
├── public/index.php          # Slim 入口 + 路由注册(唯一对外目录)
├── config/dependencies.php   # PHP-DI 容器定义(Settings/Logger/ImageService/LogService 等)
├── src/
│   ├── Controller/           # ImageController、LogController(日志查看)、OpenApiDocsController
│   ├── Service/              # ImageService、UploadedFile DTO、LogService(日志列表与末尾读取)、OpenApiSpecService
│   ├── Middleware/           # CorsMiddleware、CsrfMiddleware、AccessLogMiddleware(访问日志)、LogAccessGuard(日志接口本机限制)、JsonErrorRenderer
│   ├── Dto/                  # Image 只读 DTO
│   ├── Exception/            # ApiException 基类及其子类(ImageHostException / LogNotFoundException 等)
│   ├── Settings.php          # 从 .env 读取的只读配置
│   ├── OpenApiDefinition.php # OpenAPI 的 API 级定义(信息/服务地址/标签/安全方案/包络 schema)
│   └── .env / .env.example
├── storage/                  # uploads/(图片)、logs/(日志)、openapi.json,均不入 Git
├── web/                      # Vue 3 前端(create-vue 脚手架)
│   ├── src/api/              # http.ts(axios 实例 + ApiError)、images.ts、logs.ts(日志接口)
│   ├── src/types/            # ApiEnvelope / ImageItem、LogFileItem / LogRecord 类型
│   ├── src/utils/            # format.ts(文件大小/时间格式化、复制)
│   ├── src/views/            # GalleryView.vue(画廊页)、LogsView.vue(日志查看页)
│   └── .env.development / .env.production
└── composer.json
```

## API 路由

统一响应格式:`{ "success": bool, "message"?: string, "data"?: T }`

| 方法 | 路径 | 说明 |
|---|---|---|
| GET | `/api/health` | 健康检查 |
| GET | `/api/images` | 图片列表,`data: ImageItem[]` |
| POST | `/api/images` | 上传图片(`multipart/form-data`,字段 `file`,支持多文件) |
| DELETE | `/api/images/{name}` | 删除图片 |
| GET | `/i/{name}` | 图片外链(inline,长缓存) |
| GET | `/download/{name}` | 附件方式下载 |
| GET | `/api/logs` | 日志文件列表(按天分文件,按修改时间倒序) |
| GET | `/api/logs/{name}` | 查看单个日志文件末尾,`?limit=`(1–1000,默认 200)与 `?level=`(如 `ERROR`)过滤 |
| GET | `/docs` | 接口文档页(Swagger UI) |
| GET | `/openapi.json` | OpenAPI 3.0 规范,`?refresh=1` 强制忽略缓存重新生成 |

`/api/logs*` 两个端点由路由级 `LogAccessGuard` 限制为**仅本机来源**(127.0.0.1 / ::1)访问,
其他来源返回 403;需要内网或生产环境调试时把 `.env` 的 `LOG_VIEW_ALLOW_ANY` 置为 1。

错误码:400 参数/文件非法,404 不存在,403 缺安全头 / 非本机访问日志接口,413 超限,500 服务器错误(生产环境不泄露内部细节,详细堆栈写入 Monolog)。

> **已知问题**:业务异常(`ImageNotFoundException`、`InvalidUploadException`、`LogNotFoundException`)没有可用的 HTTP 状态码通道,
> 目前统一返回 500 而非语义上的 404 / 400 / 413。根因是 Slim 4 的错误处理器只在异常实现 `Slim\Exception\HttpException`
> 时才采用 `getCode()`,而该类强制要求构造时传入 `ServerRequestInterface`(业务层抛出点拿不到);
> 让业务异常继承它会连带把未传码的异常变成非法状态码 0,所以 `ApiException` 继承 `RuntimeException` 并把约束写进了它的文档注释。
> `/docs` 中的规范按当前实际行为标注。

## 接口文档

依赖 [`zircote/swagger-php`](https://github.com/zircote/swagger-php),基于 PHP 8 属性注解生成 OpenAPI 3.0.3 规范:

- API 级定义(信息、服务地址、标签、安全方案、`ApiEnvelope` 包络)在 `src/OpenApiDefinition.php`
- 端点注解写在各控制器方法上(`ImageController`、`LogController`、`OpenApiDocsController`),与 `public/index.php` 的路由一一对应——**改路由必须同步改注解**
- `src/Dto/Image.php` 的 `Image` 对象由 `OA\Schema` + 属性提升参数上的 `OA\Property` 登记为 `Image` schema
- 标签共 4 个:系统 / 图片 / 文档 / 日志

> PHP 8 属性只接受**常量表达式**,所以注解里不能写字符串拼接、也不能调用静态方法构造参数对象。
> 公共参数(如 CSRF 头)需要**逐个内联**到每个端点,重复是刻意的;见 `ImageController` 类注释。

规范首次请求时扫描 `src/` 生成并缓存到 `storage/openapi.json`(已 gitignore),之后命中缓存直接返回。
缓存按修改时间失效:`src/` 下任一文件变更或 `vendor/composer/installed.json` 变更即自动重建,因此注解改完无需任何手工步骤;
需要强制刷新时访问 `/openapi.json?refresh=1`。

`public/docs.html` 是 Swagger UI 页面(资源来自 `unpkg.com` 的 `swagger-ui-dist@5.32.15`,需联网)。
页面通过 `requestInterceptor` 为非 GET 的 `/api/*` 请求统一注入 `X-Requested-With: XMLHttpRequest`,
因此可以直接在页面上执行 POST / DELETE。

> Swagger UI 的配置键是 `dom_id`(下划线),不是 `domId`;写错时值变为 `undefined`,
> 初始化会静默跳过渲染且不打任何报错,表现为页面只剩工具栏。

## 安全说明

- **CORS**:后端 `CorsMiddleware` 对所有来源放行(`Access-Control-Allow-Origin: *`),OPTIONS 预检直接 204 短路
- **CSRF**:无 cookie 登录态,采用无状态方案——非 GET 的 `/api/*` 请求必须携带 `X-Requested-With: XMLHttpRequest` 请求头(前端 axios 实例统一注入);未来接入登录后应升级为 token 方案
- 上传校验:扩展名白名单(jpg/jpeg/png/gif/webp/bmp)+ `finfo` 内容校验 + `getimagesize()`,存储名随机 `bin2hex(random_bytes(16))`
- 刻意不支持 svg:内联渲染时可执行 JS,存在存储型 XSS 风险
- 文件读取统一经 `basename()` + `realpath()` 前缀校验,防止目录穿越
- **日志接口访问控制**:`/api/logs*` 校验 `REMOTE_ADDR` 是否为 127.0.0.1 / ::1 / localhost,非本机直接 403(`LogAccessGuard`)。
  依据是日志含绝对路径、完整堆栈与访问 IP,比业务接口敏感;本项目无登录态,按来源 IP 划界是最轻量的做法
- **日志内容按不可信文本处理**:前端 `LogsView.vue` 一律用 `{{ }}` 文本插值渲染,不使用 `v-html`,
  因此日志里出现 `<script>` / `onerror` 之类内容只会被转义显示,不会执行(已实测)
- 日志接口只做读取,不提供任何删除或截断入口;日志清理需另行处理
- 存储目录在 Web 根目录之外,无法被直接访问或执行 PHP
- 生产环境建议:nginx 托管 `public/`(API + 图片外链)与 `web/dist/`(静态资源),`APP_DEBUG=false`;
  若需让日志接口在内网可用,把 `LOG_VIEW_ALLOW_ANY` 置 1 并在网关层限制来源网段
