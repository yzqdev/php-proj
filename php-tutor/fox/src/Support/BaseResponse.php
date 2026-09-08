<?php

declare(strict_types=1);

namespace Yzqde\Fox\Support;

use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Unified JSON envelope for the /api/* endpoints.
 *
 *   { "code": 200, "message": "ok", "data": { ... } }
 *
 * `code` mirrors the HTTP status so a client has one field to switch on.
 * List endpoints nest their page fields under `data`:
 *
 *   { "code": 200, "message": "ok",
 *     "data": { "list": [ ... ], "total": 120, "limit": 50, "offset": 0, "hasMore": true } }
 *
 * Intentionally not enveloped: /swagger (HTML) and /swagger/json (must stay a raw
 * OpenAPI document), /api/logs/{date}?raw=1 (a file dump), and the plain-text
 * home endpoints. They are not JSON API payloads.
 */
#[OA\Schema(
    schema: 'ApiResponse',
    description: 'Envelope used by every /api/* endpoint',
    properties: [
        new OA\Property(property: 'code', type: 'integer', description: 'Mirrors the HTTP status; 2xx means success', example: 200),
        new OA\Property(property: 'message', type: 'string', example: 'ok'),
        new OA\Property(property: 'data', nullable: true, description: 'Endpoint payload, or an error-free list envelope'),
    ]
)]
final class BaseResponse
{
    public const OK = 200;
    public const CREATED = 201;
    public const NO_CONTENT = 204;
    public const BAD_REQUEST = 400;
    public const NOT_FOUND = 404;
    public const METHOD_NOT_ALLOWED = 405;
    public const INTERNAL = 500;

    private function __construct()
    {
    }

    public static function success(
        Response $response,
        mixed $data = null,
        string $message = 'ok',
        int $status = self::OK
    ): Response {
        return self::json($response, [
            'code' => $status,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public static function list(
        Response $response,
        array $items,
        int $total,
        ?int $limit = null,
        ?int $offset = null,
        int $status = self::OK
    ): Response {
        $page = ['list' => $items, 'total' => $total];

        if ($limit !== null) {
            $page['limit'] = $limit;
        }

        if ($offset !== null) {
            $page['offset'] = $offset;
            $page['hasMore'] = $offset + $limit < $total;
        }

        return self::success($response, $page, 'ok', $status);
    }

    public static function error(Response $response, string $message, int $code = self::BAD_REQUEST, ?int $status = null): Response
    {
        $status ??= $code;

        return self::json($response, [
            'code' => $code,
            'message' => $message,
            'data' => null,
        ], $status);
    }

    /** 204 carries no body. */
    public static function noContent(Response $response, int $status = self::NO_CONTENT): Response
    {
        return $response->withStatus($status);
    }

    /** Raw JSON writer for endpoints that must not be enveloped. */
    public static function json(Response $response, array $payload, int $status = self::OK): Response
    {
        $response->getBody()->write((string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
    }
}
