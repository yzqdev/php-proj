# 创建一个新的 API 接口

在项目中新增一个 HTTP 接口。

## 步骤

1. 先阅读 `AGENTS.md` 与 `.ai/skills/backend/SKILL.md`,了解项目的路由与控制器组织方式。
2. 参考 `src/Controller/HelloController.php` 的现有写法,在 `src/Controller/` 中实现(或复用)控制器方法:接收 PSR-7 的 Request/Response,返回 JSON 时写入 body 并设置 `Content-Type`。
3. 在 `routes/web.php` 中注册路由。
4. 如需注入日志等服务,通过构造函数类型标注声明,由 PHP-DI 自动装配;手动注册的服务写在 `config/container.php`。
5. 完成后运行 `vendor/bin/phpstan analyse`,确保静态检查通过。
