<?php
declare(strict_types=1);

namespace App\Exceptions;

/**
 * 参数校验失败,统一 422,details 携带字段级错误。
 */
final class ValidationException extends ApiException
{
    /**
     * @param array<string, array<int, string>> $details 字段 => 错误消息列表
     */
    public function __construct(string $message = '参数校验失败', array $details = [])
    {
        parent::__construct($message, 422, 'validation_failed', $details);
    }
}
