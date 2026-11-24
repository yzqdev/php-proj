---
name: security
description: PHP Web 安全开发指南：注入、XSS、CSRF、SSRF、文件上传、认证授权、JWT 与依赖供应链
---

# PHP 安全开发指南

## Overview

安全不是最后加一层过滤器，而是贯穿输入、处理、输出、存储、依赖的每个环节。本指南按 OWASP 风险组织，代码级细节见 `references/owasp-php.md`。

核心原则：

1. **所有输入不可信**：HTTP 参数、Header、Cookie、文件、队列消息、数据库读出的历史数据。
2. **所有输出需编码**：按上下文（HTML、JS、URL、SQL、Shell）选择编码方式。
3. **最小权限**：数据库账号、文件权限、Token 权限、容器权限。
4. **纵深防御**：不依赖单一校验。
5. **默认安全**：默认拒绝、默认关闭、默认加密。

## 一、SQL 注入

- 一律使用预处理语句与占位符。
- 表名、列名、排序方向用白名单，不能参数化。
- 不使用字符串拼接构造 SQL，即使"看起来是数字"。

```php
// 正确
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
$stmt->execute(['email' => $email]);

// 错误
$sql = "SELECT * FROM users WHERE email = '{$email}'";
```

详见 `.agents/skills/data-access/SKILL.md`。

## 二、XSS

- 输出到 HTML 用 `htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8')`。
- 输出到 JS 用 `json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)`。
- 输出到 URL 用 `rawurlencode()`。
- 不用 `strip_tags()` 作为唯一防护。
- 富文本用成熟的白名单清洗库（如 HTML Purifier），不要自研。
- 设置 `Content-Security-Policy` 作为第二道防线。

```php
echo htmlspecialchars($userInput, ENT_QUOTES | ENT_HTML5, 'UTF-8');
```

## 三、CSRF

- 基于 Cookie/Session 的写操作必须校验 CSRF Token。
- Token 每会话或每请求生成，服务端校验。
- 使用 `SameSite=Lax/Strict` Cookie 降低风险。
- API 使用 Bearer Token 且不依赖 Cookie 时，CSRF 风险较低，但仍要防 CORS 误配。

```php
if (! hash_equals($_SESSION['csrf'], $_POST['_token'] ?? '')) {
    throw new RuntimeException('Invalid CSRF token');
}
```

## 四、SSRF

- 只允许访问白名单域名/网段。
- 禁止访问内网地址（`127.0.0.0/8`、`10.0.0.0/8`、`172.16.0.0/12`、`192.168.0.0/16`、`169.254.0.0/16`）。
- 解析 DNS 后再次校验 IP，防 DNS Rebinding。
- 禁止跟随重定向到非白名单地址。
- 设置超时与响应大小上限。

## 五、文件上传

- 白名单扩展名与 MIME，双重校验。
- 重命名文件，不使用用户原始文件名。
- 存储目录不在 Web 根目录，或禁止执行脚本。
- 限制大小与数量。
- 图片重新编码以剥离恶意内容。
- 下载走应用鉴权，不直接暴露存储路径。

## 六、路径穿越

```php
$base = realpath(__DIR__ . '/storage/files');
$target = realpath($base . '/' . $userPath);

if ($target === false || ! str_starts_with($target, $base . DIRECTORY_SEPARATOR)) {
    throw new RuntimeException('Invalid path');
}
```

- 用 `realpath()` 规范化后再比较前缀。
- 不使用用户输入直接拼接文件路径。
- 拒绝 `..`、绝对路径、空字节。

## 七、命令注入

```php
// 正确：参数数组，不经过 shell
$process = new Symfony\Component\Process\Process(['convert', $input, $output]);
$process->run();

// 错误
exec("convert {$input} {$output}");
```

- 优先使用库 API，不调用 shell。
- 必须调用时用参数数组或 `escapeshellarg()`。
- 不把用户输入拼进命令。

## 八、反序列化

- 不对不可信数据使用 `unserialize()`。
- 需要时用 `unserialize($data, ['allowed_classes' => false])`。
- 优先用 JSON。
- 框架的签名 Cookie/加密序列化要注意密钥管理。

## 九、Session 安全

```ini
session.cookie_httponly=1
session.cookie_secure=1
session.cookie_samesite=Lax
session.use_strict_mode=1
session.use_only_cookies=1
```

