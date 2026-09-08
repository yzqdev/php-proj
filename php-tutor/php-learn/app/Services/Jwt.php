<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\Config;

/**
 * ============================================================
 * JWT (JSON Web Token) 手写实现 —— HS256
 * ============================================================
 *
 * 为什么不引 Composer 依赖（firebase/php-jwt）？
 *   - 本项目是"无框架学习 PHP"，JWT 的核心其实就 3 行：
 *       header.payload.signature = base64(header).base64(payload).hmac(base64(header).base64(payload))
 *   - 手写一遍能彻底看清"JWT 只是三段 base64 拼起来，签名用 HMAC"，
 *     比调 API 库更有价值。
 *
 * 对应 Java / Spring Security：
 *   - io.jsonwebtoken.Jwts.builder()...signWith(secretKey)
 *   - Jwts.parserBuilder().setSigningKey(secretKey).build().parseClaimsJws(token)
 *
 * PHP 8.x 特性：
 *   - readonly 属性：类构造后签名算法、密钥都不能改；
 *   - named arguments：签发时用 Jwt::issue(secret: $secret, ttl: 900, payload: [...])
 *   - static:: / self:: 区别（此处不需要延迟到子类，都用 self）。
 *
 * 安全性说明（务必了解）：
 *   - HS256 用 HMAC-SHA256 签名，密钥必须 ≥ 256 bit（32 字节）。
 *     本项目的密钥用 base64_encode(random_bytes(32)) 生成，长度足够。
 *   - JWT 是**无状态签名**：服务端不用查库就能验签。
 *     代价是无法主动撤销（过期前一直有效）。本项目用"短 access + refresh"缓解。
 *   - 不要修改 header 里的 alg 字段：某些库如果没校验 alg，攻击者可能把 HS256 换成
 *     none / RS256。本实现的 verify() 里**显式校验 alg === 'HS256'**，拒绝任何其他算法。
 */
final class Jwt
{
    public const ALG = 'HS256';

    public function __construct(
        private readonly string $secret,
        private readonly string $issuer,
        private readonly string $audience,
    ) {
        if ($secret === '' || $secret === 'REPLACE_WITH_YOUR_BASE64_SECRET') {
            throw new \RuntimeException('JWT secret 未配置：请在 config.php 的 api.jwt.secret 填入真实密钥');
        }
    }

    /** 从全局配置构造（Controller / Service 里直接用这个静态工厂） */
    public static function make(): self
    {
        return new self(
            secret: (string)Config::get('api.jwt.secret', ''),
            issuer: (string)Config::get('api.jwt.issuer', 'php-learn'),
            audience: (string)Config::get('api.jwt.audience', 'php-learn-web'),
        );
    }

    /**
     * 签发 Token
     *
     * @param array<string,mixed> $payload 载荷（会附加 iat / exp / iss / aud / jti）
     * @param int $ttlSeconds 有效期秒数（0 表示永不过期，不推荐）
     */
    public function issue(array $payload, int $ttlSeconds): string
    {
        $now = time();
        $claims = [
            'iat' => $now,
            'exp' => $ttlSeconds > 0 ? $now + $ttlSeconds : null,
            'iss' => $this->issuer,
            'aud' => $this->audience,
            'jti' => bin2hex(random_bytes(16)), // 唯一 ID，方便撤销
        ] + $payload;

        // 移除 null 值（exp 永不过期时不写）
        $claims = array_filter($claims, static fn($v) => $v !== null);

        $header = ['typ' => 'JWT', 'alg' => self::ALG];

        $headerB64  = self::base64UrlEncode((string)json_encode($header, JSON_UNESCAPED_SLASHES));
        $payloadB64 = self::base64UrlEncode((string)json_encode($claims, JSON_UNESCAPED_SLASHES));

        $signature = $this->sign("{$headerB64}.{$payloadB64}");

        return "{$headerB64}.{$payloadB64}.{$signature}";
    }

    /**
     * 校验 Token 并返回载荷；失败抛 \RuntimeException（业务层捕获转 401）
     *
     * @return array<string,mixed> 载荷
     */
    public function verify(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new \RuntimeException('Token 格式错误：期望 3 段');
        }
        [$headerB64, $payloadB64, $signatureB64] = $parts;

        // 1) 校验算法（防 alg=none 攻击）
        $header = self::base64UrlDecode($headerB64);
        if ($header === false || !str_starts_with($header, '{')) {
            throw new \RuntimeException('Token header 无效');
        }
        $header = json_decode($header, true);
        if (!is_array($header) || ($header['alg'] ?? '') !== self::ALG) {
            throw new \RuntimeException('Token 算法不受支持');
        }

        // 2) 校验签名（hash_equals 常量时间比较，防时序攻击）
        $expected = $this->sign("{$headerB64}.{$payloadB64}");
        if (!hash_equals($expected, $signatureB64)) {
            throw new \RuntimeException('Token 签名不匹配');
        }

        // 3) 解出载荷
        $payload = self::base64UrlDecode($payloadB64);
        if ($payload === false || !str_starts_with($payload, '{')) {
            throw new \RuntimeException('Token 载荷无效');
        }
        $claims = json_decode($payload, true);
        if (!is_array($claims)) {
            throw new \RuntimeException('Token 载荷 JSON 解析失败');
        }

        // 4) 校验过期时间
        if (isset($claims['exp']) && $claims['exp'] < time()) {
            throw new \RuntimeException('Token 已过期');
        }

        // 5) 校验签发方（可选，生产环境建议打开）
        if (($claims['iss'] ?? '') !== $this->issuer) {
            throw new \RuntimeException('Token 签发方不匹配');
        }

        return $claims;
    }

    /**
     * HMAC-SHA256 签名 + base64url 编码
     *
     * PHP 特性：hash_hmac($algo, $data, $key, true) 第 4 参数 true 表示返回原始二进制。
     *          然后 base64urlEncode 把它转成 URL 安全字符串。
     */
    private function sign(string $data): string
    {
        return self::base64UrlEncode(hash_hmac('sha256', $data, $this->secret, true));
    }

    /**
     * Base64 URL-safe 编码
     *
     * 标准 base64 含 + / = 三个特殊字符，URL 里会被误解析。
     * JWT 用 url-safe 版本：+ → -，/ → _，去掉末尾 =。
     * 对应 Java：DatatypeConverter / Base64.getUrlEncoder().withoutPadding()
     */
    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL-safe 解码（失败返回 false，不抛异常）
     */
    private static function base64UrlDecode(string $data): string|false
    {
        // 补齐被去掉的 = 填充
        $mod = strlen($data) % 4;
        if ($mod > 0) {
            $data .= str_repeat('=', 4 - $mod);
        }
        return base64_decode(strtr($data, '-_', '+/'), true);
    }
}
