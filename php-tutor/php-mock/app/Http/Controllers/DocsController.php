<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

/**
 * API 文档：返回手写的 OpenAPI 3.0 规范 JSON（由 routes/web.php 的 Swagger UI 消费）
 */
class DocsController extends Controller
{
    public function spec(): JsonResponse
    {
        return response()->json($this->buildSpec());
    }

    private function buildSpec(): array
    {
        $response = fn (string $desc) => [
            'description' => $desc,
            'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/ApiResponse']]],
        ];

        $jsonBody = fn (array $props, array $required = []) => [
            'required' => $required,
            'content' => ['application/json' => ['schema' => ['type' => 'object', 'properties' => $props]]],
        ];

        return [
            'openapi' => '3.0.0',
            'info' => [
                'title' => '模拟数据 API 平台',
                'version' => '1.0.0',
                'description' => '定义数据模板 → 批量生成假数据 → 公开 Mock API 完整 CRUD。'
                    .'管理接口使用 Bearer token（登录后获取），Mock 接口无需认证。',
            ],
            'servers' => [['url' => '/']],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => ['type' => 'http', 'scheme' => 'bearer'],
                ],
                'schemas' => [
                    'ApiResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'code' => ['type' => 'integer', 'example' => 0],
                            'message' => ['type' => 'string', 'example' => 'success'],
                            'data' => [],
                            'meta' => ['type' => 'object'],
                        ],
                    ],
                    'Field' => [
                        'type' => 'object',
                        'required' => ['name', 'type'],
                        'properties' => [
                            'name' => ['type' => 'string', 'example' => 'email'],
                            'type' => ['type' => 'string', 'enum' => ['name', 'email', 'phone', 'address', 'company', 'number', 'enum', 'bool', 'date', 'text', 'uuid', 'url', 'image']],
                            'min' => ['type' => 'integer', 'description' => 'number 类型下限，默认 0'],
                            'max' => ['type' => 'integer', 'description' => 'number 类型上限，默认 100'],
                            'options' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'enum 必填'],
                            'length' => ['type' => 'integer', 'description' => 'text 长度，默认 200'],
                            'width' => ['type' => 'integer', 'description' => 'image 宽，默认 200'],
                            'height' => ['type' => 'integer', 'description' => 'image 高，默认 200'],
                        ],
                    ],
                ],
            ],
            'paths' => [
                '/api/auth/register' => [
                    'post' => [
                        'tags' => ['认证'],
                        'summary' => '注册并获取 token',
                        'requestBody' => $jsonBody([
                            'name' => ['type' => 'string'],
                            'email' => ['type' => 'string', 'format' => 'email'],
                            'password' => ['type' => 'string'],
                        ], ['name', 'email', 'password']),
                        'responses' => ['200' => $response('包含 Sanctum token')],
                    ],
                ],
                '/api/auth/login' => [
                    'post' => [
                        'tags' => ['认证'],
                        'summary' => '登录获取 token',
                        'requestBody' => $jsonBody([
                            'email' => ['type' => 'string'],
                            'password' => ['type' => 'string'],
                        ], ['email', 'password']),
                        'responses' => ['200' => $response('包含 Sanctum token')],
                    ],
                ],
                '/api/projects' => [
                    'get' => [
                        'tags' => ['管理'],
                        'summary' => '我的项目列表',
                        'security' => [['bearerAuth' => []]],
                        'responses' => ['200' => $response('项目列表')],
                    ],
                    'post' => [
                        'tags' => ['管理'],
                        'summary' => '创建项目（自动生成 slug 与 api_key）',
                        'security' => [['bearerAuth' => []]],
                        'requestBody' => $jsonBody(['name' => ['type' => 'string']], ['name']),
                        'responses' => ['201' => $response('新项目')],
                    ],
                ],
                '/api/projects/{project}/resources' => [
                    'post' => [
                        'tags' => ['管理'],
                        'summary' => '定义资源并立即生成数据（count 缺省 20，上限 10000）',
                        'security' => [['bearerAuth' => []]],
                        'parameters' => [['name' => 'project', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]],
                        'requestBody' => $jsonBody([
                            'name' => ['type' => 'string', 'example' => 'users'],
                            'fields' => ['type' => 'array', 'items' => ['$ref' => '#/components/schemas/Field']],
                            'count' => ['type' => 'integer', 'example' => 50],
                        ], ['name', 'fields']),
                        'responses' => ['201' => $response('资源与生成数量')],
                    ],
                ],
                '/api/resources/{resource}' => [
                    'get' => [
                        'tags' => ['管理'],
                        'summary' => '资源详情（Schema + 数据量）',
                        'security' => [['bearerAuth' => []]],
                        'parameters' => [['name' => 'resource', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]],
                        'responses' => ['200' => $response('资源详情')],
                    ],
                ],
                '/api/resources/{resource}/generate' => [
                    'post' => [
                        'tags' => ['管理'],
                        'summary' => '重新生成（先清空旧数据）',
                        'security' => [['bearerAuth' => []]],
                        'parameters' => [['name' => 'resource', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]],
                        'requestBody' => $jsonBody(['count' => ['type' => 'integer']]),
                        'responses' => ['200' => $response('生成结果')],
                    ],
                ],
                '/api/resources/{resource}/records' => [
                    'delete' => [
                        'tags' => ['管理'],
                        'summary' => '清空资源数据',
                        'security' => [['bearerAuth' => []]],
                        'parameters' => [['name' => 'resource', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]],
                        'responses' => ['200' => $response('删除条数')],
                    ],
                ],
                '/api/v1/mock/{projectSlug}/{resourceName}' => [
                    'get' => [
                        'tags' => ['Mock'],
                        'summary' => '列表（支持 page/size/sort/keyword+search_in）',
                        'parameters' => [
                            ['name' => 'projectSlug', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string'], 'example' => 'demo'],
                            ['name' => 'resourceName', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string'], 'example' => 'users'],
                            ['name' => 'page', 'in' => 'query', 'schema' => ['type' => 'integer']],
                            ['name' => 'size', 'in' => 'query', 'schema' => ['type' => 'integer', 'maximum' => 100]],
                            ['name' => 'sort', 'in' => 'query', 'schema' => ['type' => 'string'], 'description' => '字段升序，前缀 - 降序，如 -id'],
                            ['name' => 'keyword', 'in' => 'query', 'schema' => ['type' => 'string']],
                            ['name' => 'search_in', 'in' => 'query', 'schema' => ['type' => 'string'], 'description' => '逗号分隔的 JSON 字段名，如 name,email'],
                        ],
                        'responses' => ['200' => $response('分页列表，记录 id 已合并进 data')],
                    ],
                    'post' => [
                        'tags' => ['Mock'],
                        'summary' => '新增一条记录（body 即 data）',
                        'parameters' => [
                            ['name' => 'projectSlug', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                            ['name' => 'resourceName', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                        ],
                        'requestBody' => ['content' => ['application/json' => ['schema' => ['type' => 'object']]]],
                        'responses' => ['201' => $response('新记录')],
                    ],
                ],
                '/api/v1/mock/{projectSlug}/{resourceName}/{id}' => [
                    'get' => [
                        'tags' => ['Mock'],
                        'summary' => '详情',
                        'parameters' => [
                            ['name' => 'projectSlug', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                            ['name' => 'resourceName', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => ['200' => $response('单条记录')],
                    ],
                    'put' => [
                        'tags' => ['Mock'],
                        'summary' => '更新（合并 data）',
                        'parameters' => [
                            ['name' => 'projectSlug', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                            ['name' => 'resourceName', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'requestBody' => ['content' => ['application/json' => ['schema' => ['type' => 'object']]]],
                        'responses' => ['200' => $response('更新后的记录')],
                    ],
                    'delete' => [
                        'tags' => ['Mock'],
                        'summary' => '删除',
                        'parameters' => [
                            ['name' => 'projectSlug', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                            ['name' => 'resourceName', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string']],
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => ['200' => $response('已删除 id')],
                    ],
                ],
            ],
        ];
    }
}
