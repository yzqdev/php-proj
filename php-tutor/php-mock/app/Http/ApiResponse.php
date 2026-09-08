<?php

declare(strict_types=1);

namespace App\Http;

use Illuminate\Http\JsonResponse;

/**
 * 统一 API 响应封装
 *
 * 成功：{"code":0,"message":"success","data":...,"meta":{...}}
 * 错误：{"code":<http状态码>,"message":"..."}
 */
class ApiResponse
{
    public static function success(mixed $data = null, array $meta = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'code' => 0,
            'message' => 'success',
            'data' => $data,
            'meta' => $meta ?: (object) [],
        ], $status);
    }

    public static function error(int $status, string $message): JsonResponse
    {
        return response()->json([
            'code' => $status,
            'message' => $message,
        ], $status);
    }

    /**
     * 列表分页响应：meta 携带 current_page / total / last_page
     */
    public static function paginated($paginator): JsonResponse
    {
        return self::success($paginator->items(), [
            'current_page' => $paginator->currentPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ]);
    }
}
