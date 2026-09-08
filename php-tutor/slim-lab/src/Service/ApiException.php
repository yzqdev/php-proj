<?php

declare(strict_types=1);

namespace Service;

/**
 * 业务错误异常：等价于旧 api/helpers.php 的 apiError($message, $code)。
 * 控制器捕获后按迁移前完全一致的 JSON 信封输出。
 */
final class ApiException extends \RuntimeException
{
    public function __construct(string $message, int $code = ApiResponse::BAD_REQUEST)
    {
        parent::__construct($message, $code);
    }
}
