<?php

declare(strict_types=1);

namespace App\Helpers;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;

/**
 * ============================================================
 * JSON 响应助手（Slim 4 PSR-7 版）
 * ============================================================
 *
 * 设计原则：
 *   - 所有接口统一返回 {code, message, data, errors?}；
 *   - HTTP 状态码与业务 code 分离（HTTP 表达传输层，code 表达业务层）；
 *   - 分页统一格式 {list, total, page, pageSize, totalPage}。
 *
 * 与 Slim 的关系：
 *   - 本类不 echo、不 exit；返回的是 ResponseInterface，
 *     由 Slim 中间件链统一输出，符合 PSR-15 规范。
 *
 * 对应 Java / Spring Boot：
 *   - ResponseEntity<T> + @RestController
 *   - 所有 Controller 方法返回 Response 对象，由 Spring MVC 序列化输出
 *
 * 业务 code 约定（4 位数字）：
 *   - 0      成功
 *   - 4xxxx  客户端错误
 *   - 5xxxx  服务端错误
 */
final class JsonResponse
{
    // 常用业务 code 常量（避免散落在控制器里的魔法数字）
    public const CODE_OK              = 0;
    public const CODE_VALIDATION      = 42201;
    public const CODE_UNAUTHORIZED    = 40101;
    public const CODE_INVALID_TOKEN   = 40102;
    public const CODE_FORBIDDEN       = 40301;
    public const CODE_NOT_FOUND       = 40401;
    public const CODE_INTERNAL_ERROR  = 50000;

    private function __construct()
    {
    }

    /**
     * 成功响应（HTTP 200）
     *
     * @param mixed $data 数据体
     * @param string $message 中文描述
     * @param int $status HTTP 状态码（默认 200）
     */
    public static function ok(mixed $data = null, string $message = 'ok', int $status = 200): ResponseInterface
    {
        return self::send([
            'code'    => self::CODE_OK,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * 校验失败（HTTP 422）
     *
     * @param array<string, array<int, string>> $errors 字段级错误
     */
    public static function validation(array $errors, string $message = '参数校验失败'): ResponseInterface
    {
        return self::send([
            'code'    => self::CODE_VALIDATION,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
        ], 422);
    }

    /**
     * 业务错误
     */
    public static function error(
        int $code,
        string $message,
        int $http = 500,
        array|null $errors = null,
    ): ResponseInterface {
        $payload = ['code' => $code, 'message' => $message, 'data' => null];
        if ($errors !== null) {
            $payload['errors'] = $errors;
        }
        return self::send($payload, $http);
    }

    /**
     * 204 No Content（无 body）
     */
    public static function noContent(): ResponseInterface
    {
        return self::send(null, 204);
    }

    /**
     * 分页响应
     *
     * @param array $list 当前页数据
     * @param int $total 总条数
     * @param int $page 当前页码
     * @param int $pageSize 每页条数
     */
    public static function paginate(array $list, int $total, int $page, int $pageSize): ResponseInterface
    {
        $totalPage = $pageSize > 0 ? (int)ceil($total / $pageSize) : 0;
        $data = [
            'list'      => $list,
            'total'     => $total,
            'page'      => max(1, $page),
            'pageSize'  => $pageSize,
            'totalPage' => $totalPage,
        ];
        return self::ok($data);
    }

    /**
     * 底层：创建 PSR-7 JSON 响应
     */
    private static function send(mixed $body, int $status): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(
            $body !== null ? json_encode($body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : ''
        );
        return $response
            ->withHeader('Content-Type', 'application/json; charset=UTF-8')
            ->withStatus($status);
    }
}