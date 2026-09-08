<?php
declare(strict_types=1);

namespace App\Security;

use Lcobucci\Clock\Clock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use RuntimeException;

/**
 * JWT(HS256)签发、解析与校验。
 * 使用 lcobucci/jwt 5.x 的 Configuration / Signer / Validator API,
 * 时钟由 lcobucci/clock 注入,便于测试。
 */
final class JwtService
{
    private readonly Configuration $configuration;

    public function __construct(
        private readonly string $secret,
        private readonly string $issuer,
        private readonly int $ttlSeconds,
        private readonly Clock $clock,
    ) {
        if ($secret === '') {
            throw new RuntimeException('JWT_SECRET 不能为空');
        }

        $this->configuration = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($secret),
        );
    }

    /** 为指定用户签发 access token(HS256)。 */
    public function issue(int $userId): string
    {
        $now = $this->clock->now();
        $expiresAt = $now->modify(sprintf('+%d seconds', $this->ttlSeconds));
        assert($expiresAt !== false);

        $token = $this->configuration->builder()
            ->issuedBy($this->issuer)
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($expiresAt)
            ->withClaim('uid', $userId)
            ->getToken($this->configuration->signer(), $this->configuration->signingKey());

        return $token->toString();
    }

    /** 解析 token 字符串;格式非法时抛出异常。 */
    public function parse(string $token): UnencryptedToken
    {
        $parsed = $this->configuration->parser()->parse($token);
        if (!$parsed instanceof UnencryptedToken) {
            throw new RuntimeException('Token 不是非加密 JWT');
        }

        return $parsed;
    }

    /** 校验签名、签发者与有效期。 */
    public function validate(UnencryptedToken $token): bool
    {
        return $this->configuration->validator()->validate($token, ...$this->constraints());
    }

    /** 从 token 中读取用户 id。 */
    public function userIdFromToken(UnencryptedToken $token): int
    {
        return (int) $token->claims()->get('uid');
    }

    /** @return list<object> */
    private function constraints(): array
    {
        return [
            new SignedWith($this->configuration->signer(), $this->configuration->verificationKey()),
            new IssuedBy($this->issuer),
            new StrictValidAt($this->clock),
        ];
    }
}
