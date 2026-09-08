<?php

declare(strict_types=1);

namespace App\Entities;

use Doctrine\ORM\EntityRepository;

/**
 * ============================================================
 * 活动仓储（Doctrine ORM 版）
 * ============================================================
 *
 * 关键查询：
 *   - paginate(upcoming): upcoming=true 时只返回 start_time >= NOW()
 *   - countFiltered: 与筛选条件匹配的总条数
 */
class ActivityRepository extends EntityRepository
{
    /**
     * 分页查询活动
     *
     * @param int    $page     页码（1 起）
     * @param int    $perPage  每页条数
     * @param bool   $upcoming true 时只返回开始时间 ≥ 当前时间（升序，最近排前面）
     *
     * @return Activity[]
     */
    public function paginate(int $page, int $perPage, bool $upcoming = false): array
    {
        $page = max(1, $page);
        $q = $this->createQueryBuilder('a');

        if ($upcoming) {
            // 用 CURRENT_TIMESTAMP 让 DB 自己比较，跨时区也正确
            $q->where('a.startTime >= CURRENT_TIMESTAMP')->orderBy('a.startTime', 'ASC');
        } else {
            $q->orderBy('a.id', 'DESC');
        }

        $q->setFirstResult(($page - 1) * $perPage)->setMaxResults(max(1, $perPage));

        return $q->getQuery()->getResult();
    }

    /**
     * 与 paginate 的筛选条件匹配，返回总条数
     *
     * 签名不能叫 count()——与 EntityRepository::count(array $criteria) 冲突
     */
    public function countFiltered(bool $upcoming = false): int
    {
        $q = $this->createQueryBuilder('a')->select('COUNT(a.id) AS cnt');
        if ($upcoming) {
            $q->where('a.startTime >= CURRENT_TIMESTAMP');
        }
        return (int)$q->getQuery()->getSingleScalarResult();
    }
}
