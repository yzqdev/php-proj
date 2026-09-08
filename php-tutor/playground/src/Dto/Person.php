<?php

declare(strict_types=1);

namespace Yzqde\Playground\Dto;

use OpenApi\Attributes as OA;

/**
 * 人员 DTO（Data Transfer Object）
 *
 * 对比 Java / Spring Boot：
 *   - 等价于 Spring 的 PersonVO / PersonResponse DTO
 *   - Service 层产出，Controller 层消费，不暴露内部数据结构
 */
#[OA\Schema(
    schema: 'Person',
    title: '人员',
    description: '人员信息视图',
)]
final readonly class Person
{
    public function __construct(
        #[OA\Property(property: 'id', type: 'integer', format: 'int64', description: '唯一标识', example: 1)]
        public int $id,
        #[OA\Property(property: 'name', type: 'string', description: '姓名', example: '张三')]
        public string $name,
        #[OA\Property(property: 'email', type: 'string', format: 'email', description: '邮箱', example: 'zhangsan@example.com')]
        public string $email,
        #[OA\Property(property: 'phone', type: 'string', nullable: true, description: '手机号', example: '13800138000')]
        public ?string $phone,
        #[OA\Property(property: 'createdAt', type: 'string', format: 'date-time', description: '创建时间', example: '2026-09-08T08:00:00+00:00')]
        public string $createdAt,
    ) {
    }

    /**
     * @return array{id: int, name: string, email: string, phone: ?string, createdAt: string}
     */
    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'email'     => $this->email,
            'phone'     => $this->phone,
            'createdAt' => $this->createdAt,
        ];
    }
}
