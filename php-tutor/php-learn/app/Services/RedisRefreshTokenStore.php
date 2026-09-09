<?php

declare(strict_types=1);

namespace App\Services;

use Predis\Client;

/**
 * ============================================================
 * Refresh Token 存储 — Redis 版
 * ============================================================
 *
 * 替代原来的 Doctrine RefreshTokenRepository + MySQL 表。
 *
 * Redis Key 设计：
 *   - refresh_token:{token}  → JSON {userId, expiresAt}，TTL = refresh_ttl
 *   - refresh_tokens:user:{userId} → Set（记录该用户所有 token，用于 revokeAll）
 *
 * 对比 MySQL 方案：
 *   - 无磁盘 IO，纯内存操作，延迟从 ~2ms 降到 ~0.1ms
 *   - TTL 由 Redis 自动管理，无需定时清理过期行
 *   - revokeAll 通过 Set 批量删除，无需全表扫描
 */
final class RedisRefreshTokenStore
{
    public function __construct(
        private readonly Client $redis,
        private readonly string $prefix = 'learn.refresh_token:',
        private readonly string $userPrefix = 'learn.refresh_tokens:user:',
    ) {
    }

    /**
     * 存储 Refresh Token
     *
     * @param int    $userId      用户 ID
     * @param string $token       refresh token 字符串
     * @param int    $ttlSeconds  过期秒数
     */
    public function store(int $userId, string $token, int $ttlSeconds): void
    {
        $key = $this->prefix . $token;
        $data = json_encode([
            'userId'    => $userId,
            'expiresAt' => time() + $ttlSeconds,
        ], JSON_UNESCAPED_SLASHES);

        // Pipeline：同时写 token key + 用户 token 集合
        $pipeline = $this->redis->pipeline();
        $pipeline->setex($key, $ttlSeconds, $data);
        $pipeline->sadd($this->userPrefix . $userId, [$token]);
        // 用户集合也设 TTL（比 refresh_ttl 多 1 天 buffer，防止泄漏）
        $pipeline->expire($this->userPrefix . $userId, $ttlSeconds + 86400);
        $pipeline->execute();
    }

    /**
     * 查找有效的 Refresh Token
     *
     * @return array{userId: int}|null 有效则返回用户信息，无效返回 null
     */
    public function findValid(string $token): ?array
    {
        $key = $this->prefix . $token;
        $raw = $this->redis->get($key);

        if ($raw === null) {
            return null;
        }

        $data = json_decode($raw, true);
        if (!is_array($data) || !isset($data['userId'], $data['expiresAt'])) {
            return null;
        }

        // 双重校验：Redis TTL 可能还没清理，手动再判一次
        if ($data['expiresAt'] < time()) {
            $this->revoke($token);
            return null;
        }

        return ['userId' => (int)$data['userId']];
    }

    /**
     * 撤销单条 Refresh Token（轮转时用）
     */
    public function revoke(string $token): int
    {
        // 先读出 userId，再删 user set 里的成员
        $key = $this->prefix . $token;
        $raw = $this->redis->get($key);

        $deleted = $this->redis->del([$key]);

        if ($raw !== null) {
            $data = json_decode($raw, true);
            if (isset($data['userId'])) {
                $this->redis->srem($this->userPrefix . $data['userId'], [$token]);
            }
        }

        return $deleted;
    }

    /**
     * 撤销某个用户的所有 Refresh Token
     */
    public function revokeAll(int $userId): int
    {
        $userKey = $this->userPrefix . $userId;
        $tokens = $this->redis->smembers($userKey);

        if (empty($tokens)) {
            return 0;
        }

        // Pipeline 批量删除
        $pipeline = $this->redis->pipeline();
        foreach ($tokens as $token) {
            $pipeline->del([$this->prefix . $token]);
        }
        $pipeline->del([$userKey]);
        $results = $pipeline->execute();

        // 结果数组最后一个是我们要的 count（del user key 返回 0 或 1）
        return count($tokens);
    }
}
