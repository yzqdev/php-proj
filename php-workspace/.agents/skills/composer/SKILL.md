---
name: composer
description: Composer 2.x 依赖管理、自动加载、冲突排查、私有仓库与包发布的开发指南
---

# Composer 依赖管理指南

## Overview

Composer 是 PHP 的事实标准包管理器。理解 `composer.json`（声明）与 `composer.lock`（锁定）的区别，是排查依赖问题的基础。

- `composer.json`：项目声明的依赖与约束。
- `composer.lock`：实际安装的精确版本，必须提交到版本库。
- `vendor/`：安装结果，不提交。

本机 Composer 2.11.2，PHP 8.5.10。

## 基础命令

```powershell
composer init                      # 交互式创建 composer.json
composer require monolog/monolog   # 安装并写入 require
composer require --dev phpunit/phpunit
composer install                   # 按 lock 安装
composer update                    # 按 json 解析新版本并更新 lock
composer update monolog/monolog    # 只更新指定包
composer remove monolog/monolog
composer dump-autoload             # 重新生成自动加载
composer validate                  # 校验 composer.json
composer outdated                  # 查看可升级依赖
composer why vendor/package        # 谁依赖了它
composer why-not php 8.1           # 谁阻止升级
composer show --tree               # 依赖树
composer audit                     # 已知漏洞检查
```

区别：

- `install` 严格按 `composer.lock`，适合部署与 CI。
- `update` 会改变 lock，适合主动升级。
- CI 与生产环境用 `composer install --no-dev --optimize-autoloader`。

## composer.json 关键字段

```json
{
  "name": "acme/api",
  "description": "Acme REST API",
  "type": "project",
  "license": "proprietary",
  "require": {
    "php": "^8.1",
    "monolog/monolog": "^3.0"
  },
  "require-dev": {
    "phpunit/phpunit": "^11.0",
    "phpstan/phpstan": "^2.0"
  },
  "autoload": {
    "psr-4": {
      "App\\": "src/"
    }
  },
  "autoload-dev": {
    "psr-4": {
      "App\\Tests\\": "tests/"
    }
  },
  "config": {
    "sort-packages": true,
    "optimize-autoloader": true
  },
  "scripts": {
    "test": "phpunit",
    "analyse": "phpstan analyse",
    "check": [
      "@analyse",
      "@test"
    ]
  }
}
```

- `require` 是运行时依赖，`require-dev` 只用于开发/测试。
- `config.platform.php` 固定解析时的 PHP 版本，避免本地高版本解析出部署环境不可用的依赖。
- `sort-packages` 让依赖按字母排序，减少 diff 噪音。
- `scripts` 统一团队命令入口，CI 与本地一致。

## 版本约束

| 写法 | 含义 | 建议 |
|---|---|---|
| `^3.5` | `>=3.5.0 <4.0.0` | 应用项目常用 |
| `~3.5` | `>=3.5.0 <3.6.0` | 需要更保守时 |
| `3.5.*` | `>=3.5.0 <3.6.0` | 等价 `~3.5` |
| `>=3.5 <4.0` | 显式范围 | 需要精确控制时 |
| `dev-main` | 分支 | 仅临时，锁定 commit 更稳 |
| `*` | 任意 | 禁止用于正式项目 |

应用项目用 `^`；库项目约束可放宽；不要同时锁定过死导致无法升级安全补丁。

## PSR-4 自动加载

```json
{
  "autoload": {
    "psr-4": {
      "App\\": "src/"
    },
    "files": ["src/helpers.php"],
    "classmap": ["legacy/"]
  }
}
```

- PSR-4 是首选；类名与文件路径严格对应。
- `files` 用于函数库，每次请求都会加载，慎用。
- `classmap` 用于不符合 PSR-4 的历史代码。
- 修改自动加载后执行 `composer dump-autoload`；生产用 `--optimize --classmap-authoritative`。

## Scripts

