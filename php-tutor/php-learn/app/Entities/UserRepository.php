<?php

declare(strict_types=1);

namespace App\Entities;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * ============================================================
 * 用户仓储（Doctrine ORM 版）
 * ============================================================
 *
 * 对比 Java / Spring Data JPA：
 *   - Spring Data JPA 的 Repository 接口可直接写方法名派生查询；
 *   - Doctrine 的 EntityRepository 需要手写 DQL / Criteria；
 *   - 这里提供项目真正用到的几个查询方法。
 *
 * 为什么保留 Repository 层而不是直接在 Entity 里写静态方法？
 *   - 静态方法（Article::find）会绕过 Identity Map / 缓存 / Unit of Work，
 *     长期项目会越来越难维护；
 *   - Repository 让查询逻辑与实体解耦，方便做缓存、分页、只读副本。
 */
class UserRepository extends EntityRepository
{
    /**
     * 按 email 查（登录场景，email 唯一）
     *
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * 按 username 查
     *
     * @return User|null
     */
    public function findByUsername(string $username): ?User
    {
        return $this->findOneBy(['username' => $username]);
    }

    /**
     * 按 email 或 username 查（登录表单用户没区分时）
     *
     * @return User|null
     */
    public function findByLogin(string $login): ?User
    {
        return $this->findByEmail($login) ?? $this->findByUsername($login);
    }

    /**
     * 分页查询
     *
     * @return User[]
     */
    public function paginate(int $page = 1, int $perPage = 8): array
    {
        $query = $this->createQueryBuilder('u')
            ->orderBy('u.id', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery();

        return $query->getResult();
    }

    /** 总条数（签名不能叫 count()——与 EntityRepository::count(array $criteria) 冲突） */
    public function countAll(): int
    {
        $query = $this->createQueryBuilder('u')
            ->select('COUNT(u.id) AS cnt')
            ->getQuery();

        return (int) $query->getSingleScalarResult();
    }

    /**
     * 按 ID 删除用户（会级联删除该用户的 refresh_tokens：
     * 见 User#refreshTokens 的 cascade: ['persist', 'remove'] + orphanRemoval: true）
     *
     * @return int 受影响行数
     */
    public function deleteById(int $id): int
    {
        $query = $this->createQueryBuilder('u')
            ->delete()
            ->where('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery();

        return $query->execute();
    }
}