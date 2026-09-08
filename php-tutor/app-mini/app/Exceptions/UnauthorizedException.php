<?php
declare(strict_types=1);

namespace App\Exceptions;

/**
 * 未认证/凭据无效,统一 401。
 */
final class UnauthorizedException extends ApiException
{
    public function __construct(string $message = '未登录或凭据无效')
    {
        parent::__construct($message, 401, 'unauthorized');
    }
}
