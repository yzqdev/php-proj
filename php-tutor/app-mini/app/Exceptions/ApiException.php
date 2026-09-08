<?php
declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;
use Throwable;

/**
 * 业务异常基类,携带 HTTP 状态码、错误码与结构化详情,
 * 由全局错误处理器转换为统一 JSON 信封。
 */
class ApiException extends RuntimeException
{
    public function __construct(
        string $message,
        protected readonly int $statusCode = 500,
        protected readonly string $errorCode = 'internal_error',
        protected readonly array $details = [],
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /** @return array<string, mixed> */
    public function getDetails(): array
    {
        return $this->details;
    }
}
