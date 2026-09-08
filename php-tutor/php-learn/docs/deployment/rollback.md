# 回滚指南

一旦灰度切流后出现严重问题，按本指南快速回滚到上一稳定版本。

## 目录

- [快速决策表](#快速决策表)
- [场景 A：SPA 前端异常（推荐优先方案）](#场景-aspa-前端异常)
- [场景 B：API 后端异常](#场景-bapi-后端异常)
- [场景 C：数据库迁移导致故障](#场景-c数据库迁移导致故障)
- [场景 D：Token 服务整体故障](#场景-dtoken-服务整体故障)
- [回滚后必做](#回滚后必做)

---

## 快速决策表

| 现象 | 定位 | 回滚方案 |
|---|---|---|
| SPA 白屏 / JS 报错 | 前端 | 场景 A（30 秒） |
| 登录 500 / 用户报错 | 后端 auth | 场景 B（1 分钟） |
| 文章/活动 CRUD 500 | 后端业务 | 场景 B（1 分钟） |
| API P99 延迟 > 3s | 后端/DB | 场景 B 或 C |
| 无法读取数据 | DB 问题 | 场景 C（5 分钟） |
| JWT 校验失败 | 时间不同步 | 场景 D（1 分钟） |

---

## 场景 A：SPA 前端异常

**适用：** SPA 页面白屏、JS 崩溃、构建产物问题、静态资源 404。

**策略：** 前端和后端可独立回滚，SPA 出问题时**回滚前端 dist 到上一稳定版本**。

### 步骤（30 秒）

1. **切换 Nginx 到 SPA 兜底路径：**

   ```bash
   # 编辑 nginx 配置，把 /app/ 指向备份目录
   sudo vim /etc/nginx/sites-enabled/php-learn.conf
   # 找到 location /app/，把 alias 改成上一版本
   # alias /var/www/php-learn/web/dist-prev/;
   sudo nginx -t && sudo systemctl reload nginx
   ```

2. **或临时关闭 SPA（灰度用户强制走 SSR）：**

   ```bash
   # Nginx 里注释掉 location /app/，reload
   # 灰度用户会被 302 回 /（SSR 页面）
   ```

3. **验证：**

   ```bash
   curl -sI https://php-learn.example.com/app/
   # 应该返回 200 且是上一稳定版本
   ```

### 回滚时间

- 有备份 dist：**< 30 秒**
- 无备份 dist：**3 分钟**（重新 git checkout 上一 tag 构建）

---

## 场景 B：API 后端异常

**适用：** 后端接口 500、登录失败、CRUD 报错。

**策略：** 回滚 PHP 代码到上一稳定版本，恢复 Session 版接口。

### 步骤（1 分钟）

1. **切回老代码：**

   ```bash
   cd /var/www/php-learn
   git log --oneline -5   # 找到上一稳定 tag
   git checkout v1.4.2-stable   # 或具体 commit
   composer install --no-dev --optimize-autoloader
   sudo systemctl restart php-fpm
   ```

2. **验证 API 恢复：**

   ```bash
   # 健康检查
   curl -s https://php-learn.example.com/api/v1/health
   # 应该返回 {"code":0,"message":"ok",...}

   # 老 Session 版接口
   curl -s https://php-learn.example.com/api/activities
   # 应该返回原格式 JSON 数组
   ```

3. **前端保留 SPA（如果 SPA 本身没问题）：**

   - Nginx 保持 `/app/` 指向前端 dist
   - 但如果 API 完全不可用，SPA 也会无法登录
   - 可临时把 Nginx 灰度改为 0%，强制用户走 SSR

### 回滚时间

- 有稳定 tag：**< 1 分钟**
- 需重新编译 Composer：**3-5 分钟**

---

## 场景 C：数据库迁移导致故障

**适用：** Step 2 添加了 `refresh_tokens` 表，之后若有更多 schema 变更，可能导致读写失败。

**策略：** 保留旧 schema，跳过新表访问。

### 步骤（5 分钟）

1. **临时禁用新表访问：**

   修改 `app/Services/TokenService.php`，把 `storeRefreshToken` / `lookupRefreshToken` 改为 no-op（开发分支临时改动）：

   ```php
   private function storeRefreshToken(int $userId, string $token, int $ttl): void {
       // 回滚期临时禁用
       return;
   }
   ```

   重新部署后端。

2. **或彻底删除 refresh_tokens 表（谨慎）：**

   ```sql
   DROP TABLE IF EXISTS refresh_tokens;
   ```

3. **验证：**

   - 登录正常
   - `/me` 接口正常
   - `/refresh` 接口报错（预期，因为 refresh token 无法写入/查询）
   - 老 Session 版登录仍可用

### 回滚时间

- 禁用表访问：**1 分钟**
- 恢复表结构：**5-10 分钟**（重新执行 alter SQL）

---

## 场景 D：Token 服务整体故障

**适用：** JWT 校验全失败、refresh token 全部失效。

**最常见原因：服务器时间不同步**

### 排查步骤

1. **检查服务器时间：**

   ```bash
   date -u  # 输出应接近 UTC 当前时间
   ```

2. **如果时间偏差 > 30 秒，校准：**

   ```bash
   sudo apt-get install chrony   # Ubuntu/Debian
   sudo chronyc makestep
   # 或
   sudo ntpdate pool.ntp.org
   ```

3. **验证时间：**

   ```bash
   date -u
   curl -s https://php-learn.example.com/api/v1/health | jq .data.time
   ```

### 回滚方案（如果不是时间问题）

1. **换 JWT secret（生成新密钥）：**

   ```bash
   # 生成新 secret
   NEW_SECRET=$(php -r "echo base64_encode(random_bytes(32))")
   ```

   修改 `config/config.php`：
   ```php
   'api' => [
       'jwt' => [
           'secret'  => $NEW_SECRET,  // 新密钥
           ...
       ]
   ]
   ```

2. **告知所有用户重新登录**（旧 token 会立即失效）

3. **恢复 refresh_tokens 表**（清空所有记录）：

   ```sql
   TRUNCATE TABLE refresh_tokens;
   ```

---

## 回滚后必做

无论哪种场景，回滚完成后必须：

### 1. 通知团队

- 通过即时通讯工具通知全体开发/运维
- 说明：回滚原因、当前状态、下一步计划

### 2. 收集现场

```bash
# 后端错误日志
tail -n 500 /var/www/php-learn/logs/*.log
# 或
journalctl -u php-fpm --since "10 minutes ago"

# Nginx 访问日志
tail -n 500 /var/log/nginx/access.log | grep 'api/v1'

# 前端错误监控（Sentry 等）
# 记录 JS 错误率、影响用户数
```

### 3. 提交事故报告

模板：
```
# 事故报告

**时间：** 2026-XX-XX XX:XX
**级别：** P1/P2/P3
**影响范围：** 灰度用户 X 人 / 全量
**症状：** 一句话描述
**原因（初判）：** ...
**回滚动作：** 场景 A/B/C/D，耗时 X 分钟
**后续排查：** ...
**永久修复计划：** ...
```

### 4. 修复后再上线

- 本地环境完整回归测试
- 灰度 10% 观察 24 小时
- 再逐步扩大到 100%
