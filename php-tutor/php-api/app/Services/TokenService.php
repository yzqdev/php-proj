<?php
declare(strict_types=1);

namespace App\Services;

use App\Support\Env;
use App\Support\NotFoundException;
use DateTimeImmutable;
use DateTimeZone;
use DateInterval;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\ClaimsFormatter;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\Builder as TokenBuilder;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint;
use Lcobucci\JWT\Validation\ConstraintViolation;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Lcobucci\JWT\Validation\Validator;
use Throwable;

/**
 * JWT v5 TokenService — signs, parses and validates tokens.
 *
 * v5 API notes (hard constraint 3):
 *  - Build with `new Token\Builder(new JoseEncoder(), ChainedFormatter::default())`.
 *  - Serialize ONLY via `$token->toString()` — never `(string) $token`.
 *  - Validate via `(new Token\Parser(new JoseEncoder()))->parse()` + Validator
 *    with SignedWith / StrictValidAt constraints; StrictValidAt needs
 *    `new SystemClock(new DateTimeZone('UTC'))`.
 */
final class TokenService
{
    private const CLAIM_USER_ID = 'uid';
    private const CLAIM_TYPE    = 'typ';

    public const TYPE_ACCESS  = 'access';
    public const TYPE_REFRESH = 'refresh';

    private Signer $signer;
    private InMemory $key;
    /** @var array{secret:string,issuer:string,audience:string,access_ttl:int,refresh_ttl:int} */
    private array $config;

    public function __construct(?array $config = null)
    {
        $this->config = $config ?? [
            'secret'      => (string) Env::get('JWT_SECRET', 'change-me'),
            'issuer'      => (string) Env::get('JWT_ISSUER', 'php-tutor-api'),
            'audience'    => (string) Env::get('JWT_AUDIENCE', 'php-tutor-api'),
            'access_ttl'  => (int) Env::get('JWT_ACCESS_TTL', 3600),
            'refresh_ttl' => (int) Env::get('JWT_REFRESH_TTL', 604800),
        ];

        $this->signer = new Sha256();
        // Use the raw secret as key material (symmetric HMAC).
        $this->key = InMemory::plainText($this->config['secret']);
    }

    /**
     * Issue a signed token (access or refresh).
     *
     * @return array{token:string, expires_at:int, token_type:string}
     */
    public function issue(int $userId, string $type = self::TYPE_ACCESS): array
    {
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $ttl = $type === self::TYPE_REFRESH
            ? $this->config['refresh_ttl']
            : $this->config['access_ttl'];
        $expiresAt = $now->modify("+{$ttl} seconds");

        // v5: Token\Builder + ChainedFormatter (hard constraint 3).
        // NOTE: the v5 builder is immutable — every call returns a NEW
        // instance, so calls MUST be chained (or reassigned).
        $token = (new TokenBuilder(new JoseEncoder(), ChainedFormatter::default()))
            ->issuedBy($this->config['issuer'])
            ->permittedFor($this->config['audience'])
            ->identifiedBy(bin2hex(random_bytes(16)))
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($expiresAt)
            ->relatedTo((string) $userId)
            ->withClaim(self::CLAIM_USER_ID, $userId)
            ->withClaim(self::CLAIM_TYPE, $type)
            ->getToken($this->signer, $this->key);

        return [
            // v5: toString() only — never (string) cast.
            'token'      => $token->toString(),
            'expires_at' => $expiresAt->getTimestamp(),
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Parse and fully validate a token string.
     *
     * @return array{user_id:int, type:string, expires_at:int}
     * @throws NotFoundException When the token is invalid/expired/forged.
     */
    public function parseAndValidate(string $jwt): array
    {
        try {
            // v5: explicit parser with JoseEncoder.
            $parser = new Token\Parser(new JoseEncoder());
            $token = $parser->parse($jwt);

            $validator = new Validator();
            $clock = new SystemClock(new DateTimeZone('UTC'));

            $validator->assert(
                $token,
                new SignedWith($this->signer, $this->key),
                new StrictValidAt($clock),
                new PermittedFor($this->config['audience'])
            );
        } catch (RequiredConstraintsViolated | ConstraintViolation | Throwable $e) {
            throw NotFoundException::invalidToken($e);
        }

        $claims = $token->claims();
        $userId = (int) $claims->get(self::CLAIM_USER_ID);
        $type   = (string) $claims->get(self::CLAIM_TYPE, self::TYPE_ACCESS);
        $exp    = $claims->get(Token\RegisteredClaims::EXPIRATION_TIME);

        return [
            'user_id'    => $userId,
            'type'       => $type,
            'expires_at' => $exp instanceof DateTimeImmutable ? $exp->getTimestamp() : 0,
        ];
    }

    /**
     * Validate an access token and return the user id.
     */
    public function getUserIdFromAccessToken(string $jwt): int
    {
        $data = $this->parseAndValidate($jwt);
        if ($data['type'] !== self::TYPE_ACCESS) {
            throw NotFoundException::invalidToken();
        }
        return $data['user_id'];
    }

    /**
     * Validate a refresh token and return the user id.
     */
    public function getUserIdFromRefreshToken(string $jwt): int
    {
        $data = $this->parseAndValidate($jwt);
        if ($data['type'] !== self::TYPE_REFRESH) {
            throw NotFoundException::invalidToken();
        }
        return $data['user_id'];
    }
}
