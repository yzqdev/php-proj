# 开发环境与常用命令

## 环境要求

- PHP 8.1+,需启用 PDO 扩展(composer.json 已声明 `ext-pdo`)
- Composer

## 安装依赖

```bash
composer install
```

## 本地运行

```bash
php -S 0.0.0.0:8080 -t public
```

访问 `http://localhost:8080/hello`,应返回 `{"message":"Hello, World!"}`。

## 静态分析

PHPStan 配置在 `phpstan.neon`,level 为 `max`,检查范围包括 `src`、`config`、`routes`、`public`:

```bash
vendor/bin/phpstan analyse
```

## 配置

- 应用配置位于 `config/settings.php`(应用名、环境、日志设置),`APP_ENV` 环境变量可覆盖默认环境。
- 容器定义位于 `config/container.php`,新增公共服务时在此注册。
- 日志通过 Monolog 写入 stderr,默认级别 Debug。
