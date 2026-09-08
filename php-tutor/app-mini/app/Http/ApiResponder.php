<?php
declare(strict_types=1);

namespace App\Http;

use App\Http\Pagination;
use JsonException;
use Psr\Http\Message\ResponseInterface;

/**
 * 统一 JSON 响应信封。
 * 成功:{"success": true, "data": ..., "meta": ...}
 * 失败:{"success": false, "error": {"code", "message", "details"}}
 */
final class ApiResponder
{
    private const JSON_FLAGS = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR;

    public static function success(ResponseInterface $response, mixed $data = null, int $status = 200, ?array $meta = null): ResponseInterface
    {
        $payload = ['success' => true, 'data' => $data];
        if ($meta !== null) {
            $payload['meta'] = $meta;
        }

        return self::json($response, $payload, $status);
    }

    /** @param array<string, mixed> $details */
    public static function error(ResponseInterface $response, string $code, string $message, int $status = 400, array $details = []): ResponseInterface
    {
        $payload = ['success' => false, 'error' => ['code' => $code, 'message' => $message]];
        if ($details !== []) {
            $payload['error']['details'] = $details;
        }

        return self::json($response, $payload, $status);
    }

    /**
     * 分页成功响应,meta 含 total/page/per_page/last_page。
     */
    public static function paginated(ResponseInterface $response, Pagination $pagination, int $status = 200): ResponseInterface
    {
        return self::success($response, $pagination->items, $status, [
            'total' => $pagination->total,
            'page' => $pagination->page,
            'per_page' => $pagination->perPage,
            'last_page' => $pagination->lastPage,
        ]);
    }

    /** @param array<string, mixed> $payload */
    private static function json(ResponseInterface $response, array $payload, int $status): ResponseInterface
    {
        $response = $response->withStatus($status)->withHeader('Content-Type', 'application/json; charset=utf-8');
        try {
            $body = json_encode($payload, self::JSON_FLAGS);
        } catch (JsonException) {
            $body = '{"success":false,"error":{"code":"internal_error","message":"响应编码失败"}}';
        }
        $response->getBody()->write($body);

        return $response;
    }
}
