<?php

declare(strict_types=1);

namespace App\Helpers;

use Psr\Http\Message\ServerRequestInterface;

/**
 * ============================================================
 * 请求体助手（PSR-7 版）
 * ============================================================
 *
 * 用途：统一从 PSR-7 请求中读取 JSON body，消除各控制器
 *       里重复的 file_get_contents('php://input') + json_decode 逻辑。
 *
 * 设计要点：
 *   - 不 echo、不抛异常；解析失败或空 body 一律返回 []，
 *     由调用方决定错误响应（避免破坏"控制器决定 4xx 语义"的边界）；
 *   - 支持两种数据源：PSR-7 parsed body（Slim 已经 parse 的）或 php://input。
 *
 * 对应 Java / Spring Boot：@RequestBody Map<String,Object>
 */
final class RequestBody
{
    private function __construct()
    {
    }

    /**
     * 从 PSR-7 请求解析 JSON body，返回关联数组。
     *
     * 优先级：
     *   1. $psr7->getParsedBody() 已经是数组（Slim 的 RequestBodyParser 已经解析）
     *   2. 否则回退到 php://input + json_decode
     *
     * @return array<string,mixed> 解析失败返回 []
     */
    public static function json(ServerRequestInterface $psr7): array
    {
        $parsed = $psr7->getParsedBody();
        if (is_array($parsed) && $parsed !== []) {
            return $parsed;
        }

        return self::fromRaw((string)file_get_contents('php://input'));
    }

    /**
     * 从原始 JSON 字符串解析；解析失败或不是 JSON 对象时返回 []。
     *
     * @return array<string,mixed>
     */
    private static function fromRaw(string $raw): array
    {
        if (trim($raw) === '') {
            return [];
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}
