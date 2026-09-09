<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * 旧版 api/helpers.php 中 apiSuccess/apiError 的信封常量。
 * 所有 JSON 响应结构必须与迁移前完全一致：
 * {"code": int, "message": string, "data": mixed}
 */
final class ApiResponse
{
    public const SUCCESS_CODE = 0;

    public const BAD_REQUEST = 400;
    public const UNAUTHORIZED = 401;
    public const FORBIDDEN = 403;
    public const NOT_FOUND = 404;
    public const METHOD_NOT_ALLOWED = 405;
    public const INTERNAL_ERROR = 500;
}
