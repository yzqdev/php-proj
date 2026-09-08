<?php

declare(strict_types=1);

namespace App\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * 统一 API 响应封装
 *
 * 所有 API 端点返回此结构，前端按 code 判断成功/失败：
 *   code = 0  → 成功
 *   code > 0  → 业务错误
 *
 * 响应体格式：
 *   { "code": 0, "message": "...", "data": {...} }
 */
final class BaseResponse
{
    private function __construct()
    {
    }

    /**
     * 成功响应
     *
     * @param ResponseInterface $response PSR-7 响应对象
     * @param mixed             $data     业务数据（默认 null）
     * @param string            $message  提示信息（默认 ''）
     * @param int               $status   HTTP 状态码（默认 200）
     *
     * @return ResponseInterface JSON 响应，code=0
     */
    public static function success(
        ResponseInterface $response,
        mixed $data = null,
        string $message = '',
        int $status = 200,
    ): ResponseInterface {
        $body = [
            'code'    => 0,
            'message' => $message,
            'data'    => $data,
        ];

        return self::writeJson($response, $body, $status);
    }

    /**
     * 业务错误响应（code > 0）
     *
     * @param ResponseInterface $response  PSR-7 响应对象
     * @param string            $message   错误提示
     * @param int               $code      业务错误码（默认 400）
     * @param mixed             $details   详细信息（默认 null）
     * @param int               $status    HTTP 状态码（默认 400）
     *
     * @return ResponseInterface JSON 响应，code=$code
     */
    public static function error(
        ResponseInterface $response,
        string $message,
        int $code = 400,
        mixed $details = null,
        int $status = 400,
    ): ResponseInterface {
        $body = [
            'code'    => $code,
            'message' => $message,
            'data'    => $details,
        ];

        return self::writeJson($response, $body, $status);
    }

    /**
     * 404 未找到
     *
     * @param ResponseInterface $response PSR-7 响应对象
     * @param string            $message  提示信息（默认 '资源不存在'）
     *
     * @return ResponseInterface HTTP 404 JSON 响应
     */
    public static function notFound(
        ResponseInterface $response,
        string $message = '资源不存在',
    ): ResponseInterface {
        return self::error($response, $message, 404, null, 404);
    }

    /**
     * 422 参数验证失败
     *
     * @param ResponseInterface $response  PSR-7 响应对象
     * @param string            $message   错误提示
     * @param mixed             $details   详细信息（默认 null）
     *
     * @return ResponseInterface HTTP 422 JSON 响应
     */
    public static function validationError(
        ResponseInterface $response,
        string $message,
        mixed $details = null,
    ): ResponseInterface {
        return self::error($response, $message, 422, $details, 422);
    }

    /**
     * 将数组写入 PSR-7 响应体
     *
     * @param ResponseInterface $response PSR-7 响应对象
     * @param array             $body     序列化为 JSON 的数据
     * @param int               $status   HTTP 状态码
     *
     * @return ResponseInterface 设置了 Content-Type 和状态码的响应
     */
    private static function writeJson(
        ResponseInterface $response,
        array $body,
        int $status,
    ): ResponseInterface {
        $response->getBody()->write((string) json_encode($body, JSON_UNESCAPED_UNICODE));
        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
}