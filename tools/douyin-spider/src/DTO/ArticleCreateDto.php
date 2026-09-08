<?php

declare(strict_types=1);

namespace Yzqde\DouyinSpider\DTO;

/**
 * 文章创建/更新 DTO
 */
readonly class ArticleCreateDto
{
    public function __construct(
        public string $title,
        public string $content,
        public ?string $summary = null,
        public int $categoryId = 0,
    ) {
    }
}
