<?php
declare(strict_types=1);

namespace App\Http;

/**
 * 分页结果值对象(不依赖 illuminate/pagination)。
 *
 * @template T
 */
final readonly class Pagination
{
    /**
     * @param list<T> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $perPage,
        public int $lastPage,
    ) {
    }
}
