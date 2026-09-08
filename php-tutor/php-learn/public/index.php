<?php

/**
 * ============================================================
 * 唯一的 Web 入口（Front Controller 模式）— Slim 4 版
 * ============================================================
 *
 * 类比 Java / Spring Boot：
 *   - 这里相当于 DispatcherServlet（所有请求都先经过它）；
 *   - Spring Boot 的 SpringApplication.run() 里已经做了容器初始化、
 *     中间件注册、路由扫描，这里通过 bootstrap.php 完成。
 *
 * 为什么静态资源（CSS/JS）也能被访问？
 *   PHP 内置服务器 `php -S localhost:8080 -t public` 的规则：
 *   - public 下真实存在的文件 → 直接返回（走不到 index.php）；
 *   - 其他请求 → 交给 index.php 处理。
 *   所以 assets/ 里的样式表会被直接读出来。
 *
 * 生产环境用 Apache/Nginx 时，需要写一条 rewrite 规则把所有请求指到 index.php。
 */
declare(strict_types=1);

// 引导：构建 Slim App + 容器 + 中间件 + 路由，返回 App 对象
$app = require dirname(__DIR__) . '/app/bootstrap.php';

// 启动应用（Slim 负责处理请求并输出响应）
$app->run();