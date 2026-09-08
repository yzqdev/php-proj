<?php
declare(strict_types=1);

/**
 * Standalone smoke test — run: php tests/smoke.php (server must be running on :8099)
 */

function http(string $method, string $url, ?string $body = null, array $headers = []): array
{
    $opts = [
        'http' => [
            'method'        => $method,
            'ignore_errors' => true,
            'timeout'       => 10,
            'header'        => implode("\r\n", array_merge(['Content-Type: application/json'], $headers)),
        ],
    ];
    if ($body !== null) {
        $opts['http']['content'] = $body;
    }
    $ctx = stream_context_create($opts);
    $resp = file_get_contents($url, false, $ctx);
    $code = 0;
    foreach (http_get_last_response_headers() ?? [] as $h) {
        if (preg_match('#HTTP/\S+\s+(\d+)#', $h, $m)) {
            $code = (int) $m[1];
        }
    }
    return [$code, $resp];
}

$base = 'http://127.0.0.1:8099';
$pass = 0;
$fail = 0;

function check(string $name, bool $ok, string $detail = ''): void
{
    global $pass, $fail;
    if ($ok) {
        $pass++;
        echo "PASS  $name\n";
    } else {
        $fail++;
        echo "FAIL  $name" . ($detail !== '' ? " -- $detail" : '') . "\n";
    }
}

// ---- 1. Public article list -------------------------------------------
[$c, $r] = http('GET', "$base/api/v1/articles?page=1&per_page=2");
$d = json_decode($r, true);
check('GET /articles returns 200', $c === 200, "got $c");
check('GET /articles has data.items', isset($d['data']['items']));

// ---- 2. 404 for missing article ----------------------------------------
[$c, $r] = http('GET', "$base/api/v1/articles/999999");
$d = json_decode($r, true);
check('GET /articles/999999 returns 404', $c === 404, "got $c");
check('404 envelope has code/message/request_id', isset($d['code'], $d['message'], $d['request_id']));

// ---- 3. Validation failure -> 422 with Chinese field errors -------------
[$c, $r] = http('POST', "$base/api/v1/auth/register", json_encode([
    'username' => 'x', 'email' => 'bad', 'password' => '123',
]));
$d = json_decode($r, true);
check('bad register returns 422', $c === 422, "got $c");
check('422 has errors object', isset($d['errors']) && is_array($d['errors']));
check('422 messages are Chinese', isset($d['errors']['username']) && preg_match('/用户名/u', json_encode($d['errors'], JSON_UNESCAPED_UNICODE) ?: ''));

// ---- 4. Register success -> 201 -----------------------------------------
$uniq = 'smoke' . bin2hex(random_bytes(3));
[$c, $r] = http('POST', "$base/api/v1/auth/register", json_encode([
    'username' => $uniq, 'email' => "$uniq@test.com", 'password' => 'password123',
]));
$d = json_decode($r, true);
check('register returns 201', $c === 201, "got $c: $r");
$accessToken = $d['data']['access_token']['token'] ?? '';
$refreshToken = $d['data']['refresh_token']['token'] ?? '';
check('register returns access_token', $accessToken !== '');
check('register returns refresh_token', $refreshToken !== '');

// ---- 5. Login ------------------------------------------------------------
[$c, $r] = http('POST', "$base/api/v1/auth/login", json_encode([
    'email' => "$uniq@test.com", 'password' => 'password123',
]));
$d = json_decode($r, true);
check('login returns 200', $c === 200, "got $c: $r");
check('login returns user id', isset($d['data']['user']['id']));

// wrong password -> 401
[$c, $r] = http('POST', "$base/api/v1/auth/login", json_encode([
    'email' => "$uniq@test.com", 'password' => 'wrong-password',
]));
check('wrong password returns 401', $c === 401, "got $c");

// ---- 6. Protected route WITHOUT token -> 401 -----------------------------
[$c, $r] = http('GET', "$base/api/v1/auth/me");
check('GET /auth/me without token returns 401', $c === 401, "got $c");

// ---- 7. Protected route WITH token ---------------------------------------
[$c, $r] = http('GET', "$base/api/v1/auth/me", null, ["Authorization: Bearer $accessToken"]);
$d = json_decode($r, true);
check('GET /auth/me with token returns 200', $c === 200, "got $c: $r");
check('/auth/me returns the registered user', ($d['data']['username'] ?? '') === $uniq);

// ---- 8. Forged token -> 401 ------------------------------------------------
[$c, $r] = http('GET', "$base/api/v1/auth/me", null, ['Authorization: Bearer ' . $accessToken . 'xx']);
check('forged token returns 401', $c === 401, "got $c");

// ---- 9. Refresh flow --------------------------------------------------------
[$c, $r] = http('POST', "$base/api/v1/auth/refresh", json_encode(['refresh_token' => $refreshToken]));
$d = json_decode($r, true);
check('refresh returns 200', $c === 200, "got $c: $r");
check('refresh returns new access_token', isset($d['data']['access_token']['token']));

// ---- 10. Create article (JWT) -----------------------------------------------
[$c, $r] = http('POST', "$base/api/v1/articles", json_encode([
    'title' => '冒烟测试文章', 'body' => '这是冒烟测试的正文内容。',
]), ["Authorization: Bearer $accessToken"]);
$d = json_decode($r, true);
check('POST /articles returns 201', $c === 201, "got $c: $r");
$articleId = $d['data']['id'] ?? 0;
check('created article has Chinese title', ($d['data']['title'] ?? '') === '冒烟测试文章');

// ---- 11. Update article ------------------------------------------------------
[$c, $r] = http('PUT', "$base/api/v1/articles/$articleId", json_encode([
    'title' => '更新后的标题', 'body' => '更新后的正文。',
]), ["Authorization: Bearer $accessToken"]);
check('PUT /articles/{id} returns 200', $c === 200, "got $c: $r");

// ---- 12. Comment flow ----------------------------------------------------------
[$c, $r] = http('POST', "$base/api/v1/articles/$articleId/comments", json_encode([
    'content' => '冒烟测试评论',
]), ["Authorization: Bearer $accessToken"]);
$d = json_decode($r, true);
check('POST comment returns 201', $c === 201, "got $c: $r");
$commentId = $d['data']['id'] ?? 0;

[$c, $r] = http('GET', "$base/api/v1/articles/$articleId/comments");
$d = json_decode($r, true);
check('GET comments returns 200 with items', $c === 200 && isset($d['data']['items']), "got $c");

// ---- 13. Delete comment / article ------------------------------------------------
[$c, $r] = http('DELETE', "$base/api/v1/comments/$commentId", null, ["Authorization: Bearer $accessToken"]);
check('DELETE comment returns 200', $c === 200, "got $c: $r");

[$c, $r] = http('DELETE', "$base/api/v1/articles/$articleId", null, ["Authorization: Bearer $accessToken"]);
check('DELETE article returns 200', $c === 200, "got $c: $r");

// ---- 14. CORS preflight ------------------------------------------------------
[$c, $r, ] = http('OPTIONS', "$base/api/v1/articles");
check('OPTIONS preflight returns 200', $c === 200, "got $c");

echo "\n==============================\n";
echo "PASS: $pass  FAIL: $fail\n";
exit($fail > 0 ? 1 : 0);
