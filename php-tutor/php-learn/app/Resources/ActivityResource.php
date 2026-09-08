<?php

declare(strict_types=1);

namespace App\Resources;

use App\Entities\Activity;

/**
 * ============================================================
 * 活动 API Resource（序列化层）
 * ============================================================
 *
 * 输入：Doctrine Entity（App\Entities\Activity）
 * 输出：array（camelCase + 格式化后的时间字符串）
 *
 * 时间字段统一 'Y-m-d H:i:s' 格式，与旧的手写 BaseModel 返回保持一致，
 * 前端不需要改。
 */
class ActivityResource
{
    private function __construct()
    {
    }

    /**
     * 序列化单条活动
     *
     * @return array|null
     */
    public static function make(Activity|null $activity): ?array
    {
        if ($activity === null) {
            return null;
        }

        return [
            'id'          => $activity->getId(),
            'title'       => $activity->getTitle(),
            'location'    => $activity->getLocation(),
            'startTime'   => self::formatDate($activity->getStartTime()),
            'endTime'     => self::formatDate($activity->getEndTime()),
            'description' => $activity->getDescription(),
            'createdAt'   => self::formatDate($activity->getCreatedAt()),
            'updatedAt'   => self::formatDate($activity->getUpdatedAt()),
        ];
    }

    /**
     * 批量序列化
     *
     * @param Activity[] $items
     */
    public static function collection(array $items): array
    {
        return array_map(self::make(...), $items);
    }

    /** 把 DateTimeImmutable 格式化成 'Y-m-d H:i:s' */
    private static function formatDate(?\DateTimeInterface $dt): string
    {
        return $dt === null ? '' : $dt->format('Y-m-d H:i:s');
    }
}
