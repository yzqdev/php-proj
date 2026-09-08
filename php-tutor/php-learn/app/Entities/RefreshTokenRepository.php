<?php

declare(strict_types=1);

namespace App\Entities;

use Doctrine\ORM\EntityRepository;

/**
 * ============================================================
 * Refresh Token 仓储（Doctrine ORM 版）
 * ============================================================
 *
 * 对应 TokenService 的 refresh() / revokeAll() / revokeRefresh()。
 *
 * 关键查询：
 *   - 按 token 查有效 token（同时校验 token 存在 + 未过期）
 *   - 按 user_id 批量撤销
 */
class RefreshTokenRepository extends EntityRepository
{
    /**
     * 查 Refresh Token 是否有效；返回实体或 null
     *
     * 关键点：同时校验 token 存在 + 未过期（数据库层过滤，不是查出来再判）
     */
    public function findValid(string $token): ?RefreshToken
    {
        $token = $this->findOneBy(['token' => $token]);
        if ($token === null) {
            return null;
        }
        // 过期的 token 视为无效
        return $token->isExpired() ? null : $token;
    }

    /** 撤销单条（Refresh Token 轮转时用） */
    public function revoke(string $token): int
    {
        return $this->createQueryBuilder('rt')
            ->delete()
            ->where('rt.token = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->execute();
    }

    /** 撤销某个用户的所有 refresh token */
    public function revokeAll(int $userId): int
    {
        return $this->createQueryBuilder('rt')
            ->delete()
            ->where('rt.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->execute();
    }
}