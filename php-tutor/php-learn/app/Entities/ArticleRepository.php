<?php

declare(strict_types=1);

namespace App\Entities;

use Doctrine\ORM\EntityRepository;

/**
 * ============================================================
 * 文章仓储（Doctrine ORM 版）
 * ============================================================
 *
 * 对应 Java / Spring Data JPA：
 *   - Spring 里 findBy 方法名会派生查询；Doctrine 需要手写 DQL
 *   - 这里提供项目真正用到的几个查询方法
 */
class ArticleRepository extends EntityRepository
{
    /**
     * 分页 + 可选筛选
     *
     * @param int    $page     页码（1 起）
     * @param int    $perPage  每页条数
     * @param string $category 分类筛选（空串忽略）
     * @param string $status   状态筛选（空串忽略）
     *
     * @return Article[]
     */
    public function paginate(int $page, int $perPage, string $category = '', string $status = ''): array
    {
        $page = max(1, $page);
        $q = $this->createQueryBuilder('a')->orderBy('a.id', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults(max(1, $perPage));

        if ($category !== '') {
            $q->where('a.category = :category')->setParameter('category', $category);
        }
        if ($status !== '') {
            if ($q->where() !== null) {
                $q->andWhere('a.status = :status')->setParameter('status', $status);
            } else {
                $q->where('a.status = :status')->setParameter('status', $status);
            }
        }

        return $q->getQuery()->getResult();
    }

    /**
     * 与 paginate 的筛选条件匹配，返回总条数
     *
     * 签名不能叫 count()——与 EntityRepository::count(array $criteria) 冲突
     */
    public function countFiltered(string $category = '', string $status = ''): int
    {
        $q = $this->createQueryBuilder('a')->select('COUNT(a.id) AS cnt');

        if ($category !== '') {
            $q->where('a.category = :category')->setParameter('category', $category);
        }
        if ($status !== '') {
            if ($q->where() !== null) {
                $q->andWhere('a.status = :status')->setParameter('status', $status);
            } else {
                $q->where('a.status = :status')->setParameter('status', $status);
            }
        }

        return (int)$q->getQuery()->getSingleScalarResult();
    }
}
