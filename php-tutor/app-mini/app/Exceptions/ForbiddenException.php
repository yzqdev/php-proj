<?php
declare(strict_types=1);

namespace App\Exceptions;

/**
 * 已认证但无权操作,统一 403。
 */
final class ForbiddenException extends ApiException
{
    public function __construct(string $message = '无权执行该操作')
    {
        parent::__construct($message, 403, 'forbidden');
    }
}
