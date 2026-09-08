<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\RefreshToken;
use App\Entities\User;
use App\Exceptions\BusinessException;
use App\Helpers\Config;
use Doctrine\ORM\EntityManager;

/**
 * ============================================================
 * Token 服务：Access (JWT) + Refresh (DB) 双令牌
 * ============================================================
 *
 * 为什么不只用 JWT？
 *   - JWT 是"无状态签名"，服务端无法主动撤销（过期前一直有效）；
 *   - 密码泄露 / 账号被盗 / 用户改密码后，旧 token 应立刻失效；
 *   - 解法：**Access Token 短过期（15 分钟）+ Refresh Token 存表（7 天）**。
 *     Access 过期后前端用 Refresh 换新的 Access；Refresh 可被服务端撤销。
 *
 * 对应 Java / Spring Security：
 *   - Spring Security 的 OAuth2 Resource Server 也是 access + refresh 双令牌；
 *   - Passport 用完整 OAuth2 流程。
 *
 * 依赖：EntityManager（读写 RefreshToken Entity）+ Jwt（签发/校验 Access Token）
 */
final class TokenService
{
    public function __construct(
        private readonly EntityManager $em,
        private readonly Jwt           $jwt,
    ) {
    }

    /**
     * 登录成功后签发一对 Token
     *
     * @return array{
     *     accessToken: string,
     *     refreshToken: string,
     *     tokenType: string,
     *     expiresIn: int,
     *     user: array{id:int, username:string, email:string, role:string}
     * }
     */
    public function issue(User $user): array
    {
        $accessTtl  = (int)Config::get('api.jwt.access_ttl', 900);
        $refreshTtl = (int)Config::get('api.jwt.refresh_ttl', 604800);

        // 1) 签发 Access Token（JWT，15 分钟）
        //    载荷里塞最小必要信息：id/username/role（前端不用每次调 /me 就能渲染顶部栏）
        $accessToken = $this->jwt->issue([
            'sub'      => (string)$user->getId(),
            'userId'   => $user->getId(),
            'username' => $user->getUsername(),
            'role'     => $user->getRole(),
        ], $accessTtl);

        // 2) 签发 Refresh Token（随机 hex，7 天，存表可撤销）
        $refreshToken = self::generateRandomToken();
        $this->storeRefreshToken($user, $refreshToken, $refreshTtl);

        return [
            'accessToken'  => $accessToken,
            'refreshToken' => $refreshToken,
            'tokenType'    => 'Bearer',
            'expiresIn'    => $accessTtl,
            'user'         => [
                'id'       => $user->getId(),
                'username' => $user->getUsername(),
                'email'    => $user->getEmail(),
                'role'     => $user->getRole(),
            ],
        ];
    }

    /**
     * 用 Refresh Token 换新的 Access Token（同时轮转 refresh token）
     *
     * 为什么"轮转"？
     *   - Refresh Token 泄露后攻击者能用它换 access；
     *   - 每次 refresh 后**旧 refresh 立即失效**，攻击者用旧的会被拒绝；
     *   - 这是行业标准做法（Google Auth、Discord API 都这样）。
     *
     * @throws BusinessException 刷新令牌无效或已过期
     */
    public function refresh(string $refreshToken): array
    {
        $repo    = $this->em->getRepository(RefreshToken::class);
        $valid   = $repo->findValid($refreshToken);
        if ($valid === null) {
            throw new BusinessException('刷新令牌无效或已过期', 40102, 401);
        }

        $userId = $valid->getUser()->getId();

        // 撤销旧的 refresh token（强制轮转）
        $repo->revoke($refreshToken);

        // 查用户当前资料（可能改过 role/username）
        $user = $this->em->find(User::class, $userId);
        if ($user === null) {
            throw new BusinessException('用户不存在', 40103, 401);
        }

        $accessTtl  = (int)Config::get('api.jwt.access_ttl', 900);
        $refreshTtl = (int)Config::get('api.jwt.refresh_ttl', 604800);

        $accessToken = $this->jwt->issue([
            'sub'      => (string)$user->getId(),
            'userId'   => $user->getId(),
            'username' => $user->getUsername(),
            'role'     => $user->getRole(),
        ], $accessTtl);

        $newRefreshToken = self::generateRandomToken();
        $this->storeRefreshToken($user, $newRefreshToken, $refreshTtl);

        return [
            'accessToken'  => $accessToken,
            'refreshToken' => $newRefreshToken,
            'tokenType'    => 'Bearer',
            'expiresIn'    => $accessTtl,
        ];
    }

    /**
     * 校验 Access Token 并返回用户信息（不查库，从 JWT 载荷读）
     *
     * 对应 Java：Jwts.parser().parseClaimsJws(token).getBody()
     *
     * @throws BusinessException 令牌无效
     * @return array{userId:int, username:string, role:string}
     */
    public function verifyAccess(string $accessToken): array
    {
        try {
            $claims = $this->jwt->verify($accessToken);
        } catch (\Throwable $e) {
            throw new BusinessException('访问令牌无效或已过期', 40102, 401);
        }

        // 从载荷里取必需字段（防御式：任何缺失都当无效）
        return [
            'userId'   => (int)($claims['userId'] ?? 0),
            'username' => (string)($claims['username'] ?? ''),
            'role'     => (string)($claims['role'] ?? ''),
        ];
    }

    /**
     * 登出：撤销该用户的所有 refresh token
     */
    public function revokeAll(int $userId): int
    {
        return $this->em->getRepository(RefreshToken::class)->revokeAll($userId);
    }

    /** 单条撤销（Refresh Token 轮转时用） */
    public function revokeRefresh(string $refreshToken): int
    {
        return $this->em->getRepository(RefreshToken::class)->revoke($refreshToken);
    }

    // =====================================================================
    // 内部方法
    // =====================================================================

    /**
     * 生成 32 字节随机 hex（64 字符）作为 Refresh Token
     *
     * random_bytes() 使用 CSPRNG，比 rand() / mt_rand() 安全得多。
     */
    private static function generateRandomToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /** 存 Refresh Token 到 DB（Entity + Unit of Work） */
    private function storeRefreshToken(User $user, string $token, int $ttlSeconds): void
    {
        $expiresAt = new \DateTimeImmutable("+{$ttlSeconds} seconds");

        $entity = new RefreshToken();
        $entity->setUser($user)
               ->setToken($token)
               ->setExpiresAt($expiresAt);

        $this->em->persist($entity);
        $this->em->flush();
    }
}
