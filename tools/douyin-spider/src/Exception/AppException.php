<?php

declare(strict_types=1);

namespace Yzqde\DouyinSpider\Exception;

use RuntimeException;

/**
 * 应用业务异常基类
 */
class AppException extends RuntimeException
{
    public function __construct(
        string $message = '',
        int $code = 400,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
