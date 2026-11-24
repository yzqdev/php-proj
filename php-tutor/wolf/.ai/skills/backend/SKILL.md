---
name: backend
description: 后端 API 开发指南(Slim 4 + PHP-DI)
---

# 后端开发指南

## Overview

项目使用 Slim 4 作为 HTTP 框架、PHP-DI 作为依赖注入容器。后端开发的主体工作是:在 `routes/web.php` 注册路由,在 `src/Controller/` 实现控制器,通过容器获得日志等服务。

## Architecture

- 入口 `public/index.php` 负责构建容器、创建 App、加载路由、注册中间件,应用生命周期从这里开始。
- `config/container.php` 返回容器定义数组,是所有服务注册的位置;`config/settings.php` 提供应用配置,被容器定义引用。
- 路由集中定义在 `routes/web.php`,该文件返回一个接收 `Slim\App` 的闭包,逐条注册路由。

## Project Structure

```text
public/index.php            入口
config/settings.php         应用配置
config/container.php        容器定义
routes/web.php              路由注册
src/Controller/             控制器(PSR-4: Yzqde\Wolf\)
```

## Design

控制器保持轻量:接收 PSR-7 Request,完成参数处理并返回 Response。当前项目逻辑简单,尚无独立 Service 层,业务逻辑可直接写在控制器方法中;后续逻辑复杂时再考虑拆分。

返回 JSON 的常见写法参考 `HelloController::hello`:使用 `json_encode(..., JSON_THROW_ON_ERROR)` 编码,写入 Response body 后设置 `Content-Type: application/json` 头。

服务统一在 `config/container.php` 中注册。控制器需要某个服务(如 `Psr\Log\LoggerInterface`)时,通过构造函数类型标注声明即可,PHP-DI 的 autowiring 会自动完成装配;Slim 在解析路由处理器时也会通过容器实例化控制器类。

## Conventions

- 所有 PHP 文件以 `declare(strict_types=1)` 开头
- 类声明为 `final`,命名空间前缀 `Yzqde\Wolf\`
- 控制器放在 `src/Controller/`,类名以 `Controller` 结尾
- 路由统一在 `routes/web.php` 注册

## Development

新增接口的通常步骤:在 `src/Controller/` 实现(或复用)控制器方法 → 在 `routes/web.php` 注册路由 → 运行 `vendor/bin/phpstan analyse` 做静态检查。

Doctrine、Predis、swagger-php 已随 composer 安装,但代码中尚未接入。引入数据访问或 Redis 时,通常先在 `config/container.php` 补充相应服务的容器定义,再在业务代码中通过注入使用。

## References

- 运行与检查命令见 `../../docs/development.md`
