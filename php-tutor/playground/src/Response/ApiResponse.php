<?php

declare(strict_types=1);

namespace Yzqde\Playground\Response;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Factory\StreamFactory;

/**
 * 统一 JSON 响应包装（类 Java 的 BaseResponse<T>）
 *
 * 对比 Java / Spring Boot：
 *   - 等价于统一的 BaseResponse<T> / Result<T> / ApiResult<T>
 *   - success + code + message + data 四件套，前端只需判断 success
 *   - 静态工厂方法替代构造器，调用方不关心 json_encode / header / status 细节
 *
 * 用法示例：
 *   return ApiResponse::ok($response, $data);                  // 成功 + 数据
 *   return ApiResponse::ok($response, $data, '上传成功');      // 成功 + 数据 + 提示
 *   return ApiResponse::error($response, '未找到', 404);       // 失败
 *   return ApiResponse::error($response, '参数非法', 422, 42200); // 失败 + 业务码
 *   return ApiResponse::noContent($response);                  // 204 无响应体
 */
final class ApiResponse
{
    /** @var StreamFactory 复用实例，避免每次 new */
    private static ?StreamFactory $streamFactory = null;

    /**
     * 成功响应（success=true）
     *
     * @param ResponseInterface $response PSR-7 响应（由 Slim 控制器传入）
     * @param mixed|null        $data     业务数据，任意类型
     * @param string|null       $message  可选提示信息
     * @param int               $httpStatus HTTP 状态码，默认 200
     */
    public static function ok(
        ResponseInterface $response,
        mixed $data = null,
        ?string $message = null,
        int $httpStatus = 200,
    ): ResponseInterface {
        return self::build($response, true, $message, $data, null, $httpStatus);
    }

    /**
     * 成功响应（success=true），不返回 data（用于 DELETE 等操作）
     */
    public static function okMessage(
        ResponseInterface $response,
        string $message,
        int $httpStatus = 200,
    ): ResponseInterface {
        return self::build($response, true, $message, null, null, $httpStatus);
    }

    /**
     * 失败响应（success=false）
     *
     * @param string      $message  面向用户的错误描述
     * @param int         $httpStatus HTTP 状态码（400/404/422/500 等）
     * @param int|null    $bizCode  可选业务错误码（前端用于细粒度判断）
     */
    public static function error(
        ResponseInterface $response,
        string $message,
        int $httpStatus = 400,
        ?int $bizCode = null,
    ): ResponseInterface {
        return self::build($response, false, $message, null, $bizCode, $httpStatus);
    }

    /**
     * 204 No Content（无响应体，用于无返回值的操作）
     */
    public static function noContent(ResponseInterface $response): ResponseInterface
    {
        return $response->withStatus(204);
    }

    /**
     * 构建最终响应
     */
    private static function build(
        ResponseInterface $response,
        bool $success,
        ?string $message,
        mixed $data,
        ?int $bizCode,
        int $httpStatus,
    ): ResponseInterface {
        $body = [
            'success' => $success,
            'message' => $message,
            'data'    => $data,
        ];
        if ($bizCode !== null) {
            $body['code'] = $bizCode;
        }

        $json = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $stream = self::streamFactory()->createStream($json);

        return $response
            ->withBody($stream)
            ->withHeader('Content-Type', 'application/json; charset=UTF-8')
            ->withStatus($httpStatus);
    }

    private static function streamFactory(): StreamFactory
    {
        if (self::$streamFactory === null) {
            self::$streamFactory = new StreamFactory();
        }
        return self::$streamFactory;
    }
}
