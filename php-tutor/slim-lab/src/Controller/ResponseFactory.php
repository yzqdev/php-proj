<?php

declare(strict_types=1);

namespace App\Controller;

use Psr\Http\Message\ResponseInterface;

/**
 * 旧 api/helpers.php 中 apiSuccess/apiError 的等价实现（改为返回 PSR-7 响应）。
 * JSON 编码选项与迁移前完全一致：仅 JSON_UNESCAPED_UNICODE，信封 {code,message,data}。
 */
final class ResponseFactory
{
    /** 旧 index.php 全局 header('Content-Type: application/json; charset=utf-8') */
    public const JSON_CONTENT_TYPE = 'application/json; charset=utf-8';

    /**
     * 等价旧 apiSuccess($data, $message)：code=0，HTTP 200。
     */
    public static function ok(ResponseInterface $response, mixed $data = null, string $message = 'success'): ResponseInterface
    {
        return self::json($response, ['code' => 0, 'message' => $message, 'data' => $data], 200);
    }

    /**
     * 等价旧 apiError($message, $code)：code=HTTP 状态码，data=null。
     */
    public static function error(ResponseInterface $response, string $message = 'error', int $code = 400): ResponseInterface
    {
        return self::json($response, ['code' => $code, 'message' => $message, 'data' => null], $code);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function json(ResponseInterface $response, array $payload, int $status): ResponseInterface
    {
        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE));

        return $response->withStatus($status)->withHeader('Content-Type', self::JSON_CONTENT_TYPE);
    }
}
