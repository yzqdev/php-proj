<?php

declare(strict_types=1);

namespace Yzqde\DouyinSpider\Exception;

/**
 * 认证失败异常（Token 无效/过期等）
 */
class AuthenticationException extends AppException
{
    public function __construct(string $message = '认证失败，请重新登录')
    {
        parent::__construct($message, 401);
    }
}
