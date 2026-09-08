# 灰度切流手册

本文档说明如何从「纯 SSR + Session」逐步切换到「SPA + Bearer Token API」，同时保留回滚能力。

## 目录

- [阶段划分](#阶段划分)
- [阶段 0：本地开发（当前状态）](#阶段 0本地开发当前状态)
- [阶段 1：生产首次上线（共存）](#阶段 1生产首次上线共存)
- [阶段 2：内部灰度（10% 用户）](#阶段 2内部灰度10-用户)
- [阶段 3：扩大灰度（50% 用户）](#阶段 3扩大灰度50-用户)
- [阶段 4：全量切换（100%）](#阶段 4全量切换100)
- [阶段 5：SSR 页面下线](#阶段 5ssr-页面下线)
- [观测指标与告警](#观测指标与告警)
- [常见故障与排查](#常见故障与排查)

---

## 阶段划分

```
时间 →
[阶段 0] ──── [阶段 1] ──── [阶段 2] ──── [阶段 3] ──── [阶段 4] ──── [阶段 5]
本地开发      共存上线      10% 灰度      50% 灰度      100% 切换      SSR 下线
```

每个阶段之间**至少观察 24 小时**，无异常再推进下一阶段。

---

## 阶段 0：本地开发（当前状态）

- SPA 跑在 `http://127.0.0.1:5173`（Vite dev）
- PHP API 跑在 `http://127.0.0.1:8000/api/v1/`
- 前后端跨域，通过 CORS 允许
- 前后端各自独立，不共享代码

**命令：**

```powershell
# 后端
$php = 'F:\programs\php-8.5.10-nts-Win32-vs17-x64\php.exe'
& $php -S 127.0.0.1:8000 -t public

# 前端（另一个终端）
cd client
pnpm dev
```

**验证：** 打开 `http://127.0.0.1:5173/`，登录 admin/admin123，能正常走文章/活动 CRUD。

---

## 阶段 1：生产首次上线（共存）

**目标：** 让 SPA 与 SSR 页面同时存在，用户无感。

**关键约束：**
- 根路径 `/` 继续走 SSR（原页面不动）
- 新 SPA 挂在 `/app/`（Vite base 是 `/app/`）
- `/api/v1/*` 是新 API，`/api/activities` 是旧接口
- 两个页面共享 Nginx，但通过 URL 前缀隔离

**部署步骤：**

1. **前端改 base 配置（一次性改动）：**

   修改 `../../web`：
   ```typescript
   export default defineConfig({
     base: '/app/',  // SPA 挂在 /app/ 下
     plugins: [...],
     ...
   })
   ```

   修改 `../../web`：
   ```typescript
   export const router = createRouter({
     history: createWebHistory('/app/'),  // history 模式 base 也是 /app/
     routes: [...]
   })
   ```

   重新 `pnpm build` 部署。

2. **建目录 + 上传：**

   ```bash
   # 前端 dist 部署到 /app/
   mkdir -p /var/www/php-learn/web/dist
   rsync -avz --delete web/dist/ /var/www/php-learn/web/dist/

   # 后端不动
   ```

3. **Nginx 加 location：**

   在 server block 加：
   ```nginx
   location /app/ {
       alias /var/www/php-learn/client/dist/;
       index index.html;
       location /app/assets/ {
           expires 1y;
           add_header Cache-Control "public, immutable";
       }
       try_files $uri $uri/ /app/index.html;
   }

   location /api/v1/ {
       fastcgi_pass unix:/var/run/php/php-fpm.sock;
       include fastcgi_params;
       fastcgi_param SCRIPT_FILENAME /var/www/php-learn/public/index.php;
   }
   ```

4. **验证：**

   ```bash
   # SSR 页面仍工作
   curl -sI https://php-learn.example.com/           → 200
   curl -sI https://php-learn.example.com/articles    → 200
   curl -sI https://php-learn.example.com/api/activities → 200

   # SPA 可访问
   curl -sI https://php-learn.example.com/app/       → 200
   curl -sI https://php-learn.example.com/app/articles → 200

   # 新 API 可访问
   curl -s https://php-learn.example.com/api/v1/health → {"code":0,...}

   # 老 API 可访问
   curl -s https://php-learn.example.com/api/activities  → [{"id":...}]
   ```

5. **通知团队：** 通过 `/app/` 手动访问 SPA 试用，收反馈。

---

## 阶段 2：内部灰度（10% 用户）

**目标：** 小范围真实用户验证，发现异常及时回滚。

**方案 A（推荐）：基于用户 ID 灰度**

- 在 Nginx 里根据用户 cookie 中的 ID 取模，10% 用户跳 SPA
- 需要 PHP 后端在登录时种一个 cookie 标记用户 ID

**方案 B：基于 URL 参数灰度**

- URL 加 `?v=spa` 直接看 SPA，加 `?v=ssr` 看 SSR
- 最轻量，适合内部测试

**方案 C：基于 Cookie 灰度（最简单）**

Nginx 配置：
```nginx
server {
    listen 443 ssl;
    server_name php-learn.example.com;

    # 灰度 cookie 判断
    set $app_version "ssr";
    if ($cookie_app_version = "spa") {
        set $app_version "spa";
    }

    # 灰度用户跳 SPA（10%）
    if ($app_version = "spa" -a $request_uri = "/") {
        return 302 /app/;
    }
    if ($app_version = "spa" -a $request_uri ~ "^/articles") {
        return 302 /app/articles;
    }
    if ($app_version = "spa" -a $request_uri ~ "^/activities") {
        return 302 /app/activities;
    }

    # 其余走 SSR（默认）
    ...
}
```

后端 PHP 加登录时种 cookie：
```php
// AuthController::login 成功后（旧 SSR 版）
// 10% 用户设为 spa，其余 ssr
$userHash = crc32($user->id . time());
$isGray = ($userHash % 100) < 10;
setcookie('app_version', $isGray ? 'spa' : 'ssr', time() + 86400 * 7);
```

**验证清单：**

- [ ] 灰度用户能正常登录（新旧两套都能登）
- [ ] 灰度用户能浏览文章列表
- [ ] 灰度用户能创建/编辑/删除文章
- [ ] 灰度用户能创建活动
- [ ] 非灰度用户路径完全不受影响
- [ ] 后端错误率未上升（对比监控）
- [ ] API 5xx 率未上升

---

## 阶段 3：扩大灰度（50% 用户）

- 修改 Nginx 灰度比例：`< 10` 改为 `< 50`
- 修改 PHP 后端 `crc32 % 100` 阈值：`< 10` 改为 `< 50`
- 观察 24 小时

**关键指标：**

| 指标 | 灰度前基线 | 灰度后允许波动 |
|---|---|---|
| API P99 延迟 | < 200ms | < 300ms |
| API 5xx 率 | < 0.1% | < 0.5% |
| 前端 JS 错误率 | < 0.1% | < 0.5% |
| 登录成功率 | > 99% | > 98.5% |
| 用户投诉数 | 基线 | < 基线 × 2 |

---

## 阶段 4：全量切换（100%）

- Nginx 灰度改成 `< 101`（永远为真）
- 或直接把 `location = /` 改为 `return 302 /app/;`
- 保留 SSR 页面 24 小时不删（紧急回滚用）

**关键操作：**

1. 通知用户即将切换（邮件/公告）
2. 观察灰度全量前最后一次数据
3. 切换并观察 30 分钟
4. 若 5xx 率上升超阈值 → 立即回滚

---

## 阶段 5：SSR 页面下线

**前置条件：**

- [ ] SPA 全量稳定运行 7 天
- [ ] 无严重用户投诉
- [ ] 老 Session 版接口访问量归零
- [ ] 备份旧版代码和配置（保留至少 30 天）

**操作：**

1. Nginx 移除 SSR 相关 location
2. 后端移除旧路由（`app/routes.php` 里 `/`、`/articles`、`/login` 等）
3. 后端移除旧 Controller（`AuthController` SSR 版、`ArticleController` SSR 版）
4. 后端移除 Session 中间件（`Authenticate`、`CsrfProtection`）
5. 后端移除视图目录（`views/`）
6. 前端路由去掉 `/app/` 前缀（`base: '/'`，`createWebHistory('/')`）
7. `pnpm build` 重新部署
8. Nginx `location /app/` 改为 `location /`

**验证：**

- [ ] `GET /` → SPA index.html
- [ ] `GET /articles` → SPA 文章页
- [ ] 登录/登出正常
- [ ] 老 Session 版接口全部 404

---

## 观测指标与告警

**推荐监控工具：**
- Prometheus + Grafana（指标）
- Sentry（前端 JS 错误 + 后端异常）
- Loki + Grafana（日志聚合）

**核心告警：**

| 告警 | 阈值 | 处理 |
|---|---|---|
| API 5xx 率 | > 1% 持续 5 分钟 | 立即回滚 |
| API P99 延迟 | > 1s 持续 5 分钟 | 排查慢查询 |
| 前端 JS 错误率 | > 1% | 检查前端版本 |
| 登录成功率 | < 95% | 检查 auth 链路 |
| 502/504 Nginx | > 0.1% | 检查 php-fpm 状态 |

---

## 常见故障与排查

### 1. SPA 部署后 404

**原因：** Nginx `try_files` 配置错，或前端 base 与实际部署路径不匹配。

**排查：**
```bash
# 检查前端资源是否可访问
curl -sI https://php-learn.example.com/app/assets/index-xxxx.js

# 检查 SPA fallback 是否正确
curl -sI https://php-learn.example.com/app/articles/999
# 应该返回 index.html（200），不是 404
```

### 2. 用户登录后跳 404

**原因：** 路由跳转路径与 Nginx location 不匹配。

**排查：**
- 检查 SPA 跳转的路径是否带 `/app/` 前缀
- 检查 Nginx `location /app/` 的 `try_files` 是否覆盖

### 3. API 跨域失败（分域名部署）

**原因：** 后端 `cors.allowed_origins` 未加前端域名。

**排查：**
```bash
# 看 OPTIONS 预检响应头
curl -sI -X OPTIONS https://api.example.com/api/v1/auth/login \
  -H "Origin: https://php-learn.example.com" \
  -H "Access-Control-Request-Method: POST"
# 应该返回 204 + Access-Control-Allow-Origin: https://php-learn.example.com
```

### 4. 用户 token 频繁失效

**原因：** 服务器时间不同步，JWT exp 校验失败。

**排查：**
```bash
# 服务器时间比对
date -u
# 与标准时间戳差 > 30 秒就要校准
sudo chronyc makestep  # 或 ntpdate pool.ntp.org
```

### 5. 灰度切换后老接口仍被调用

**原因：** 部分客户端（如移动端、老浏览器）仍在走 SSR。

**处理：**
- 观察 `/api/activities` 访问量
- 若 24 小时无调用可安全下线
