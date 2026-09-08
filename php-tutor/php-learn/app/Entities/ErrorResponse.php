<?php
declare(strict_types=1);

namespace App\Entities;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ErrorResponse',
    title: '统一错误响应结构',
    description: 'API 请求出错或校验失败时的统一返回对象'
)]
class ErrorResponse
{
    #[OA\Property(description: '业务/HTTP 错误码', example: 40000)]
    public int $code;

    #[OA\Property(description: '错误提示信息', example: '未登录或 Token 已过期')]
    public string $message;

    #[OA\Property(
        description: '响应数据（通常为 null）',
        example: null,
        nullable: true
    )]
    public mixed $data;

    #[OA\Property(
        description: '具体的字段校验错误明细（422 参数校验错误时返回）',
        type: 'object',
        nullable: true,
        example: ['title' => ['标题不能为空']]
    )]
    public ?array $errors;
}
