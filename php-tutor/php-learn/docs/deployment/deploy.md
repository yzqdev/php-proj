# 完整部署手册

本文档说明 php-learn 项目从代码到生产环境的完整部署流程，涵盖本地开发、CI/CD、生产环境搭建、目录结构、环境变量、密钥管理等全部内容。

## 目录

- [项目拓扑](#项目拓扑)
- [环境要求](#环境要求)
- [目录结构（生产）](#目录结构生产)
- [一次性初始化](#一次性初始化)
- [日常部署流程](#日常部署流程)
- [环境变量清单](#环境变量清单)
- [密钥管理](#密钥管理)
- [数据库备份](#数据库备份)
- [CI/CD 参考配置](#cicd-参考配置)
- [本地开发环境快速启动](#本地开发环境快速启动)
- [常见部署问题](#常见部署问题)

---

## 项目拓扑

```
┌─────────────────────────────────────────────────┐
│  用户浏览器                                       │
│  ├─ 前端 SPA（Vue 3 + TS + Tailwind v4）        │
│  └─ 调用 /api/v1/*（跨域或同域）                 │
└────────────────────────┬────────────────────────┘
                         │
        ┌────────────────┴────────────────┐
        │                                 │
┌───────▼────────┐                ┌───────▼────────┐
│  Nginx         │                │  Nginx         │
│  前端 host     │                │  后端 host     │
│  (方案 A/B)    │                │  (仅方案 A)    │
└───────┬────────┘                └───────┬────────┘
        │                                 │
┌───────▼────────┐                ┌───────▼────────┐
│  client/dist/  │                │  php-fpm       │
│  (静态文件)    │                │  → public/     │
└────────────────┘                └───────┬────────┘
                                          │
                                    ┌─────▼─────┐
                                    │  MySQL    │
                                    │  8.4      │
                                    └───────────┘
```

## 环境要求

### 服务器硬件

| 组件 | 最低要求 | 推荐 |
|---|---|---|
| CPU | 1 核 | 2+ 核 |
| 内存 | 2 GB | 4+ GB |
| 磁盘 | 20 GB | 50+ GB SSD |
| 带宽 | 1 Mbps | 10+ Mbps |

### 软件版本

| 软件 | 版本 | 说明 |
|---|---|---|
| Ubuntu/Debian | 22.04 LTS | 推荐 LTS |
| Nginx | 1.24+ | 支持 HTTP/2 |
| PHP | 8.5 | 无框架模式 |
| PHP-FPM | 8.5 | 与 PHP 同版本 |
| MySQL | 8.4 | utf8mb4 |
| Redis | 7.x | 可选，用于 session/cache |
| Node.js | 20+ | 前端构建用 |
| pnpm | 9+ | 前端包管理 |
| Composer | 2.7+ | PHP 依赖 |
| Certbot | 2.0+ | SSL 证书 |
| Git | 2.40+ | 代码部署 |

---

## 目录结构（生产）

```
/var/www/php-learn/
├── public/                     Web 入口（Nginx 指向这里）
│   ├── index.php               Front Controller
│   └── assets/                 静态资源
├── app/                        业务代码
│   ├── bootstrap.php
│   ├── api/
│   │   └── routes.php
│   ├── Controllers/            （含 Api/ 子目录）
│   ├── Entities/               （Doctrine ORM 全部实体 + 仓储）
│   ├── Middleware/             （含 ApiAuthenticate、Cors、ErrorRenderer）
│   ├── Services/               （含 Jwt、TokenService、DoctrineServiceProvider）
│   ├── Helpers/
│   ├── Resources/              （Resource 序列化层）
│   ├── Exceptions/
│   └── api/                    （Api routes.php）
├── config/
│   ├── config.example.php      模板（可提交）
│   └── config.php              真实配置（.gitignore 忽略）
├── client/                     前端源码（构建产物上传到 dist）
│   └── dist/                   pnpm build 输出
├── storage/
│   ├── session/                Session 文件（旧 SSR 用）
│   ├── uploads/                上传文件
│   └── logs/                   日志
├── logs/                       应用日志
├── docs/                       文档
│   ├── sql/
│   │   ├── schema.sql          建库建表
│   │   └── alter_add_refresh_tokens.sql
│   └── deployment/             部署文档
├── composer.json
├── composer.lock
└── .env                        环境变量（若使用）
```

**权限设置：**

```bash
# Web 用户 www-data 拥有可写目录
chown -R www-data:www-data storage logs web/dist
chmod -R 775 storage logs web/dist
# 代码目录只读
chmod -R 644 app config public web/web 2>/dev/null || true
```

---

## 一次性初始化

### 1. 安装系统包

```bash
sudo apt-get update
sudo apt-get install -y nginx mysql-server php8.5-fpm \
  php8.5-cli php8.5-fpm php8.5-mysql php8.5-pdo \
  php8.5-json php8.5-xml php8.5-curl php8.5-zip \
  php8.5-mbstring    # 生产环境建议开启，但本项目兼容未开情况
unzip zip git curl
```

### 2. 初始化 MySQL

```bash
sudo mysql_secure_installation    # 交互式配置 root 密码
sudo mysql -u root -p <<'SQL'
CREATE DATABASE php_learn
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_0900_ai_ci;
CREATE USER 'php_learn'@'localhost'
  IDENTIFIED BY '<strong-password>';
GRANT ALL ON php_learn.* TO 'php_learn'@'localhost';
FLUSH PRIVILEGES;
SQL
```

### 3. 部署代码

```bash
# 建目录
sudo mkdir -p /var/www/php-learn
sudo chown -R www-data:www-data /var/www/php-learn

# 拉代码（假设用 git）
cd /var/www/php-learn
sudo git clone <repo-url> .
sudo chown -R www-data:www-data .
```

### 4. 初始化数据库表

```bash
cd /var/www/php-learn
mysql -u root -p php_learn < docs/sql/schema.sql
mysql -u root -p php_learn < docs/sql/alter_add_refresh_tokens.sql
```

### 5. 安装 PHP 依赖

```bash
cd /var/www/php-learn
composer install --no-dev --optimize-autoloader
sudo chown -R www-data:www-data vendor
```

### 6. 安装 Node.js + pnpm

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs
sudo corepack enable
sudo corepack prepare pnpm@latest --activate
node -v && pnpm -v
```

### 7. 构建前端

```bash
cd /var/www/php-learn/web
pnpm install --frozen-lockfile
pnpm build
sudo chown -R www-data:www-data dist
```

### 8. 配置 Nginx

```bash
sudo cp web/docs/nginx.conf.example /etc/nginx/sites-available/php-learn.conf
sudo ln -s /etc/nginx/sites-available/php-learn.conf /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx
```

### 9. 申请 SSL 证书

```bash
sudo apt-get install -y certbot python3-certbot-nginx
sudo certbot --nginx -d php-learn.example.com -d api.php-learn.example.com
```

### 10. 配置 PHP-FPM

编辑 `/etc/php/8.5/fpm/pool.d/www.conf`：
```ini
[www]
user = www-data
group = www-data
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 10

# Session 与上传目录权限
php_value session.save_path /var/www/php-learn/storage/session
```

```bash
sudo systemctl restart php-fpm
```

### 11. 首次验证

```bash
curl -sI https://php-learn.example.com/                    → 200（SSR 首页）
curl -sI https://php-learn.example.com/app/                → 200（SPA）
curl -s  https://php-learn.example.com/api/v1/health       → {"code":0,...}
curl -sI https://php-learn.example.com/api/activities      → 200（老接口）
```

---

## 日常部署流程

### 后端部署

```bash
# 1. 拉代码
cd /var/www/php-learn
git pull

# 2. 安装/更新 Composer 依赖
composer install --no-dev --optimize-autoloader

# 3. 数据库迁移（如有）
mysql -u root -p php_learn < docs/sql/alter_xxx.sql

# 4. 重启 PHP-FPM
sudo systemctl restart php-fpm

# 5. 健康检查
curl -s https://php-learn.example.com/api/v1/health
```

### 前端部署

```bash
# 1. 拉代码
cd /var/www/php-learn/web
git pull

# 2. 安装依赖
pnpm install --frozen-lockfile

# 3. 构建
pnpm build

# 4. 部署 dist
sudo cp -r dist/ /var/www/php-learn/web/dist-new/
sudo mv /var/www/php-learn/web/dist /var/www/php-learn/web/dist-prev
sudo mv /var/www/php-learn/web/dist-new /var/www/php-learn/web/dist

# 5. 权限
sudo chown -R www-data:www-data /var/www/php-learn/web/dist

# 6. 清前端缓存（可选）
sudo systemctl reload nginx
```

### 灰度回滚

```bash
# 前端
sudo mv /var/www/php-learn/web/dist /var/www/php-learn/web/dist-bad
sudo mv /var/www/php-learn/web/dist-prev /var/www/php-learn/web/dist

# 后端
cd /var/www/php-learn
git checkout v1.4.2-stable
composer install --no-dev --optimize-autoloader
sudo systemctl restart php-fpm
```

---

## 环境变量清单

本项目大部分配置写在 `config/config.php`，只有以下少数需要 `.env` 或环境变量（可选）：

| 变量 | 用途 | 示例值 |
|---|---|---|
| `APP_ENV` | 环境标识 | `production` / `development` |
| `APP_DEBUG` | 是否输出详细错误 | `false` |
| `DB_HOST` | 数据库主机 | `127.0.0.1` |
| `DB_PORT` | 数据库端口 | `3306` |
| `DB_NAME` | 数据库名 | `php_learn` |
| `DB_USER` | 数据库用户 | `php_learn` |
| `DB_PASS` | 数据库密码 | `<strong-password>` |
| `JWT_SECRET` | JWT 密钥（base64 32 字节） | `<见下方密钥管理>` |
| `JWT_ACCESS_TTL` | Access token 有效期（秒） | `900` |
| `JWT_REFRESH_TTL` | Refresh token 有效期（秒） | `604800` |
| `CORS_ALLOWED_ORIGINS` | CORS 白名单（分号分隔） | `https://app.example.com` |
| `UPLOAD_STORAGE_DRIVER` | 上传存储驱动 | `local` / `s3` |
| `UPLOAD_URL_BASE` | 上传文件访问 URL 前缀 | `https://api.example.com/uploads` |

**注意：** 本项目当前实现把配置硬写在 `config/config.php`。若要改成 `.env` 读取，可在 `App\Helpers\Config::load()` 里加 getenv 逻辑（当前尚未实现）。

---

## 密钥管理

### JWT Secret 生成

```bash
# 生成 32 字节随机数并 base64 编码
php -r "echo base64_encode(random_bytes(32))"
# 输出示例：C2XBVtxVE23JGYDfmgwQK8PQns+78o9UhXdV8kbdX5M=
```

**要求：**
- 长度：32 字节
- 编码：base64
- 字符：可含 `+`、`/`、`=`

### 密钥轮换

生产环境建议每 90 天轮换一次 JWT secret：

1. 生成新 secret
2. 部署到 `config/config.php`
3. 保留旧 secret 一段时间（可通过多密钥方案，本项目未实现）
4. 通知所有用户重新登录

### 密钥存储

生产环境**不要**把 `config/config.php` 提交到 git。用以下方案之一：

- **Vault**（HashiCorp）
- **AWS Secrets Manager / Azure Key Vault / GCP Secret Manager**
- **Ansible Vault / SaltStack Vault**
- **简单方案**：本地维护一份 `config.php.prod`，部署时 rsync 覆盖

---

## 数据库备份

### 每日自动备份

`/etc/cron.d/php-learn-backup`：
```
0 3 * * * root /bin/bash /var/www/php-learn/scripts/backup-db.sh
```

`scripts/backup-db.sh`：
```bash
#!/bin/bash
set -e
BACKUP_DIR=/var/backups/php-learn/db
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR
mysqldump -u root -p<password> --single-transaction --routines php_learn \
  | gzip > $BACKUP_DIR/php-learn-$DATE.sql.gz
# 保留 30 天
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete
```

### 备份验证

每周一早上 8 点做一次恢复演练（在测试库）：
```bash
mysql -u root -p test_db < <(gunzip < php-learn-xxx.sql.gz)
```

### 备份大小估算

- 单用户：< 1 MB/日
- 100 用户：< 10 MB/日
- 保留 30 天：100 用户 < 300 MB

---

## CI/CD 参考配置

### GitHub Actions（参考）

`.github/workflows/deploy.yml`：
```yaml
name: deploy
on:
  push:
    branches: [main]
jobs:
  deploy-backend:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.5'
      - run: composer install --no-dev --optimize-autoloader
      - name: SSH deploy
        uses: appleboy/scp-action@v0.1.7
        with:
          host: ${{ secrets.HOST }}
          username: ${{ secrets.USER }}
          key: ${{ secrets.SSH_KEY }}
          source: "."
          target: /var/www/php-learn

  deploy-frontend:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'pnpm'
          cache-dependency-path: web/pnpm-lock.yaml
      - run: corepack enable
      - run: cd web && pnpm install --frozen-lockfile
      - run: cd web && pnpm build
      - name: SCP dist
        uses: appleboy/scp-action@v0.1.7
        with:
          host: ${{ secrets.HOST }}
          username: ${{ secrets.USER }}
          key: ${{ secrets.SSH_KEY }}
          source: "web/dist"
          target: /var/www/php-learn/web/dist
```

### GitLab CI（可选）

```yaml
stages: [build, deploy]
build-backend:
  stage: build
  image: php:8.5-cli
  script:
    - composer install --no-dev --optimize-autoloader

build-frontend:
  stage: build
  image: node:20
  script:
    - corepack enable
    - cd web && pnpm install --frozen-lockfile && pnpm build
  artifacts:
    paths:
      - web/dist/

deploy:
  stage: deploy
  image: alpine:latest
  script:
    - apk add --no-cache openssh-web rsync
    - ssh-keyscan $HOST >> ~/.ssh/known_hosts
    - rsync -avz --delete web/dist/ $USER@$HOST:/var/www/php-learn/web/dist/
```

---

## 本地开发环境快速启动

Windows 11 + PowerShell（本项目原生环境）：

### 1. 装依赖

```powershell
# PHP 依赖
composer install

# 前端依赖
cd client
pnpm install
```

### 2. 数据库

```powershell
mysql -uroot -p123456 -h 127.0.0.1 < docs\sql\schema.sql
mysql -uroot -p123456 -h 127.0.0.1 < docs\sql\alter_add_refresh_tokens.sql
```

### 3. 启动后端

```powershell
$php = 'F:\programs\php-8.5.10-nts-Win32-vs17-x64\php.exe'
& $php -S 127.0.0.1:8000 -t public
```

### 4. 启动前端（另开一个终端）

```powershell
cd client
pnpm dev
# 访问 http://127.0.0.1:5173/
```

### 5. 一键脚本（可选）

`run_dev.ps1`：
```powershell
$php = 'F:\programs\php-8.5.10-nts-Win32-vs17-x64\php.exe'

# 后端
& $php -S 127.0.0.1:8000 -t public

# 前端
cd client
pnpm dev
```

---

## 常见部署问题

### 1. Nginx 502 Bad Gateway

**原因：** php-fpm socket 未监听或权限不对。

**排查：**
```bash
sudo systemctl status php-fpm
sudo ss -xl | grep php-fpm     # 看 unix socket 是否监听
ls -la /var/run/php/           # 看 socket 文件权限
```

**修复：**
```bash
sudo systemctl restart php-fpm
sudo systemctl reload nginx
```

### 2. 500 Internal Server Error（PHP）

**原因：** 语法错误、权限错误、扩展缺失。

**排查：**
```bash
# 检查 PHP 语法
find /var/www/php-learn/app -name "*.php" -exec php -l {} \;

# 检查 php-fpm 错误日志
tail -n 100 /var/log/php8.5-fpm.log

# 检查扩展
php -m | grep -E "pdo|mysql|json|xml|curl"
```

### 3. CORS 拒绝

**原因：** 前端域名未在 `cors.allowed_origins` 白名单。

**修复：** 编辑 `config/config.php` 加入前端域名：
```php
'allowed_origins' => [
    'https://php-learn.example.com',
    'https://app.example.com',
],
```

### 4. Session 保存失败（旧 SSR）

**原因：** `storage/session/` 目录权限不对。

**修复：**
```bash
sudo chown -R www-data:www-data /var/www/php-learn/storage/session
sudo chmod 775 /var/www/php-learn/storage/session
```

### 5. 前端构建失败（pnpm install）

**原因：** 版本不兼容或缓存问题。

**修复：**
```bash
cd web
rm -rf node_modules
pnpm store prune
pnpm install --force
```

### 6. SSL 证书过期

**排查：**
```bash
sudo certbot certificates
openssl s_client -connect php-learn.example.com:443 2>/dev/null | openssl x509 -noout -enddate
```

**修复：** certbot 已自动配置 cron 每天检查，若证书仍过期：
```bash
sudo certbot renew --force-renewal
```
