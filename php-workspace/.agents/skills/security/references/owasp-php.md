# OWASP 风险的 PHP 代码示例

本文档给出常见漏洞的错误写法与正确写法。示例是通用模式，实际项目按框架与版本调整。

## 1. SQL 注入

```php
// 错误
$email = $_GET['email'];
$rows = $pdo->query("SELECT * FROM users WHERE email = '{$email}'")->fetchAll();

// 正确
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
$stmt->execute(['email' => $_GET['email']]);
$rows = $stmt->fetchAll();
```

动态排序：

```php
$sortable = ['created_at', 'name', 'status'];
$sort = in_array($_GET['sort'] ?? '', $sortable, true) ? $_GET['sort'] : 'created_at';
$dir  = ($_GET['dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

$sql = "SELECT * FROM orders ORDER BY {$sort} {$dir} LIMIT :limit OFFSET :offset";
```

## 2. XSS

```php
// 错误：直接输出
echo "<div>{$_GET['name']}</div>";

// 正确：HTML 上下文编码
$name = htmlspecialchars($_GET['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
echo "<div>{$name}</div>";
```

```php
// 错误：把数据直接塞进内联脚本
echo "<script>var user = {$_GET['user']};</script>";

// 正确
$json = json_encode($user, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
echo "<script>var user = {$json};</script>";
```

```php
// 富文本：白名单清洗，而不是 strip_tags
$clean = (new HTMLPurifier(HTMLPurifier_Config::createDefault()))->purify($html);
```

## 3. CSRF

```php
// 生成
$_SESSION['csrf'] = bin2hex(random_bytes(32));

// 校验
$token = $_POST['_token'] ?? '';
if (! hash_equals($_SESSION['csrf'] ?? '', $token)) {
    http_response_code(419);
    exit('Invalid CSRF token');
}
```

- 校验使用 `hash_equals()`，避免时序攻击。
- 写操作只接受 POST/PUT/PATCH/DELETE。

## 4. SSRF

```php
// 错误：用户可控 URL 直接请求
$client->request('GET', $_GET['url']);

// 正确：白名单 + 内网校验
$host = parse_url($_GET['url'], PHP_URL_HOST);
$allowed = ['api.example.com', 'cdn.example.com'];
if (! in_array($host, $allowed, true)) {
    throw new RuntimeException('Host not allowed');
}

$ip = gethostbyname($host);
if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
    throw new RuntimeException('Private address not allowed');
}
```

同时禁用跟随重定向，或校验重定向目标。

## 5. 文件上传

```php
// 错误
move_uploaded_file($_FILES['file']['tmp_name'], 'uploads/' . $_FILES['file']['name']);

// 正确
$allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($_FILES['file']['tmp_name']);

if (! isset($allowed[$mime])) {
    throw new RuntimeException('Invalid file type');
}
if ($_FILES['file']['size'] > 5 * 1024 * 1024) {
    throw new RuntimeException('File too large');
}

$name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
$dest = __DIR__ . '/../storage/uploads/' . $name; // 不在 Web 根目录
move_uploaded_file($_FILES['file']['tmp_name'], $dest);
```

## 6. 路径穿越

```php
// 错误
readfile(__DIR__ . '/files/' . $_GET['name']);

// 正确
$base = realpath(__DIR__ . '/files');
$target = realpath($base . DIRECTORY_SEPARATOR . basename($_GET['name']));

if ($target === false || ! str_starts_with($target, $base . DIRECTORY_SEPARATOR)) {
    throw new RuntimeException('Invalid path');
}
readfile($target);
```

## 7. 命令注入

```php
// 错误
exec('convert ' . $_GET['file'] . ' out.png');

// 正确：参数数组，不经过 shell
use Symfony\Component\Process\Process;

$process = new Process(['convert', $inputPath, $outputPath]);
$process->setTimeout(30);
$process->run();
```

## 8. 反序列化

```php
// 错误
$data = unserialize($_COOKIE['state']);

// 正确
$data = unserialize($_COOKIE['state'], ['allowed_classes' => false]);

// 更推荐
$data = json_decode($_COOKIE['state'], true, 512, JSON_THROW_ON_ERROR);
```

## 9. Session 固定与劫持

```php
// 登录成功后重新生成 ID
session_regenerate_id(true);
```

```ini
session.use_strict_mode=1
session.cookie_httponly=1
session.cookie_secure=1
session.cookie_samesite=Lax
```

## 10. 密码存储

```php
// 错误
$hash = md5($password);

// 正确
$hash = password_hash($password, PASSWORD_DEFAULT);

if (! password_verify($input, $hash)) {
    // 失败，统一错误信息
}

if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
    $newHash = password_hash($input, PASSWORD_DEFAULT);
    // 更新数据库
}
```

## 11. JWT 校验

```php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

try {
    $payload = JWT::decode($token, new Key($secret, 'HS256'));
} catch (\Firebase\JWT\ExpiredException $e) {
    throw new AuthenticationException('Token expired');
} catch (\Throwable $e) {
    throw new AuthenticationException('Invalid token');
}
```

- 算法固定为签发的算法，不要让客户端决定 `alg`。
- 校验 `iss`、`aud`、`exp`：

```php
if (($payload->iss ?? '') !== 'acme-api') {
    throw new AuthenticationException('Invalid issuer');
}
```

## 12. 授权（IDOR）

```php
// 错误：只校验登录
$order = Order::find($_GET['id']);

// 正确：同时校验归属
$order = Order::where('id', $_GET['id'])
    ->where('user_id', $currentUser->id)
    ->firstOrFail();
```

## 13. CORS 误配

```php
// 错误：任意来源 + 允许凭据
header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
header('Access-Control-Allow-Credentials: true');

// 正确：白名单
$allowed = ['https://app.example.com', 'https://admin.example.com'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed, true)) {
    header("Access-Control-Allow-Origin: {$origin}");
    header('Access-Control-Allow-Credentials: true');
    header('Vary: Origin');
}
```

## 14. 安全响应头

```php
header("Content-Security-Policy: default-src 'self'; object-src 'none'");
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
```

## 15. 错误信息泄漏

```php
// 生产环境
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// 统一错误响应
http_response_code(500);
echo json_encode(['error' => 'Internal Server Error']);
```

## 16. 日志脱敏

```php
$safe = [
    'user_id' => $user->id,
    'email' => preg_replace('/(.{2}).*(@.*)/', '$1***$2', $user->email),
    // 不记录 password、token、card_number
];
$logger->info('user.login', $safe);
```

## 17. 依赖漏洞检查

```powershell
composer audit
composer outdated --direct
```

在 CI 中把 `composer audit` 设为必过项；高危漏洞阻断发布。

## 18. 密钥管理

```php
// 错误
$apiKey = 'sk-live-xxxxxxxx';

// 正确
$apiKey = $_ENV['PAYMENT_API_KEY']
    ?? throw new RuntimeException('PAYMENT_API_KEY not configured');
```

- `.env` 不提交。
- 不同环境不同密钥。
- 密钥轮换时保留旧密钥过渡期，并记录轮换时间。
