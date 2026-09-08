<?php

declare(strict_types=1);

namespace Yzqde\DouyinSpider\Enum;

/**
 * 用户角色枚举
 */
enum UserRole: string
{
    case USER = 'user';
    case EDITOR = 'editor';
    case ADMIN = 'admin';
}