- 登录成功后 `session_regenerate_id(true)`。
- 设置合理过期时间，退出时销毁 Session。
- Session 数据服务端存储，不把敏感信息放 Cookie。
- 多实例部署用 Redis/数据库共享 Session。

## 十、密码哈希

```php
$hash = password_hash($password, PASSWORD_DEFAULT);
if (! password_verify($input, $hash)) {
    throw new AuthenticationException();
}
if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
    // 更新为新算法
}
```

- 使用 `password_hash()`，不自己实现加密。
- 不使用 MD5/SHA1 存密码。
- 不限制密码过短或强制无意义复杂度；限制最大长度防 DoS。
- 登录失败信息统一，避免用户名枚举。

## 十一、JWT 安全

- 校验签名、`exp`、`nbf`、`iss`、`aud`。
- 固定算法，拒绝 `none` 与算法混淆。
- 密钥足够长，HS256 用随机 32 字节以上；或使用 RS256/ES256。
- 不在 payload 放敏感信息（可被 Base64 解码）。
- 短期 Access Token + Refresh Token，支持吊销。
- 令牌存 `HttpOnly` Cookie 或安全存储，避免 XSS 窃取。

## 十二、CORS

```php
header('Access-Control-Allow-Origin: https://app.example.com');
header('Access-Control-Allow-Credentials: true');
header('Vary: Origin');
```

- 不要用 `*` 配合 `Allow-Credentials: true`。
- 白名单校验 Origin，不直接回显任意 Origin。
- 预检请求正确响应 `OPTIONS`。
- 限制允许的 Header 与 Method。

## 十三、TLS 与传输

- 全站 HTTPS，HSTS。
- 证书有效，禁用弱协议与弱加密套件。
- 内部服务调用也尽量 TLS。
- 不在 URL query 传敏感信息。

## 十四、限流与防滥用

- 登录、注册、验证码、支付接口必须限流。
- 按 IP、用户、接口维度限流。
- 分布式限流用 Redis 计数器或令牌桶。
- 失败次数过多临时锁定或加验证码。
- 防重放：一次性 nonce + 时间戳 + 签名。

## 十五、认证与授权

- 认证回答"你是谁"，授权回答"你能做什么"。
- 每个敏感操作都校验资源归属，不只是登录状态。
- 防 IDOR：`WHERE id = ? AND user_id = ?`。
- 权限变更立即生效，不依赖客户端缓存。
- 管理接口与用户接口分离，独立鉴权。

## 十六、依赖与供应链

- `composer audit` 定期检查漏洞。
- 锁定 `composer.lock`，生产用 `composer install`。
- 不执行来源不明的 Composer 脚本。
- 关注依赖的维护状态与发布记录。
- 私有包使用可信仓库，校验来源。

## 十七、密钥与配置

- 密钥放环境变量或密钥管理服务，不入库、不入仓。
- `.env` 加入 `.gitignore`，提供 `.env.example`。
- 不同环境使用不同密钥。
- 密钥轮换有计划，旧密钥有过渡期。
- 日志与错误页面不输出配置与密钥。

## 十八、安全响应头

```text
Content-Security-Policy: default-src 'self'
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
Referrer-Policy: strict-origin-when-cross-origin
Strict-Transport-Security: max-age=31536000; includeSubDomains
Permissions-Policy: geolocation=(), microphone=()
```

## 十九、提交前安全清单

- [ ] 所有 SQL 使用参数化查询。
- [ ] 所有输出按上下文编码。
- [ ] 写操作有 CSRF 或 Token 防护。
- [ ] 上传文件校验类型、大小、存储位置。
- [ ] 文件路径经过规范化与白名单校验。
- [ ] 外部请求限制目标地址与重定向。
- [ ] 密码使用 `password_hash()`。
- [ ] JWT 校验算法与过期时间。
- [ ] 敏感操作校验资源归属。
- [ ] 日志不含敏感信息。
- [ ] 密钥来自环境变量。
- [ ] `composer audit` 无高危漏洞。
- [ ] 生产 `display_errors=Off`、`APP_DEBUG=false`。

## References

- OWASP Top 10：<https://owasp.org/www-project-top-ten/>
- OWASP Cheat Sheets：<https://cheatsheetseries.owasp.org/>
- PHP 安全：<https://www.php.net/manual/en/security.php>
- 代码级示例：`references/owasp-php.md`
