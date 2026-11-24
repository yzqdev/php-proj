---
name: runtime-deployment
description: PHP 运行时配置、PHP-FPM、Nginx/Apache、OPcache 与生产部署的开发指南
---

# 运行时与部署指南

## Overview

PHP 的运行方式决定性能、并发与运维方式：

```text
开发：php -S（内置服务器）
传统生产：Nginx/Apache + PHP-FPM
常驻进程：Octane / RoadRunner / ReactPHP
容器：PHP-FPM 镜像 + Web Server
```

本机可直接使用 PHP CLI 与内置服务器；Nginx、PHP-FPM、Docker 未安装，相关配置仅作参考。

## 一、php.ini 关键项

```ini
; 错误
display_errors=Off
log_errors=On
error_log=/path/to/php-error.log
error_reporting=E_ALL & ~E_DEPRECATED

; 资源
memory_limit=256M
max_execution_time=30
post_max_size=16M
upload_max_filesize=12M

; 时区
date.timezone=Asia/Shanghai

; 安全
expose_php=Off
allow_url_include=Off
session.cookie_httponly=1
session.cookie_secure=1
session.cookie_samesite=Lax
```

本机 php.ini：`F:\programs\php-8.5.10-nts-Win32-vs17-x64\php.ini`，`memory_limit=128M`。

## 二、本地运行

```powershell
# 内置服务器（开发）
php -S 127.0.0.1:8000 -t public

# 指定路由脚本
php -S 127.0.0.1:8000 -t public public/index.php

# CLI 执行
php bin/console list
```

- 内置服务器单进程，不支持并发，不能上生产。
- 修改代码即时生效，不需要重启。
- 静态文件由内置服务器处理，路由规则与生产 Web Server 可能不同。

## 三、PHP-FPM

```ini
; php-fpm.conf / pool.d/www.conf
[www]
user = www-data
group = www-data
listen = /run/php/php-fpm.sock
listen.owner = www-data
listen.group = www-data

pm = dynamic
pm.max_children = 20
pm.start_servers = 4
pm.min_spare_servers = 2
pm.max_spare_servers = 6
pm.max_requests = 500

request_slowlog_timeout = 5s
slowlog = /var/log/php-fpm/slow.log
```

- `pm.max_children` 根据内存与单进程占用估算：`可用内存 / 单进程内存`。
- `pm.max_requests` 防止内存缓慢增长，达到后回收进程。
- `request_terminate_timeout` 限制单请求最长执行时间。
- 每个 pool 可配置独立用户与日志。

本机 Windows 未安装 PHP-FPM，以上为 Linux 部署参考。

## 四、Nginx

```nginx
server {
    listen 80;
    server_name api.example.com;
    root /var/www/app/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php-fpm.sock;
        fastcgi_read_timeout 30s;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

要点：

- `root` 指向 `public/`，源码、`.env`、`storage` 不在 Web 根目录。
- 禁止访问 `.env`、`.git`、`composer.json`。
- 静态资源加缓存头，PHP 请求不缓存。
- 大文件上传调整 `client_max_body_size`。

## 五、Apache

```apache
<VirtualHost *:80>
    DocumentRoot /var/www/app/public
    <Directory /var/www/app/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

- 使用 `mod_php` 或 `php-fpm` + `mod_proxy_fcgi`。
- `.htaccess` 有性能开销，生产可关闭 `AllowOverride` 并把规则写入主配置。

## 六、Caddy

```caddyfile
api.example.com {
    root * /var/www/app/public
    php_fastcgi unix//run/php/php-fpm.sock
    file_server
}
```

- Caddy 自动 HTTPS，配置简洁。
- `php_fastcgi` 内部处理 `try_files`。

## 七、OPcache

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.revalidate_freq=0
opcache.save_comments=1
opcache.jit=disable
```

- 生产关闭 `validate_timestamps`，部署后 reload FPM。
- `save_comments=1` 保留注解，Doctrine 等依赖注解的库需要。
- JIT 收益因负载而异，先在预发压测。
- CLI 与 FPM 是两套 OPcache，CLI 配置不影响 Web。

## 八、环境变量与配置

```text
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...
DB_HOST=...
DB_PASSWORD=...
```

- 生产配置由容器/进程管理器/系统服务注入，不依赖 `.env` 文件。
- 密钥不写入镜像与代码仓库。
- 配置变更后重启 FPM 或 reload 进程。
- 配置缓存：Laravel `config:cache`，Symfony 编译容器。

## 九、权限

- Web 进程用户只读代码，只写 `storage/`、`cache/`、日志目录。
- 上传目录不可执行。
- `.env` 权限 600，属主为部署用户。
- 不使用 `root` 运行 PHP-FPM。

## 十、Docker（参考）

本机未安装 Docker，以下仅作参考。

```dockerfile
FROM php:8.3-fpm-alpine

RUN docker-php-ext-install pdo_mysql opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --optimize-autoloader
COPY . .

RUN chown -R www-data:www-data storage bootstrap/cache
```

- 多阶段构建减小镜像。
- 生产镜像不装 Xdebug。
- `.dockerignore` 排除 `vendor`、`.git`、`tests`。
- 配置通过环境变量注入。
- 健康检查访问 `/health`。

## 十一、健康检查与监控

```php
// /health
$checks = [
    'db' => fn () => $pdo->query('SELECT 1')->fetchColumn() === 1,
    'cache' => fn () => $cache->set('health', '1', 5),
];
```

- 健康检查区分存活（liveness）与就绪（readiness）。
- 检查数据库、缓存、磁盘、队列积压。
- 监控：请求量、错误率、P95 延迟、FPM 队列、OPcache 命中率。
- 告警阈值与值班流程明确。

## 十二、零停机部署

- 新版本部署到新目录，切换软链接，reload FPM。
- 数据库迁移向后兼容：先加字段，再切代码，最后删旧字段。
- 队列 Worker 部署后重启。
- 静态资源带 hash 文件名，避免缓存问题。
- 回滚方案与数据库回滚策略提前准备。

## 十三、Windows 与 Linux 差异

| 项目 | Windows | Linux |
|---|---|---|
| PHP-FPM | 不可用/不推荐 | 标准 |
| 进程管理 | 任务计划程序、服务 | systemd、Supervisor |
| 路径 | `\`，大小写不敏感 | `/`，大小写敏感 |
| 文件锁 | 行为不同 | 标准 |
| 扩展 | 需对应 DLL | 编译或包管理 |
| 信号 | 无 `pcntl` | 可用 |

跨平台项目注意路径拼接用 `DIRECTORY_SEPARATOR` 或 `__DIR__`，文件名大小写保持一致。

## 十四、常见坑

- 生产 `display_errors=On`，堆栈泄漏。
- OPcache 未重启，部署后仍执行旧代码。
- `root` 指向项目根而非 `public`，源码可被下载。
- FPM `pm.max_children` 过大导致内存耗尽。
- `max_execution_time` 与队列任务超时冲突。
- 上传大小限制不一致（Nginx / PHP / 应用）。
- 时区未配置，日志与业务时间偏移。

## References

- PHP-FPM：<https://www.php.net/manual/en/install.fpm.php>
- OPcache：<https://www.php.net/manual/en/book.opcache.php>
- php.ini：<https://www.php.net/manual/en/ini.core.php>
- Nginx FastCGI：<https://nginx.org/en/docs/http/ngx_http_fastcgi_module.html>
- Docker PHP：<https://hub.docker.com/_/php>
