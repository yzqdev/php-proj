<?php

declare(strict_types=1);

namespace Service\Http\Docs;

use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Service\Http\OpenApi as Schema;

/**
 * OpenAPI 文档：
 *   - GET /docs/openapi.json  运行时扫描 app/src 目录实时生成 OpenAPI JSON（不依赖手动命令）
 *   - GET /docs               Swagger UI（CDN 版，数据源指向 /docs/openapi.json）
 *
 * 接口操作注解集中在本类。注意：clouddrive 的多个 action 共用同一个 URL
 * （/api/clouddrive），因此按 HTTP 方法合并为两个操作，action 参数以枚举完整列出，
 * 参数与响应均逐项说明，文档覆盖全部接口行为。
 */
final class DocsController
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
    ) {
    }

    /**
     * 运行时生成 OpenAPI JSON。
     */
    public function openapiJson(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $root = dirname(__DIR__, 4);
        $generator = new \OpenApi\Generator();
        $openapi = $generator->generate([
            $root . '/app',
            $root . '/src',
        ]);

        $response->getBody()->write($openapi?->toJson() ?? '{}');

        return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
    }

    /**
     * Swagger UI 页面（CDN 引入 swagger-ui-dist@5）。
     */
    public function ui(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $html = <<<'HTML'
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>slim-lab API 文档</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.17.14/swagger-ui.css">
</head>
<body>
  <div id="swagger-ui"></div>
  <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.17.14/swagger-ui-bundle.js" crossorigin></script>
  <script>
    window.addEventListener('load', function () {
      window.ui = SwaggerUIBundle({
        url: '/docs/openapi.json',
        dom_id: '#swagger-ui',
        deepLinking: true,
        withCredentials: true,
        persistAuthorization: true,
        displayRequestDuration: true
      });
    });
  </script>
</body>
</html>
HTML;

        $response->getBody()->write($html);

        return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
    }

    // ------------------------------------------------------------------
    // php 模块（原 /?module=php）
    // ------------------------------------------------------------------

    #[OA\Get(
        path: '/api/php-demo',
        operationId: 'phpDemoAction',
        summary: 'PHP 演示：统一 action 入口',
        description: '按 action 执行只读的 PHP 语言特性演示。data.message 为 JSON 字符串（含中文与换行）。',
        tags: ['php'],
        parameters: [
            new OA\Parameter(
                name: 'action',
                in: 'query',
                required: true,
                description: 'array_ops=数组操作；string_ops=字符串；date_ops=日期；json_ops=JSON；regex_ops=正则；file_ops=文件；random_ops=随机与加密；filter_ops=过滤验证；closure_ops=闭包',
                schema: new OA\Schema(type: 'string', enum: ['array_ops', 'string_ops', 'date_ops', 'json_ops', 'regex_ops', 'file_ops', 'random_ops', 'filter_ops', 'closure_ops'], example: 'array_ops'),
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: '演示结果（data.message）',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/ApiEnvelope'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(property: 'data', ref: '#/components/schemas/MessageData'),
                            ],
                        ),
                    ],
                ),
            ),
            new OA\Response(response: '400', description: '未知操作（action 不在枚举内）', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: '500', description: '服务器内部错误', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ],
    )]
    public function docPhpDemo(): void
    {
    }

    // ------------------------------------------------------------------
    // doctrine 模块（原 /?module=doctrine）
    // ------------------------------------------------------------------

    #[OA\Get(
        path: '/api/doctrine',
        operationId: 'doctrineAction',
        summary: 'Doctrine ORM 演示：统一 action 入口',
        description: '按 action 执行 Doctrine ORM 演示（建表/插入/查询/更新/删除/事务/统计）。init 会重建全部表（清空数据）。',
        tags: ['doctrine'],
        parameters: [
            new OA\Parameter(
                name: 'action',
                in: 'query',
                required: true,
                description: 'init=重建表；create_users=插入用户；create_products=插入商品；find_user=按ID查询；dql_query=DQL模糊查询；join_query=LEFT JOIN；update_user=更新；delete_product=删除；repository=findAll；count=COUNT统计；transaction=事务回滚；stats=数量统计',
                schema: new OA\Schema(type: 'string', enum: ['init', 'create_users', 'create_products', 'find_user', 'dql_query', 'join_query', 'update_user', 'delete_product', 'repository', 'count', 'transaction', 'stats'], example: 'stats'),
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: '演示结果；action=stats 时 data 为 {userCount, productCount}，其余为 data.message 文本',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/ApiEnvelope'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    oneOf: [
                                        new OA\Schema(ref: '#/components/schemas/MessageData'),
                                        new OA\Schema(ref: '#/components/schemas/DoctrineStatsData'),
                                    ],
                                ),
                            ],
                        ),
                    ],
                ),
            ),
            new OA\Response(response: '400', description: '未知操作 / 数据未初始化等业务错误', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: '500', description: '服务器内部错误（如数据库不可用）', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ],
    )]
    public function docDoctrine(): void
    {
    }

    // ------------------------------------------------------------------
    // clouddrive 模块（原 /?module=clouddrive）—— GET 类 action
    // ------------------------------------------------------------------

    #[OA\Get(
        path: '/api/clouddrive',
        operationId: 'clouddriveGetAction',
        summary: '网盘：GET 类 action（logout/check/get_share_list/list_dir/download/zip/stream）',
        description: '登录与查询类接口返回 JSON 信封；download/zip/stream 为流式文件输出。除 logout/check/stream 外均需登录（未登录返回 401）。action 不在枚举内返回 400 未知操作。',
        tags: ['clouddrive'],
        security: [['SessionCookie' => []]],
        parameters: [
            new OA\Parameter(
                name: 'action',
                in: 'query',
                required: true,
                description: 'logout=退出登录；check=查询登录状态；get_share_list=分享列表；list_dir=列出目录；download=下载文件；zip=打包文件夹；stream=在线预览（免登录，CORS 为 *）',
                schema: new OA\Schema(type: 'string', enum: ['logout', 'check', 'get_share_list', 'list_dir', 'download', 'zip', 'stream'], example: 'list_dir'),
            ),
            new OA\Parameter(name: 'dir', in: 'query', required: false, description: 'list_dir/download/zip 使用：相对目录（空=根目录）', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sortby', in: 'query', required: false, description: 'list_dir 使用：排序字段', schema: new OA\Schema(type: 'string', enum: ['name', 'mtime', ''])),
            new OA\Parameter(name: 'sortorder', in: 'query', required: false, description: 'list_dir 使用：排序方向', schema: new OA\Schema(type: 'string', enum: ['asc', 'desc', ''])),
            new OA\Parameter(name: 'file', in: 'query', required: false, description: 'download 使用：文件名；stream 使用：相对根目录的文件路径', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'folder', in: 'query', required: false, description: 'zip 使用：要打包的文件夹名', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'JSON 类 action 返回信封（logout/check → LoginData；get_share_list → ShareListData；list_dir → DirListData）；download/zip/stream 返回二进制文件流',
                content: [
                    new OA\MediaType(
                        mediaType: 'application/json',
                        schema: new OA\Schema(
                            allOf: [
                                new OA\Schema(ref: '#/components/schemas/ApiEnvelope'),
                                new OA\Schema(
                                    properties: [
                                        new OA\Property(
                                            property: 'data',
                                            oneOf: [
                                                new OA\Schema(ref: '#/components/schemas/LoginData'),
                                                new OA\Schema(ref: '#/components/schemas/ShareListData'),
                                                new OA\Schema(ref: '#/components/schemas/DirListData'),
                                            ],
                                        ),
                                    ],
                                ),
                            ],
                        ),
                    ),
                    new OA\MediaType(
                        mediaType: 'application/octet-stream',
                        schema: new OA\Schema(type: 'string', format: 'binary'),
                    ),
                ],
            ),
            new OA\Response(response: '400', description: '未知操作 / 文件不存在', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: '401', description: '未登录', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: '500', description: '服务器内部错误', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ],
    )]
    public function docCloudGet(): void
    {
    }

    // ------------------------------------------------------------------
    // clouddrive 模块 —— POST 类 action
    // ------------------------------------------------------------------

    #[OA\Post(
        path: '/api/clouddrive',
        operationId: 'clouddrivePostAction',
        summary: '网盘：POST 类 action（login/global_search/create_share/delete_share/mkdir/upload/change_pwd/delete/batch_delete/rename/move）',
        description: '表单编码（multipart/form-data）。除 login 外均需登录（401）。POST 之外的 HTTP 方法返回 405 方法不允许。action 不在枚举内返回 400 未知操作。',
        tags: ['clouddrive'],
        security: [['SessionCookie' => []]],
        parameters: [
            new OA\Parameter(
                name: 'action',
                in: 'query',
                required: true,
                description: 'login=登录；global_search=全局搜索；create_share=创建分享；delete_share=删除分享；mkdir=新建文件夹；upload=上传文件；change_pwd=修改密码；delete=删除；batch_delete=批量删除；rename=重命名；move=移动',
                schema: new OA\Schema(type: 'string', enum: ['login', 'global_search', 'create_share', 'delete_share', 'mkdir', 'upload', 'change_pwd', 'delete', 'batch_delete', 'rename', 'move'], example: 'login'),
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: '各 action 所需字段（均 multipart/form-data）：'
                . 'login: pwd；global_search: kw；create_share: current_folder, share_file, is_dir_share(0/1), share_pwd, share_expire(小时,0=永久)；'
                . 'delete_share: token；mkdir: current_folder, target_dir；upload: current_folder, file(binary), relative_path(可选)；'
                . 'change_pwd: new_pwd1, new_pwd2；delete: current_folder, name；batch_delete: current_folder, batch_list[](重复字段)；'
                . 'rename: current_folder, old_name, new_name；move: current_folder, src_name, dst_dir',
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'pwd', type: 'string', description: 'login：登录密码（默认 123456，可由 storage/pwd.config.txt 覆盖）'),
                        new OA\Property(property: 'kw', type: 'string', description: 'global_search：搜索关键字（大小写不敏感，空=空列表）'),
                        new OA\Property(property: 'current_folder', type: 'string', description: '当前目录（空=根目录），多数写操作必填'),
                        new OA\Property(property: 'share_file', type: 'string', description: 'create_share：要分享的名称'),
                        new OA\Property(property: 'is_dir_share', type: 'string', enum: ['0', '1'], description: 'create_share：1=分享文件夹'),
                        new OA\Property(property: 'share_pwd', type: 'string', description: 'create_share：提取密码'),
                        new OA\Property(property: 'share_expire', type: 'string', description: 'create_share：有效期小时数，0=永久', example: '24'),
                        new OA\Property(property: 'token', type: 'string', description: 'delete_share：分享 token'),
                        new OA\Property(property: 'target_dir', type: 'string', description: 'mkdir：新目录名'),
                        new OA\Property(property: 'file', type: 'string', format: 'binary', description: 'upload：上传的文件'),
                        new OA\Property(property: 'relative_path', type: 'string', description: 'upload：可选相对路径（目录不存在时自动创建）'),
                        new OA\Property(property: 'new_pwd1', type: 'string', description: 'change_pwd：新密码'),
                        new OA\Property(property: 'new_pwd2', type: 'string', description: 'change_pwd：确认新密码'),
                        new OA\Property(property: 'name', type: 'string', description: 'delete：要删除的名称'),
                        new OA\Property(property: 'batch_list[]', type: 'array', items: new OA\Items(type: 'string'), description: 'batch_delete：批量删除名称列表'),
                        new OA\Property(property: 'old_name', type: 'string', description: 'rename：原名称'),
                        new OA\Property(property: 'new_name', type: 'string', description: 'rename：新名称'),
                        new OA\Property(property: 'src_name', type: 'string', description: 'move：要移动的名称'),
                        new OA\Property(property: 'dst_dir', type: 'string', description: 'move：目标目录（空=根目录）'),
                    ],
                ),
            ),
        ),
        responses: [
            new OA\Response(
                response: '200',
                description: 'login → data:{login:true}（message 登录成功）；global_search → SearchListData；create_share → ShareCreateData；'
                    . 'delete_share/mkdir/upload/change_pwd/delete/batch_delete/rename/move → data:null，message 为对应成功文案（删除成功/目录创建成功/目录已存在/上传成功/密码修改成功，请重新登录/删除成功/批量删除成功/重命名成功/移动成功）',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/ApiEnvelope'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    oneOf: [
                                        new OA\Schema(ref: '#/components/schemas/LoginData'),
                                        new OA\Schema(ref: '#/components/schemas/SearchListData'),
                                        new OA\Schema(ref: '#/components/schemas/ShareCreateData'),
                                        new OA\Schema(type: 'object', nullable: true, example: null),
                                    ],
                                ),
                            ],
                        ),
                    ],
                ),
            ),
            new OA\Response(response: '400', description: '密码错误/目录名称不能为空/文件为空/两次密码不一致/重命名失败/移动失败/分享不存在/非法目录路径/目录创建失败等业务错误', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: '401', description: '未登录', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: '405', description: '方法不允许（非 POST）', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: '500', description: '服务器内部错误', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ],
    )]
    public function docCloudPost(): void
    {
    }

    // ------------------------------------------------------------------
    // 旧版直连入口兼容（原 index.php 的 /?module=xxx&action=yyy）
    // ------------------------------------------------------------------

    #[OA\Get(
        path: '/',
        operationId: 'legacyModuleDispatch',
        summary: '【兼容】旧版直连入口：/?module=xxx&action=yyy',
        description: '迁移前 index.php 的模块分发入口。module=php|doctrine|clouddrive 时行为与 /api/{module}.php 完全一致；module 缺失或未知时返回 404 未知模块（与迁移前一致）。此路径亦是未匹配路由的 404 兜底。',
        deprecated: true,
        tags: ['legacy'],
        parameters: [
            new OA\Parameter(name: 'module', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['php', 'doctrine', 'clouddrive'])),
            new OA\Parameter(name: 'action', in: 'query', required: false, description: '与对应模块的 action 参数一致', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: '200', description: '与对应模块接口一致的响应', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: '404', description: '未知模块 / 未匹配路由', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ],
    )]
    public function docLegacyDispatch(): void
    {
    }

    // ------------------------------------------------------------------
    // 运行日志
    // ------------------------------------------------------------------

    #[OA\Get(
        path: '/api/logs/days',
        operationId: 'logDays',
        summary: '可查日志日期列表',
        description: '列出 storage/logs 下按天切分的日志文件（新→旧）。仅返回日期与文件大小，不读文件内容。',
        tags: ['logs'],
        security: [['BearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                required: false,
                description: '最多返回几天（1~90，默认 30）',
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 90, default: 30, example: 30),
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: '日期列表',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/ApiEnvelope'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'object',
                                    required: ['days'],
                                    properties: [
                                        new OA\Property(property: 'days', type: 'array', items: new OA\Items(ref: '#/components/schemas/LogDay')),
                                    ],
                                ),
                            ],
                        ),
                    ],
                ),
            ),
            new OA\Response(response: '401', description: '未登录或令牌失效', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: '500', description: '服务器内部错误', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ],
    )]
    public function docLogDays(): void
    {
    }

    #[OA\Get(
        path: '/api/logs',
        operationId: 'logReadDay',
        summary: '按天分页读取日志',
        description: '读取指定日期的日志窗口，单次遍历文件（内存占用与文件总行数无关）。日期须为 YYYY-MM-DD；未传时默认今天。',
        tags: ['logs'],
        security: [['BearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'date',
                in: 'query',
                required: false,
                description: '日志日期 YYYY-MM-DD，缺省为今天；当日无日志文件时返回 total=0 与空列表',
                schema: new OA\Schema(type: 'string', format: 'date', example: '2026-09-08'),
            ),
            new OA\Parameter(
                name: 'offset',
                in: 'query',
                required: false,
                description: '起始行号（从 0 开始，负数按 0 处理）',
                schema: new OA\Schema(type: 'integer', minimum: 0, default: 0, example: 0),
            ),
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                required: false,
                description: '返回行数（上限 2000）',
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 2000, default: 500, example: 500),
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: '该日日志窗口',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/ApiEnvelope'),
                        new OA\Schema(
                            properties: [
                                new OA\Property(property: 'data', ref: '#/components/schemas/LogData'),
                            ],
                        ),
                    ],
                ),
            ),
            new OA\Response(response: '400', description: '日期格式非法', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: '401', description: '未登录或令牌失效', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: '500', description: '日志读取失败', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ],
    )]
    public function docLogReadDay(): void
    {
    }

    // ------------------------------------------------------------------
    // 文档接口自身
    // ------------------------------------------------------------------

    #[OA\Get(
        path: '/docs/openapi.json',
        operationId: 'docsOpenapiJson',
        summary: 'OpenAPI JSON（运行时扫描生成）',
        tags: ['docs'],
        responses: [
            new OA\Response(response: '200', description: 'OpenAPI 3.0 JSON', content: new OA\MediaType(mediaType: 'application/json')),
            new OA\Response(response: '500', description: '扫描/生成失败', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ],
    )]
    public function docOpenapiJson(): void
    {
    }

    #[OA\Get(
        path: '/docs',
        operationId: 'docsUi',
        summary: 'Swagger UI 页面',
        tags: ['docs'],
        responses: [
            new OA\Response(response: '200', description: 'HTML 页面', content: new OA\MediaType(mediaType: 'text/html')),
        ],
    )]
    public function docUi(): void
    {
    }
}
