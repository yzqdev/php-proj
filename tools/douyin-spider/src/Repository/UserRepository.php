<?php

declare(strict_types=1);

namespace Yzqde\DouyinSpider\Repository;

use Doctrine\ORM\EntityRepository;
use Yzqde\DouyinSpider\Model\User;

/**
 * @method User|null find($id)
 * @method User|null findOneBy(array $criteria, ?array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null)
 */
class UserRepository extends EntityRepository
{
    public function findByUsername(string $username): ?User
    {
        return $this->findOneBy(['username' => $username]);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * 分页获取用户列表
     */
    public function findPaginated(int $page = 1, int $limit = 20): array
    {
        $qb = $this->createQueryBuilder('u')
            ->orderBy('u.createdAt', 'DESC');

        $offset = ($page - 1) * $limit;
        $qb->setFirstResult($offset)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function countAll(): int
    {
        return (int) $this->count([]);
    }
}
