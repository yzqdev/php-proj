<?php
declare(strict_types=1);

namespace App\Exceptions;

/**
 * 资源不存在,统一 404。
 */
final class NotFoundException extends ApiException
{
    public function __construct(string $message = '资源不存在')
    {
        parent::__construct($message, 404, 'not_found');
    }
}
