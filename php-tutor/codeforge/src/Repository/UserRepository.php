<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * 用户数据访问层
 *
 * 基于 Doctrine ORM，封装 users 表的 CRUD 操作。
 * 替代原有的原生 PDO + Redis 缓存实现。
 */
final class UserRepository extends EntityRepository
{
    /**
     * 获取全部用户（按 ID 升序）
     *
     * @return User[] 用户对象数组
     */
    public function findAllUsers(): array
    {
        return $this->findBy([], ['id' => 'ASC']);
    }

    /**
     * 按 ID 查找用户
     *
     * @param int $id 用户 ID
     *
     * @return User|null 找到返回 User 实例，不存在返回 null
     */
    public function findById(int $id): ?User
    {
        return $this->find($id);
    }

    /**
     * 创建新用户
     *
     * @param User $user 待持久化的用户实体（ID 为 null）
     *
     * @return User 持久化后的用户实体（ID 已分配）
     */
    public function create(User $user): User
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return $user;
    }

    /**
     * 更新已有用户
     *
     * @param User $user 包含新值的用户实体
     *
     * @return User 更新后的用户实体
     */
    public function update(User $user): User
    {
        $this->getEntityManager()->flush();

        return $user;
    }

    /**
     * 删除用户
     *
     * @param int $id 用户 ID
     *
     * @return bool 是否成功删除
     */
    public function delete(int $id): bool
    {
        $user = $this->find($id);
        if ($user === null) {
            return false;
        }

        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * 分页查询用户
     *
     * @param int $page     页码（从 1 开始）
     * @param int $pageSize 每页条数
     *
     * @return array{ data: User[], total: int, page: int, pageSize: int, lastPage: int }
     */
    public function paginate(int $page = 1, int $pageSize = 20): array
    {
        $page = max(1, $page);
        $pageSize = max(1, min($pageSize, 100));

        $query = $this->createQueryBuilder('u')
            ->orderBy('u.id', 'ASC')
            ->setFirstResult(($page - 1) * $pageSize)
            ->setMaxResults($pageSize)
            ->getQuery();

        $paginator = new Paginator($query, fetchJoinCollection: true);
        $total = count($paginator);
        $lastPage = (int) ceil($total / $pageSize);

        return [
            'data'     => $paginator->getIterator()->getArrayCopy(),
            'total'    => $total,
            'page'     => $page,
            'pageSize' => $pageSize,
            'lastPage' => $lastPage,
        ];
    }
}