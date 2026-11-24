# 新建 PHP 项目

在空目录或新目录中创建一个可运行的 PHP 项目。

## 开始前

1. 读取 `AGENTS.md`、`.agents/docs/environment.md`。
2. 确认目标 PHP 版本与部署环境；本机为 PHP 8.5.10 + Composer 2.11.2。
3. 明确项目类型：Laravel 全栈、Slim 轻量 API、Symfony、原生 + Composer、CLI 工具。

## 步骤

1. **选型**：按 `.agents/docs/framework-comparison.md` 的决策维度选择框架；只有 API 时优先 Slim，业务系统优先 Laravel/Symfony，CLI 用 Symfony Console。
2. **初始化**：
   - Laravel：按官方文档创建项目，确认骨架版本与 PHP 版本匹配。
   - Slim：`composer init` 后安装 `slim/slim slim/psr7 php-di/php-di monolog/monolog`。
   - 原生：建立 `public/index.php`、`src/`、`config/`，配置 PSR-4。
3. **目录结构**：参考 `.agents/skills/slim-api/SKILL.md` 或 `.agents/skills/laravel/SKILL.md` 的结构，按项目规模调整，不强行套用。
4. **配置**：
   - `declare(strict_types=1)`。
   - `.env.example` 与 `.gitignore`。
   - `composer.json` 增加 `scripts`：`test`、`analyse`、`cs`、`check`。
   - `config/platform.php` 固定部署 PHP 版本。
5. **质量工具**：安装 PHPStan、PHP-CS-Fixer、PHPUnit（或 Pest），写入最小配置并能运行。
6. **健康检查**：提供 `/health` 或对应命令，验证启动成功。
7. **文档**：在项目 README 写清启动方式、测试方式、目录职责。

## 完成后

- 运行 `composer validate`、`composer check`。
- 用 `php -S 127.0.0.1:8000 -t public` 或框架命令启动验证。
- 按 `.agents/skills/security/SKILL.md` 的提交前清单自检。
- 更新 `.agents/memory/worklog.md`。

## 参考

- `.agents/docs/framework-comparison.md`
- `.agents/docs/ecosystem-map.md`
- `.agents/skills/composer/SKILL.md`
- `.agents/skills/quality-testing/SKILL.md`
- `.agents/skills/security/SKILL.md`
