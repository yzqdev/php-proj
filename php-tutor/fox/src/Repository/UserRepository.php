<?php

declare(strict_types=1);

namespace Yzqde\Fox\Repository;

use Doctrine\ORM\EntityRepository;
use Yzqde\Fox\Entity\User;

class UserRepository extends EntityRepository
{
    /**
     * 按名称模糊搜索用户。
     *
     * @return User[]
     */
    public function searchByName(string $keyword): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.name LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%')
            ->orderBy('u.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 按邮箱查找单个用户。
     */
    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }
}
