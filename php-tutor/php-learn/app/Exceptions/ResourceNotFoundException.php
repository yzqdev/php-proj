<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * 资源不存在异常（HTTP 404）
 *
 * 为什么要单独一个类？
 *   - 语义清晰：Controller 里 throw new ResourceNotFoundException('Article', 1)
 *     一眼就知道是"资源 404"，而不是"任何 4xx"；
 *   - 全局异常处理器可以按类做特殊处理（比如记录不同级别的日志）；
 *   - 对应 Java：Spring 的 ResponseStatusException(HttpStatus.NOT_FOUND)。
 *
 * @see BusinessException
 */
class ResourceNotFoundException extends BusinessException
{
    public function __construct(
        private readonly string $resource,
        private readonly int $resourceId,
        string $message = '',
        ?\Throwable $previous = null,
    ) {
        // 默认消息 "资源 XXX #1 不存在"，允许自定义覆盖
        $defaultMsg = "{$resource} #{$resourceId} 不存在";
        parent::__construct($message !== '' ? $message : $defaultMsg, 40401, 404, $previous);
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function getResourceId(): int
    {
        return $this->resourceId;
    }
}
