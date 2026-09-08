<?php

declare(strict_types=1);

namespace Yzqde\DouyinSpider\Repository;

use Doctrine\ORM\EntityRepository;
use Yzqde\DouyinSpider\Model\Article;

/**
 * @method Article|null find($id)
 * @method Article|null findOneBy(array $criteria, ?array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null)
 */
class ArticleRepository extends EntityRepository
{
    /**
     * 分页查询文章列表，预加载作者信息
     */
    public function findPaginated(int $page = 1, int $limit = 20): array
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.author', 'au')
            ->addSelect('au')
            ->orderBy('a.createdAt', 'DESC');

        $offset = ($page - 1) * $limit;
        $qb->setFirstResult($offset)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function countAll(): int
    {
        return (int) $this->count([]);
    }

    public function findByAuthor(int $authorId, int $page = 1, int $limit = 20): array
    {
        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.author', 'au')
            ->addSelect('au')
            ->where('a.author = :authorId')
            ->setParameter('authorId', $authorId)
            ->orderBy('a.createdAt', 'DESC');

        $offset = ($page - 1) * $limit;
        $qb->setFirstResult($offset)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }
}