```json
{
  "scripts": {
    "post-install-cmd": ["@php artisan package:discover --ansi"],
    "test": "@php vendor/bin/phpunit",
    "cs": "@php vendor/bin/php-cs-fixer fix --dry-run --diff"
  }
}
```

- 脚本跨平台时用 `@php` 前缀调用，不直接写 `php` 的绝对路径。
- 不要在执行 `composer install` 时自动执行有副作用的脚本（部署环境应可控）。
- Composer 脚本可执行任意命令，私有仓库与第三方脚本有供应链风险。

## 私有仓库与 Path 仓库

私有 Git 仓库：

```json
{
  "repositories": [
    { "type": "vcs", "url": "https://gitee.com/acme/internal-package.git" }
  ],
  "require": {
    "acme/internal-package": "dev-main"
  }
}
```

本地路径仓库（多包开发）：

```json
{
  "repositories": [
    { "type": "path", "url": "../internal-package", "options": { "symlink": true } }
  ]
}
```

- Path 仓库使用符号链接，改本地包立即生效。
- 生产环境不要保留 path 仓库，改为 VCS 或私有 Satis/Composer 仓库。

## 冲突排查

常见报错与处理：

| 报错 | 原因 | 处理 |
|---|---|---|
| `Your requirements could not be resolved` | 版本约束互斥 | `composer why-not vendor/pkg version` 定位 |
| `requires php ^8.2 but you have 8.1` | PHP 版本不匹配 | 升级 PHP 或降级包 |
| `Found package conflict` | 两个包依赖不同大版本 | 选兼容版本，或用替换包 |
| `Class not found` | 自动加载未更新 | `composer dump-autoload` |
| `Allowed memory size exhausted` | 解析占用内存 | `php -d memory_limit=-1 composer.phar update` |

排查顺序：

1. 读完整报错，看第一个冲突点。
2. `composer why-not`、`composer show --tree`。
3. 检查 `config.platform.php` 是否与真实环境一致。
4. 必要时临时放宽约束定位问题，再收紧。

## 依赖升级

```powershell
composer outdated --direct
composer update --dry-run
composer update --with-dependencies vendor/package
composer audit
```

- 小步升级，每次只升一组相关依赖。
- 升级前看 CHANGELOG 与迁移指南。
- 用 PHPStan/Psalm 的 `phpVersion` 与测试套件验证。
- `composer audit` 检查已知漏洞；`composer audit --format=json` 便于 CI。

## 包开发与发布

```json
{
  "name": "acme/logger",
  "type": "library",
  "autoload": { "psr-4": { "Acme\\Logger\\": "src/" } },
  "require": { "php": "^8.1", "psr/log": "^3.0" },
  "require-dev": { "phpunit/phpunit": "^11.0" },
  "extra": { "branch-alias": { "dev-main": "1.x-dev" } }
}
```

- 库项目不提交 `composer.lock`。
- 语义化版本：破坏性变更升 major。
- 发布前 `composer validate --strict`，打 Git tag 后提交 Packagist。
- 私有包可用 Satis、Private Packagist 或 VCS 仓库。

## 安全注意

- 不执行来源不明的 `post-install-cmd` 脚本。
- 定期 `composer audit`，关注依赖漏洞公告。
- 锁定 `composer.lock`，生产环境用 `composer install`。
- 不在 `composer.json` 中写入密钥、令牌或内网地址。
- 私有仓库凭据用环境变量或 `auth.json`，不提交。

## 常见坑

- 只提交 `composer.json` 不提交 `composer.lock`，导致环境版本漂移。
- 本地 PHP 版本高于部署环境，安装了解析不出的依赖；用 `config.platform.php` 固定。
- 修改命名空间后忘记 `dump-autoload`。
- `files` 自动加载的函数在 CLI 与 FPM 下重复加载。
- Windows 下 path repository 的符号链接需要开发者模式或管理员权限。

## References

- Composer 文档：<https://getcomposer.org/doc/>
- 版本约束：<https://getcomposer.org/doc/articles/versions.md>
- Packagist：<https://packagist.org/>
- PSR-4：<https://www.php-fig.org/psr/psr-4/>
