<?php
declare(strict_types=1);

namespace App\Enums;

/**
 * 文章发布状态。
 */
enum PostStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
}
