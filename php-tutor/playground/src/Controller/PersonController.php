<?php

declare(strict_types=1);

namespace Yzqde\Playground\Controller;

use Monolog\Logger;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Slim\Psr7\Stream;
use Yzqde\Playground\Response\ApiResponse;
use Yzqde\Playground\Service\PersonService;

/**
 * 人员管理控制器（CRUD 演示）
 *
 * 对比 Java / Spring Boot：
 *   - 等价于 Spring 的 @RestController PersonController
 *   - 构造器注入 PersonService（php-di 自动装配 ↔ Spring @Autowired）
 *   - 所有响应统一走 ApiResponse（↔ BaseResponse<T>）
 */
final class PersonController
{
    public function __construct(private readonly PersonService $persons, private readonly LoggerInterface  $logger)
    {
    }

    #[OA\Get(
        path: '/api/personTool',
        operationId: 'personTool',
        description: '提供文件的上传、读取、下载、删除及列表查看等综合操作工具',
        summary: '人员及文件工具集',
        tags: ['PersonTools']
    )]
    #[OA\Parameter(
        name: 'action',
        description: '操作类型：list (列表), read (读取内容), download (下载), delete (删除)',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'string',
            default: 'list',
            enum: ['list', 'read', 'download', 'delete']
        )
    )]
    #[OA\Parameter(
        name: 'filename',
        description: '文件名（action 为 read、download、delete 时必填）',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Response(
        response: 200,
        description: '操作成功',
        content: [
            // 场景 1：返回 JSON 格式（如文件列表、文件内容或通用操作提示）
            new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'code', type: 'integer', example: 200),
                        new OA\Property(property: 'msg', type: 'string', example: 'ok'),
                        new OA\Property(
                            property: 'data',
                            oneOf: [
                                // 列表数据类型示例
                                new OA\Schema(
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'name', type: 'string', example: 'avatar_123.jpg'),
                                            new OA\Property(property: 'size', type: 'integer', example: 102400),
                                            new OA\Property(property: 'updated_at', type: 'string', example: '2026-09-07 10:00:00')
                                        ],
                                        type: 'object'
                                    )
                                ),
                                // 读取文件内容类型示例
                                new OA\Schema(
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'filename', type: 'string', example: 'note.txt'),
                                        new OA\Property(property: 'content', type: 'string', example: '文件文本内容...')
                                    ]
                                ),
                                // 操作类返回字符串示例
                                new OA\Schema(type: 'string', example: '文件删除成功')
                            ]
                        )
                    ],
                    type: 'object'
                )
            ),
            // 场景 2：返回二进制文件下载流（当 action = download 时）
            new OA\MediaType(
                mediaType: 'application/octet-stream',
                schema: new OA\Schema(
                    description: '文件二进制流',
                    type: 'string',
                    format: 'binary'
                )
            )
        ]
    )]
    #[OA\Response(
        response: 400,
        description: '请求参数错误（如缺少文件名）'
    )]
    #[OA\Response(
        response: 404,
        description: '指定文件不存在'
    )]
    #[OA\Response(
        response: 500,
        description: '服务器内部错误（如操作文件失败）'
    )]
    public function personTool(Request $request, Response $response, array $args): Response
    {
        // 获取请求参数（如查询参数或 body 参数）
        $params = $request->getQueryParams();
        $action = $params['action'] ?? 'list'; // 默认操作：查看文件列表

        // 基础存储路径（请确保该目录存在且具备可写权限）
        $targetDir = __DIR__ . '/../../storage';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        switch ($action) {
            // ================= 1. 文件上传 =================
            case 'upload':
                $uploadedFiles = $request->getUploadedFiles();

                if (empty($uploadedFiles['file'])) {
                    return ApiResponse::error($response, "没有检测到上传的文件", 400);
                }

                $uploadedFile = $uploadedFiles['file'];

                if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                    $filename = sprintf(
                        '%s_%s',
                        pathinfo($uploadedFile->getClientFilename(), PATHINFO_FILENAME),
                        uniqid() . '.' . pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION)
                    );

                    $filepath = $targetDir . DIRECTORY_SEPARATOR . $filename;
                    // 将文件移动到指定目标位置
                    $uploadedFile->moveTo($filepath);

                    return ApiResponse::ok($response, [
                        'message' => '文件上传成功',
                        'filename' => $filename
                    ]);
                }

                return ApiResponse::error($response, "文件上传失败", 500);

            // ================= 2. 读取/获取文件内容 =================
            case 'read':
                $this->logger->error(  message: "得寸进尺");
                $filename = $params['filename'] ?? '';
                $filepath = $targetDir . DIRECTORY_SEPARATOR . basename($filename);

                if (!file_exists($filepath)) {
                    return ApiResponse::error($response, "文件不存在", 404);
                }

                $content = file_get_contents($filepath);
                return ApiResponse::ok($response, [
                    'filename' => $filename,
                    'content' => $content
                ]);

            // ================= 3. 文件下载 =================
            case 'download':
                $filename = $params['filename'] ?? '';
                $filepath = $targetDir . DIRECTORY_SEPARATOR . basename($filename);

                if (!file_exists($filepath)) {
                    return ApiResponse::error($response, "文件不存在", 404);
                }

                // 读取文件流并作为二进制下载返回
                $stream = new Stream(fopen($filepath, 'rb'));

                return $response
                    ->withHeader('Content-Type', 'application/octet-stream')
                    ->withHeader('Content-Disposition', 'attachment; filename="' . basename($filepath) . '"')
                    ->withHeader('Content-Length', filesize($filepath))
                    ->withBody($stream);

            // ================= 4. 删除文件 =================
            case 'delete':
                $filename = $params['filename'] ?? '';
                $filepath = $targetDir . DIRECTORY_SEPARATOR . basename($filename);

                if (!file_exists($filepath)) {
                    return ApiResponse::error($response, "文件不存在", 404);
                }

                if (unlink($filepath)) {
                    return ApiResponse::ok($response, "文件删除成功");
                }

                return ApiResponse::error($response, "文件删除失败", 500);

            // ================= 5. 获取目录下的文件列表 =================
            case 'list':
            default:
                $files = array_diff(scandir($targetDir), ['.', '..']);
                $fileList = [];

                foreach ($files as $file) {
                    $filePath = $targetDir . DIRECTORY_SEPARATOR . $file;
                    if (is_file($filePath)) {
                        $fileList[] = [
                            'name' => $file,
                            'size' => filesize($filePath),
                            'updated_at' => date('Y-m-d H:i:s', filemtime($filePath))
                        ];
                    }
                }

                return ApiResponse::ok($response, $fileList);
        }
    }
    // -----------------------------------------------------------------------
    // 列表
    // -----------------------------------------------------------------------

    #[OA\Get(
        path: '/api/persons',
        operationId: 'listPersons',
        description: '返回所有人员，按 ID 倒序',
        summary: '人员列表',
        tags: ['人员'],
        responses: [
            new OA\Response(
                response: '200',
                description: '人员列表',
                content: new OA\JsonContent(
                    required: ['success', 'data'],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            description: '人员列表',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Person'),
                        ),
                    ],
                    type: 'object',
                ),
            ),
        ],
    )]
    public function list(Request $request, Response $response): Response
    {
        $list = array_map(
            fn(\Yzqde\Playground\Dto\Person $p) => $p->toArray(),
            $this->persons->list(),
        );
        return ApiResponse::ok($response, $list);
    }

    // -----------------------------------------------------------------------
    // 详情
    // -----------------------------------------------------------------------

    #[OA\Get(
        path: '/api/persons/{id}',
        operationId: 'getPerson',
        description: '按 ID 查询单个人员',
        summary: '人员详情',
        tags: ['人员'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: '人员 ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', format: 'int64', example: 1),
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: '人员详情',
                content: new OA\JsonContent(
                    required: ['success', 'data'],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Person'),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: '404',
                description: '人员不存在',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'),
            ),
        ],
    )]
    public function show(Request $request, Response $response, array $args): Response
    {
        $id = (int)($args['id'] ?? 0);
        $person = $this->persons->find($id);

        if ($person === null) {
            return ApiResponse::error($response, '人员不存在', 404);
        }

        return ApiResponse::ok($response, $person->toArray());
    }

    // -----------------------------------------------------------------------
    // 新增
    // -----------------------------------------------------------------------

    #[OA\Post(
        path: '/api/persons',
        operationId: 'createPerson',
        summary: '新增人员',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', description: '姓名', example: '张三'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', description: '邮箱', example: 'zhangsan@example.com'),
                    new OA\Property(property: 'phone', type: 'string', nullable: true, description: '手机号', example: '13800138000'),
                ],
                type: 'object',
            ),
        ),
        tags: ['人员'],
        responses: [
            new OA\Response(
                response: '201',
                description: '创建成功',
                content: new OA\JsonContent(
                    required: ['success', 'data'],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: '创建成功'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Person'),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: '422',
                description: '参数校验失败',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'),
            ),
        ],
    )]
    public function create(Request $request, Response $response): Response
    {
        $body = (array)$request->getParsedBody();

        $name = trim((string)($body['name'] ?? ''));
        $email = trim((string)($body['email'] ?? ''));
        $phone = isset($body['phone']) ? trim((string)$body['phone']) : null;

        // 参数校验（类 Spring 的 @Valid / @NotNull）
        $errors = [];
        if ($name === '') {
            $errors['name'] = '姓名不能为空';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = '邮箱格式不正确';
        }
        if ($errors !== []) {
            return ApiResponse::error($response, '参数校验失败: ' . implode(', ', $errors), 422);
        }

        $person = $this->persons->create($name, $email, $phone ?: null);

        return ApiResponse::ok($response, $person->toArray(), '创建成功', 201);
    }

    // -----------------------------------------------------------------------
    // 更新
    // -----------------------------------------------------------------------

    #[OA\Put(
        path: '/api/persons/{id}',
        operationId: 'updatePerson',
        summary: '更新人员',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email'],
                properties: [
                    new OA\Property(property: 'name', description: '姓名', type: 'string', example: '张三'),
                    new OA\Property(property: 'email', description: '邮箱', type: 'string', format: 'email', example: 'zhangsan@example.com'),
                    new OA\Property(property: 'phone', description: '手机号', type: 'string', example: '13800138000', nullable: true),
                ],
                type: 'object',
            ),
        ),
        tags: ['人员'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: '人员 ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', format: 'int64', example: 1),
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: '更新成功',
                content: new OA\JsonContent(
                    required: ['success', 'data'],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: '更新成功'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/Person'),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: '404',
                description: '人员不存在',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'),
            ),
            new OA\Response(
                response: '422',
                description: '参数校验失败',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'),
            ),
        ],
    )]
    public function update(Request $request, Response $response, array $args): Response
    {
        $id = (int)($args['id'] ?? 0);
        $body = (array)$request->getParsedBody();

        $name = trim((string)($body['name'] ?? ''));
        $email = trim((string)($body['email'] ?? ''));
        $phone = isset($body['phone']) ? trim((string)$body['phone']) : null;

        $errors = [];
        if ($name === '') {
            $errors['name'] = '姓名不能为空';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = '邮箱格式不正确';
        }
        if ($errors !== []) {
            return ApiResponse::error($response, '参数校验失败: ' . implode(', ', $errors), 422);
        }

        $person = $this->persons->update($id, $name, $email, $phone ?: null);

        if ($person === null) {
            return ApiResponse::error($response, '人员不存在', 404);
        }

        return ApiResponse::ok($response, $person->toArray(), '更新成功');
    }

    // -----------------------------------------------------------------------
    // 删除
    // -----------------------------------------------------------------------

    #[OA\Delete(
        path: '/api/persons/{id}',
        operationId: 'deletePerson',
        summary: '删除人员',
        tags: ['人员'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: '人员 ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', format: 'int64', example: 1),
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: '删除成功',
                content: new OA\JsonContent(
                    required: ['success', 'message'],
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: '删除成功'),
                    ],
                    type: 'object',
                ),
            ),
            new OA\Response(
                response: '404',
                description: '人员不存在',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'),
            ),
        ],
    )]
    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = (int)($args['id'] ?? 0);

        if (!$this->persons->delete($id)) {
            return ApiResponse::error($response, '人员不存在', 404);
        }

        return ApiResponse::okMessage($response, '删除成功');
    }
}
