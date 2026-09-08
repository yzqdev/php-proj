# Douyin Spider API

基于 Slim 4 + PHP-DI + Doctrine ORM 3 的前后端分离后端 API 系统。

## 技术栈

- **PHP** ≥ 8.2（strict_types）
- **Slim 4** — 路由与中间件
- **PHP-DI 7** — 依赖注入容器
- **Doctrine ORM 3** — ORM 数据持久化
- **Firebase JWT** — Token 签发与验证
- **Monolog** — 日志
- **Symfony Console** — CLI 命令
- **vlucas/phpdotenv** — 环境变量管理

## 快速开始

### 1. 安装依赖

```bash
composer install
```

### 2. 配置环境变量

```bash
# Windows PowerShell
Copy-Item .env.example .env

# Linux / macOS
cp .env.example .env
```

编辑 `.env`，填写真实的数据库连接信息和 `JWT_SECRET`：

```env
DB_HOST=127.0.0.1
DB_DATABASE=douyin_spider
DB_USERNAME=root
DB_PASSWORD=your_password
JWT_SECRET=your-random-secret-key-min-32-chars
```

### 3. 创建数据库并执行迁移

```bash
# 创建数据库（需先连接 MySQL）
php cli.php dbal:run-sql "CREATE DATABASE douyin_spider CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

# 执行迁移生成表结构
vendor/bin/doctrine-migrations migrate
```

### 4. 启动开发服务器

```bash
php -S localhost:8080 public/index.php
```

---

## API 接口

### 认证接口（公开）

**注册**
```http
POST /api/auth/register
Content-Type: application/json

{
  "username": "testuser",
  "email": "test@example.com",
  "password": "123456"
}
```

**登录**
```http
POST /api/auth/login
Content-Type: application/json

{
  "username": "testuser",
  "password": "123456"
}
```
响应：
```json
{
  "code": 0,
  "message": "登录成功",
  "data": {
    "token": "eyJ0eXAiOiJKV1Qi...",
    "refresh_token": "eyJ0eXAiOiJKV1Qi...",
    "expires_in": 3600
  }
}
```

**刷新 Token**
```http
POST /api/auth/refresh
Content-Type: application/json

{
  "refresh_token": "eyJ0eXAiOiJKV1Qi..."
}
```

### 文章接口（需登录）

所有请求头需携带：`Authorization: Bearer <token>`

| 方法 | 路径 | 说明 |
|---|---|---|
| `GET` | `/api/articles?page=1&limit=20` | 分页列表 |
| `GET` | `/api/articles/{id}` | 文章详情（浏览量+1） |
| `POST` | `/api/articles` | 创建文章（editor/admin） |
| `PUT` | `/api/articles/{id}` | 更新文章（editor/admin） |
| `DELETE` | `/api/articles/{id}` | 删除文章（editor/admin） |

**创建文章示例**
```http
POST /api/articles
Authorization: Bearer eyJ0eXAiOiJKV1Qi...
Content-Type: application/json

{
  "title": "文章标题",
  "content": "文章内容",
  "summary": "摘要（可选）"
}
```

---

## 统一响应格式

**成功：**
```json
{
  "code": 0,
  "message": "success",
  "data": { ... }
}
```

**错误：**
```json
{
  "code": -1,
  "message": "错误描述",
  "data": null
}
```

| code | HTTP 状态 | 含义 |
|---|---|---|
| 0 | 200/201 | 成功 |
| -1 | 400 | 请求参数错误 |
| -2 | 401 | 未授权（Token 无效/过期） |
| -3 | 403 | 权限不足 |
| -4 | 404 | 资源不存在 |
| -99 | 500 | 服务器内部错误 |

---

## 角色权限

| 角色 | 值 | 权限 |
|---|---|---|
| 普通用户 | `user` | 浏览文章 |
| 编辑 | `editor` | 浏览 + 创建/编辑/删除文章 |
| 管理员 | `admin` | 全部权限 |

---

## 目录结构

```
src/
├── Controller/     # 请求处理，参数校验，调用 Service
├── Service/        # 业务逻辑编排
├── Repository/     # 数据访问（Doctrine EntityRepository）
├── Model/          # Doctrine 实体（ORM 注解）
├── DTO/            # 数据传输对象（readonly class）
├── Enum/           # PHP 枚举
├── Exception/      # 业务异常
└── Middleware/     # 全局中间件

config/
└── dependencies.php  # PHP-DI 容器配置

routes/
└── api.php         # 路由定义

public/
└── index.php       # Web 入口
```

---

## 数据库迁移

```bash
# 查看迁移状态
vendor/bin/doctrine-migrations migrations:status

# 执行最新迁移
vendor/bin/doctrine-migrations migrate

# 回滚到上一版本
vendor/bin/doctrine-migrations migrate --down
```

---

## 开发规范

- 所有 PHP 文件启用 `declare(strict_types=1);`
- 遵循 PSR-12 编码风格
- 分层依赖：Controller → Service → Repository → Model（单向）
- 密码使用 `password_hash(PASSWORD_BCRYPT)` 存储
- 敏感信息通过环境变量注入，禁止硬编码
