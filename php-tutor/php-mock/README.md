# 模拟数据 API 平台（Mock Data API Platform）

为前端 / 移动端开发者提供假数据的 API 平台：通过管理接口定义数据模板（字段 Schema），
批量生成假数据持久化到 MySQL，再通过公开的 Mock API 对这批数据做完整增删改查，
前端可直接拿它联调整个 CRUD 流程。

- Laravel 12（PHP 8.2+），Sanctum token 认证
- MySQL 8，所有模板数据统一存 `mock_records.data` JSON 列（禁止动态建表）
- fakerphp/faker，本地化 `zh_CN`（生成中文姓名 / 地址等）
- OpenAPI 文档 + Swagger UI（CDN），访问 `/docs`

## 核心概念（三层模型）

```
Project（项目，含 slug 和 api_key）
└── MockResource（资源定义，含字段 Schema JSON）
    └── MockRecord（生成的数据，存 JSON 字段）
```

## 安装

```powershell
# 1. 安装依赖
composer install

# 2. 配置 .env（Windows PowerShell；Linux/Mac 用 cp .env.example .env）
copy .env.example .env
#    修改数据库连接：
#    DB_CONNECTION=mysql
#    DB_HOST=127.0.0.1
#    DB_PORT=3306
#    DB_DATABASE=php_mock_api
#    DB_USERNAME=root
#    DB_PASSWORD=123456
php artisan key:generate

# 3. 先建库（或在 MySQL 中手动 CREATE DATABASE）
php -r "$p=new PDO('mysql:host=127.0.0.1','root','123456');$p->exec('CREATE DATABASE IF NOT EXISTS php_mock_api CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci');"

# 4. 迁移 + 种子（全新数据库无报错一次通过）
php artisan migrate --seed

# 5. 启动
php artisan serve
# 默认 http://127.0.0.1:8000；端口被占用时改端口：php artisan serve --port=8001
```

默认用户：`demo@mock.dev` / `password`，已附带 demo 项目（slug `demo`）、
`users` 资源和 50 条生成好的数据，可直接访问：
`GET http://127.0.0.1:8000/api/v1/mock/demo/users`

## 字段类型（fields Schema）

| type    | 额外参数  | 生成方式                                                     |
| ------- | --------- | ------------------------------------------------------------ |
| name    | -         | faker->name                                                  |
| email   | -         | faker->safeEmail                                             |
| phone   | -         | faker->phoneNumber                                           |
| address | -         | faker->address                                               |
| company | -         | faker->company                                               |
| number  | min, max  | random_int(min, max)，默认 0~100                             |
| enum    | options   | 从 options 中随机取一个                                      |
| bool    | -         | faker->boolean                                               |
| date    | -         | 近两年随机时间，格式 `Y-m-d H:i:s`                           |
| text    | length    | faker->text，默认 200                                        |
| uuid    | -         | faker->uuid                                                  |
| url     | -         | faker->url                                                   |
| image   | w, h      | `https://picsum.photos/seed/{uuid}/{width}x{height}`         |

## API 一览

统一响应：成功 `{"code":0,"message":"success","data":...,"meta":{...}}`；
错误 `{"code":<http状态码>,"message":"..."}`；所有响应头带 `X-Mock-Data: true`。

### 认证（公开）

| 方法 | 路径                | 说明               |
| ---- | ------------------- | ------------------ |
| POST | /api/auth/register  | {name,email,password} → Sanctum token |
| POST | /api/auth/login     | {email,password} → Sanctum token      |

### 管理（Authorization: Bearer <token>）

| 方法   | 路径                                    | 说明                                    |
| ------ | --------------------------------------- | --------------------------------------- |
| GET    | /api/projects                           | 我的项目列表                            |
| POST   | /api/projects                           | {name} 创建项目，自动生成 slug/api_key  |
| GET    | /api/projects/{project}                 | 项目详情（含资源）                      |
| POST   | /api/projects/{project}/resources       | 定义资源并立即生成（count 缺省 20，上限 10000） |
| GET    | /api/resources/{resource}               | 资源详情（schema + 数据量）             |
| POST   | /api/resources/{resource}/generate      | 重新生成（先清空旧数据）                |
| DELETE | /api/resources/{resource}/records       | 清空数据                                |

### 公开 Mock（无需认证，限流 60 次/分钟）

```
GET    /api/v1/mock/{projectSlug}/{resourceName}          列表
GET    /api/v1/mock/{projectSlug}/{resourceName}/{id}     详情
POST   /api/v1/mock/{projectSlug}/{resourceName}          新增（真写库）
PUT    /api/v1/mock/{projectSlug}/{resourceName}/{id}     更新（合并 data）
DELETE /api/v1/mock/{projectSlug}/{resourceName}/{id}     删除
```

列表查询参数：

- `page`、`size`（上限 100）
- `sort`：字段名升序，前缀 `-` 降序，如 `sort=-id`
- `keyword` + `search_in`：对 data JSON 内指定字段做 like 模糊搜索，
  如 `?keyword=张&search_in=nickname,email`

## curl 冒烟脚本

覆盖：登录拿 token → 创建项目 → 定义资源（含生成）→ 匿名访问列表 → 新增一条 →
更新 → 删除 → 重新生成 → 404 格式验证。

```bash
bash scripts/smoke.sh http://127.0.0.1:8001   # 参数是 BASE_URL，默认 8001
```

## 测试

```bash
# 使用 php_mock_api_test 库（见 phpunit.xml），需先创建：
php -r "$p=new PDO('mysql:host=127.0.0.1','root','123456');$p->exec('CREATE DATABASE IF NOT EXISTS php_mock_api_test CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci');"
php artisan test
```

覆盖：FieldResolver 全部类型生成、完整 CRUD 链路、size>100 截断、sort 排序、
鉴权越权、字段 Schema 校验（未知类型 / enum 缺 options）。

## API 文档（Swagger UI）

启动后浏览器打开 <http://127.0.0.1:8000/docs>；
OpenAPI JSON 在 `GET /api/docs`。

## 项目结构

```
app/
├── Http/
│   ├── ApiResponse.php              # 统一响应封装
│   ├── Middleware/AddMockDataHeader.php
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── AuthController.php
│   │   │   ├── ProjectController.php
│   │   │   ├── ResourceController.php
│   │   │   └── Mock/MockApiController.php   # 一个控制器服务所有资源 CRUD
│   │   └── DocsController.php       # OpenAPI JSON
│   └── Requests/                    # FormRequest 校验
├── Models/                          # Project / MockResource / MockRecord
└── Services/
    ├── FieldResolver.php            # 字段 Schema → 假数据（match 表达式）
    └── DataGeneratorService.php     # 批量生成：chunk(500) 分块 insert
```
