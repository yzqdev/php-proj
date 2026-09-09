<?php

declare(strict_types=1);

namespace App\Controller\OpenApi;

use OpenApi\Attributes as OA;

/**
 * OpenAPI 组件：php/doctrine 演示接口�?data 结构�?
 */
#[OA\Schema(
    schema: 'MessageData',
    type: 'object',
    required: ['message'],
    properties: [
        new OA\Property(property: 'message', type: 'string', description: '演示结果文本（可含换行）', example: 'Repository::findAll()' . "\n" . '所有用�? 张三, 李四'),
    ],
)]
final class MessageDataSchema
{
}
