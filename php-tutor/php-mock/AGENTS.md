# AGENTS.md

本文件为 AI 编码代理提供项目上下文与规范，修改代码前请先阅读并遵守。

## 项目概述

- **项目性质**：面向 Java / Spring Boot 开发者的 PHP 8.x 
- **技术栈**：PHP 8.5（无框架）、Composer、MySQL 8.4、PDO
 
- **注释约定**：关键代码配中文注释，并标注 Java 生态对照概念（Composer ↔ Maven、PDO ↔ JDBC 等）

## 环境信息（Windows 11 + PowerShell）

| 组件 | 说明 |
| --- | --- |
| PHP 8.5 | `F:\programs\php-8.5.10-nts-Win32-vs17-x64\php.exe`（不在 PATH，用绝对路径或加到环境变量） |
| Composer | 全局可用，已配置阿里云镜像 |
| MySQL | 127.0.0.1:3306，用户 `root`，密码 `123456` |

> ⚠️ PHP 8.5 本机**未加载 `mbstring` 扩展**（`php -m` 中无 mbstring）。
> 处理中文长度时用 `App\Helpers\Html::charLen()`（内部用 `preg_match_all('/./us')`）；
> 处理字符串截断时用 `App\Helpers\Html::truncate()`；**不要直接调 `mb_strlen` / `mb_substr`**。

## 常用命令

```powershell
# 用绝对路径调用 PHP（本环境 PATH 里没有 php）
$php = 'F:\programs\php-8.5.10-nts-Win32-vs17-x64\php.exe'

& $php -l <文件>                              # 语法检查
& $php -m                                     # 查看已加载扩展
& $php -S localhost:8000 -t public            # 启动开发服务器（端口可改）
.\run_learn.ps1                               # 等价启动脚本（5860 端口）

composer install                              # 安装依赖
composer dump-autoload --optimize             # 改完命名空间后刷新自动加载

mysql -uroot -p < docs\sql\schema.sql         # 初始化数据库
```
 

## 代码规范

- 所有 PHP 文件开头声明 `declare(strict_types=1);`
- 使用 PHP 8.x 现代特性：属性类型声明、构造器属性提升、`readonly`、`match`、命名参数
- 遵循 PSR-4 自动加载（命名空间 `App\` 对应 `app/` 目录）与 PSR-12 风格
- **禁止残留 `var_dump` / `print_r` 调试代码**
- 注释用中文，说明业务意图与用到的 PHP 特性，并适当与 Java 概念类比
- 控制器不直接 `echo`，统一返回 `Response::view()` / `Response::json()` / `Response::redirect()`

## 数据库规范

- 一律使用 PDO + 预处理语句，严禁字符串拼接 SQL
- 数据库连接配置从 `config/` 读取，禁止硬编码密码
- 表结构变更必须同步更新 `docs/sql/schema.sql`
- 表规范：自增主键 `id`，包含 `created_at`、`updated_at` 字段（MySQL 用 `DEFAULT CURRENT_TIMESTAMP`）
- 字符集 `utf8mb4`，排序规则 `utf8mb4_0900_ai_ci`（MySQL 8 默认）
- `Database::update()` / `Database::delete()` 强制要求 WHERE，禁止全表更新/删除

## 路由与中间件

- 路由表集中在 `app/routes.php`，用 `Router::get()` / `Router::post()` 注册
- URL 占位符用 `{id}` 形式，Router 内部转正则命名捕获组
- 中间件是"闭包工厂"，签名 `callable(callable $action): callable`：
  ```php
  Router::post('/articles', [ArticleController::class, 'store'])
        ->middleware(CsrfProtection::check())      // POST 必须带 token
        ->middleware(Authenticate::guard());        // 需登录
  ```
- **PHP 8.5 注意**：`callable` 类型不再接受 `[Class, 'method']` 数组形式，
  Router::toClosure() 已做兼容适配（自动 new 实例后调实例方法）

## 安全要求

- 密码存储用 `password_hash($pw, PASSWORD_BCRYPT)`，比对用 `password_verify()`
- 输出到 HTML 前用 `App\Helpers\Html::e()`（= `htmlspecialchars(ENT_QUOTES)`）转义，防 XSS
- 表单提交必须带 CSRF token（隐藏域 `_token`），中间件用 `hash_equals()` 常量时间比对
- 会话固定防护：登录成功后 `session_regenerate_id(true)`，旧 session id 立即失效
- 错误消息模糊化：登录失败统一返回"账号或密码错误"，不区分账号是否存在
- 输入白名单：`BaseModel::$fillable` 限制可写字段，防 mass-assignment

## 变更要求

- 每次改动后运行 `php -l` 语法检查；涉及页面改动时启动服务器实际验证
- 保持改动最小化，不做需求之外的"顺手重构"
- 新增 Composer 依赖前先说明理由，优先选择官方维护、下载量高的包
- 不修改 `vendor/` 目录与 `php.ini` 等全局环境配置
- **禁止使用 `mb_*` 系列函数**（本机 PHP 8.5 未加载 mbstring），用 `Html::charLen()` / `Html::truncate()` 替代
 