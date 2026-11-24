# Project Overview

wolf 是一个基于 Slim 4 的 PHP 后端项目,目前处于初始化阶段:已搭建 HTTP 应用骨架(入口、容器、路由、配置),并提供一个 `/hello` 示例接口返回 JSON。

## 强制规则

AI Agent 开始处理项目任务时，需要先读取 `AGENTS.md`。读取完成后，在首次回复中输出：“我已经读取了AGENTS.md,并且会遵守规则”。

# Technology Stack

- PHP 8.1+ / Composer
- Slim 4(slim/slim + slim/psr7):HTTP 框架与 PSR-7 实现
- PHP-DI(php-di/php-di):依赖注入容器,依赖自动装配
- Monolog:日志,写入 stderr
- zircote/swagger-php:OpenAPI 文档生成(已随 composer 安装,代码中尚未接入)
- Doctrine ORM / DBAL / Migrations:数据访问(已安装,尚未接入)
- Predis:Redis 客户端(已安装,尚未接入)
- PHPStan:静态分析(require-dev,level max)

# Architecture

应用入口为 `public/index.php`,流程如下:

1. `DI\ContainerBuilder` 加载 `config/container.php` 构建容器;
2. `AppFactory::setContainer` 后创建 Slim App;
3. 加载 `routes/web.php`,由其中定义的闭包在 App 上注册路由;
4. 添加路由中间件与错误处理中间件后运行。

控制器放在 `src/Controller/`,方法通过类型标注接收 PSR-7 的 Request/Response;返回 JSON 时写入 body 并设置 `Content-Type: application/json`。日志等公共服务以接口(如 `Psr\Log\LoggerInterface`)注册在容器中,通过构造函数注入使用。

# Directory Structure

```text
public/          Web 入口(index.php)
config/          应用配置与容器定义(settings.php、container.php)
routes/          路由定义(web.php)
src/             业务源码,PSR-4 前缀 Yzqde\Wolf\
  └── Controller/
vendor/          Composer 依赖(不入库)
```

# Development Philosophy

- 源码统一使用 `declare(strict_types=1)`,类声明为 `final`
- 依赖通过 PHP-DI 容器管理,面向接口注入
- 新增接口时参考现有 `HelloController` 的写法,保持项目内部一致
- 文档与注释使用中文,技术名称保持官方英文

# AI Resources

- `.ai/ai_guide.md`:AI 工作区与 Skill 的生成及使用规范
- `.ai/docs/`:项目知识(开发环境、常用命令、配置说明)
- `.ai/skills/`:按开发场景组织的开发指南,入口见 `.ai/skills/INDEX.md`
- `.ai/commands/`:具体任务的可复用指令

# Development Workflow

1. 安装依赖:`composer install`
2. 本地运行:`php -S 0.0.0.0:8080 -t public`,访问 `http://localhost:8080/hello`
3. 静态分析:`vendor/bin/phpstan analyse`
