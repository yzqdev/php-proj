<?php

declare(strict_types=1);

namespace Yzqde\DouyinSpider\Exception;

/**
 * 权限不足异常
 */
class AuthorizationException extends AppException
{
    public function __construct(string $message = '无权访问该资源')
    {
        parent::__construct($message, 403);
    }
}
