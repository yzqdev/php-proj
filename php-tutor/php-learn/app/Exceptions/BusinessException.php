<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * ============================================================
 * 业务异常（携带业务 code + HTTP 状态码）
 * ============================================================
 *
 * 为什么要单独抽这一层？
 *   - Controller 里 throw 一个普通 \RuntimeException 时，
 *     全局异常处理器只能给出 HTTP 500（因为不知道是业务错误还是系统错误）；
 *   - BusinessException 明确携带 code 和 httpStatus，
 *     全局异常处理器直接映射到统一响应壳；
 *   - 相当于 Java Spring 的 @ExceptionHandler(BusinessException.class)。
 *
 * 业务 code 约定（4 位数字）：
 *   - 0xxxx     系统级错误（HTTP 5xx）
 *   - 4xxxx     客户端错误（HTTP 4xx）
 *     - 40101   未提供访问令牌
 *     - 40102   令牌无效或过期
 *     - 40103   用户不存在
 *     - 40301   权限不足
 *     - 40401   资源不存在
 *     - 42201   参数校验失败
 *   - 5xxxx     服务端错误（HTTP 5xx）
 *     - 50000   内部错误
 *
 * PHP 8.x 特性：
 *   - 命名参数调用：new BusinessException(message: 'x', bizCode: 40102, httpStatus: 401)
 *   - 注意：属性名不能叫 $code，因为 \Exception::$code 已定义且非 readonly，
 *           子类不能重定义成 readonly。所以业务 code 字段改叫 $bizCode。
 */
class BusinessException extends \RuntimeException
{
    public function __construct(
        string $message = '业务异常',
        private readonly int $bizCode = 50000,
        private readonly int $httpStatus = 500,
        ?\Throwable $previous = null,
    ) {
        // 父类的 code 参数复用 bizCode（PHP \Exception::$code 是 int，方便与父类兼容）
        parent::__construct($message, $bizCode, $previous);
    }

    /** 业务 code（返回给前端用） */
    public function getBizCode(): int
    {
        return $this->bizCode;
    }

    /** HTTP 状态码 */
    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    /**
     * 便捷工厂：未授权（401）
     */
    public static function unauthorized(string $message = '未提供访问令牌'): self
    {
        return new self($message, 40101, 401);
    }

    /** 便捷：无效 token */
    public static function invalidToken(string $message = '令牌无效或已过期'): self
    {
        return new self($message, 40102, 401);
    }

    /** 便捷：用户不存在 */
    public static function userNotFound(): self
    {
        return new self('用户不存在', 40103, 401);
    }

    /** 便捷：权限不足 */
    public static function forbidden(string $message = '权限不足'): self
    {
        return new self($message, 40301, 403);
    }

    /** 便捷：参数校验失败 */
    public static function validation(string $message = '参数校验失败'): self
    {
        return new self($message, 42201, 422);
    }
}
