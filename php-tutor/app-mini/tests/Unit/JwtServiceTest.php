<?php
declare(strict_types=1);

namespace Tests\Unit;

use DateTimeImmutable;
use DateTimeZone;
use Lcobucci\Clock\FrozenClock;
use Lcobucci\JWT\UnencryptedToken;
use PHPUnit\Framework\TestCase;
use App\Security\JwtService;

/**
 * JwtService 单元测试:签发/校验/过期/篡改/签发者。
 */
final class JwtServiceTest extends TestCase
{
    private const SECRET = 'unit-test-secret-key-0123456789abcdef';

    private const ISSUER = 'blog-api-test';

    private function service(DateTimeImmutable $now): JwtService
    {
        return new JwtService(self::SECRET, self::ISSUER, 3600, new FrozenClock($now));
    }

    public function testIssueAndValidateReturnsTrueWithUserId(): void
    {
        $now = new DateTimeImmutable('2025-01-01 00:00:00', new DateTimeZone('UTC'));
        $service = $this->service($now);

        $token = $service->issue(42);
        $parsed = $service->parse($token);

        self::assertInstanceOf(UnencryptedToken::class, $parsed);
        self::assertTrue($service->validate($parsed));
        self::assertSame(42, $service->userIdFromToken($parsed));
    }

    public function testExpiredTokenFailsValidation(): void
    {
        $issuedAt = new DateTimeImmutable('2025-01-01 00:00:00', new DateTimeZone('UTC'));
        $service = $this->service($issuedAt);
        $tokenString = $service->issue(7);

        // 时钟前移超过 TTL 后校验应失败
        $expiredService = new JwtService(self::SECRET, self::ISSUER, 3600, new FrozenClock($issuedAt->modify('+7200 seconds')));
        $parsed = $expiredService->parse($tokenString);

        self::assertFalse($expiredService->validate($parsed));
    }

    public function testTamperedTokenFailsValidation(): void
    {
        $service = $this->service(new DateTimeImmutable('2025-01-01 00:00:00', new DateTimeZone('UTC')));
        $token = $service->issue(1);

        $parts = explode('.', $token);
        $payload = json_decode($this->base64UrlDecode($parts[1]), true);
        $payload['uid'] = 999;
        $parts[1] = $this->base64UrlEncode((string) json_encode($payload));

        $parsed = $service->parse(implode('.', $parts));
        self::assertFalse($service->validate($parsed));
    }

    public function testTokenFromOtherSecretFailsValidation(): void
    {
        $issuedAt = new DateTimeImmutable('2025-01-01 00:00:00', new DateTimeZone('UTC'));
        $other = new JwtService('another-secret-key-abcdef-1234567890', 'blog-api-test', 3600, new FrozenClock($issuedAt));
        $token = $other->issue(1);

        $parsed = $this->service($issuedAt)->parse($token);
        self::assertFalse($this->service($issuedAt)->validate($parsed));
    }

    public function testEmptySecretThrows(): void
    {
        $this->expectException(\RuntimeException::class);
        new JwtService('', self::ISSUER, 3600, new FrozenClock(new DateTimeImmutable('now', new DateTimeZone('UTC'))));
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder !== 0) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return (string) base64_decode(strtr($data, '-_', '+/'));
    }
}
