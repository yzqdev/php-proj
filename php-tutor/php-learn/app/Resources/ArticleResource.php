<?php

declare(strict_types=1);

namespace App\Resources;

use App\Entities\Article;

/**
 * ============================================================
 * 文章 API Resource（序列化层）
 * ============================================================
 *
 * 为什么必须经过 Resource 层？
 *   - 数据库字段是 snake_case，前端要 camelCase；
 *   - 敏感字段（如 password）统一剔除；
 *   - 计算字段（如 categoryLabel）集中处理；
 *   - 数据库改字段名时只需改 Resource，前端不感知。
 *
 * 对应 Java：
 *   - Spring 的 @JsonSerialize + JsonSerializer
 *   - JPA 的 @JsonProperty("camelCase")
 *   - Laravel 的 App\Http\Resources\ArticleResource
 *
 * 输入：Doctrine Entity（App\Entities\Article）
 * 输出：array（camelCase + 格式化后的时间字符串）
 */
class ArticleResource
{
    /**
     * 分类的中文标签（数据库是 'php'，API 返回 'PHP'）
     * 前端展示时不用再翻字典。
     */
    private const CATEGORY_LABELS = [
        'php'   => 'PHP',
        'java'  => 'Java',
        'db'    => 'Database',
        'other' => '其他',
    ];

    private function __construct()
    {
        // 工具类，禁止实例化
    }

    /**
     * 序列化单条文章（Entity → 前端格式）
     *
     * @return array|null 输入 null 时返回 null
     */
    public static function make(Article|null $article): ?array
    {
        if ($article === null) {
            return null;
        }

        return [
            'id'            => $article->getId(),
            'title'         => $article->getTitle(),
            'body'          => $article->getBody(),
            'category'      => $article->getCategory(),
            'categoryLabel' => self::CATEGORY_LABELS[$article->getCategory()] ?? '其他',
            'status'        => $article->getStatus(),
            'createdAt'     => self::formatDate($article->getCreatedAt()),
            'updatedAt'     => self::formatDate($article->getUpdatedAt()),
        ];
    }

    /**
     * 批量序列化（用于列表接口）
     *
     * @param Article[] $items
     * @return array<int, array<string, mixed>>
     */
    public static function collection(array $items): array
    {
        return array_map(self::make(...), $items);
    }

    /**
     * 把 DateTimeImmutable 格式化成 'Y-m-d H:i:s'
     * （前端显示用，非 ISO 8601，避免跨浏览器时区差异）
     */
    private static function formatDate(?\DateTimeInterface $dt): string
    {
        return $dt === null ? '' : $dt->format('Y-m-d H:i:s');
    }
}
