<?php

declare(strict_types=1);

namespace App\Auth;

use Predis\ClientInterface;

/**
 * Bearer Token 管理（Redis 实现）�?
 *
 * 键设计：auth:token:{sha256(token)} �?user_id，SETEX 7 天�?
 * - �?token 摘要作键而非原文：即�?Redis 数据被读取，也无法反推出可用凭证�?
 * - 过期交给 Redis 原生 TTL（到期自动删除），替代此�?JSON 文件方案的遍历清理；
 * - 允许多端同时在线（每次登录签发新 token，旧 token 不失效）�?
 */
final class TokenStore
{
    private const KEY_PREFIX = 'auth:token:';

    private const TTL = 7 * 24 * 3600;

    public function __construct(
        private readonly ClientInterface $redis,
    ) {
    }

    /**
     * 为主体签发新 token（subject 由调用方提供，如 "json:2" / "orm:3"�?
     * 用于区分 JSON 用户�?ORM 用户�?ID 空间）�?
     */
    public function issue(string $subject): string
    {
        $token = bin2hex(random_bytes(32));
        $this->redis->setex(
            self::KEY_PREFIX . hash('sha256', $token),
            self::TTL,
            $subject,
        );

        return $token;
    }

    /**
     * 校验 token，返�?subject（如 "json:2" / "orm:3"）；无效或过期返�?null�?
     */
    public function resolve(string $token): ?string
    {
        $subject = $this->redis->get(self::KEY_PREFIX . hash('sha256', $token));

        return $subject === null ? null : (string) $subject;
    }

    /**
     * 注销：删�?token；不存在也视为成功（幂等）�?
     */
    public function revoke(string $token): void
    {
        $this->redis->del(self::KEY_PREFIX . hash('sha256', $token));
    }
}
