<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\File;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * 文件记录数据访问层
 *
 * 基于 Doctrine ORM，封装 files 表的增删查操作。
 * 替代原有的原生 PDO 实现。
 */
final class FileRepository extends EntityRepository
{
    /** 列表返回条数的硬上限 */
    private const MAX_LIMIT = 500;

    /**
     * 获取最近上传的文件列表（按 ID 降序）
     *
     * @param int $limit 返回条数上限
     *
     * @return File[] 文件对象数组
     */
    public function findAllFiles(int $limit = 100): array
    {
        $limit = max(1, min($limit, self::MAX_LIMIT));

        return $this->createQueryBuilder('f')
            ->orderBy('f.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * 按 ID 查找文件记录
     *
     * @param int $id 文件记录 ID
     *
     * @return File|null 找到返回 File 实例，不存在返回 null
     */
    public function findById(int $id): ?File
    {
        return $this->find($id);
    }

    /**
     * 新增文件记录
     *
     * @param File $file 待持久化的文件实体（ID 为 null）
     *
     * @return File 持久化后的文件实体（ID 与 created_at 已填充）
     */
    public function create(File $file): File
    {
        $this->getEntityManager()->persist($file);
        $this->getEntityManager()->flush();

        return $file;
    }

    /**
     * 删除文件记录
     *
     * @param int $id 文件记录 ID
     *
     * @return bool 是否成功删除（不存在时返回 false）
     */
    public function delete(int $id): bool
    {
        $file = $this->find($id);
        if ($file === null) {
            return false;
        }

        $this->getEntityManager()->remove($file);
        $this->getEntityManager()->flush();

        return true;
    }

    /**
     * 分页查询文件
     *
     * @param int $page     页码（从 1 开始）
     * @param int $pageSize 每页条数
     *
     * @return array{ data: File[], total: int, page: int, pageSize: int, lastPage: int }
     */
    public function paginate(int $page = 1, int $pageSize = 20): array
    {
        $page = max(1, $page);
        $pageSize = max(1, min($pageSize, 100));

        $query = $this->createQueryBuilder('f')
            ->orderBy('f.id', 'DESC')
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