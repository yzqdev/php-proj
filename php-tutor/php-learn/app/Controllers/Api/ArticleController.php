<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Entities\Article;
use App\Exceptions\ResourceNotFoundException;
use App\Helpers\Config;
use App\Helpers\Html;
use App\Helpers\JsonResponse;
use App\Helpers\Logger;
use App\Helpers\RequestBody;
use App\Middleware\ApiAuthenticate;
use App\Resources\ArticleResource;
use Doctrine\ORM\EntityManager;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * ============================================================
 * 文章 API 控制器 — Slim 4 + Doctrine ORM
 * ============================================================
 *
 * 接口清单（RESTful）：
 *   GET    /api/v1/articles           列表（分页 + 分类/状态筛选）
 *   GET    /api/v1/articles/{id}      详情
 *   POST   /api/v1/articles           新建（需鉴权）
 *   PUT    /api/v1/articles/{id}      更新（需鉴权）
 *   DELETE /api/v1/articles/{id}      删除（需鉴权）
 *
 * 依赖注入：
 *   - EntityManager 通过构造器注入（php-di 自动装配）
 *   - 所有数据访问走 Doctrine Repository（不再手写 SQL）
 */
class ArticleController
{
    public function __construct(private readonly EntityManager $em)
    {
    }

    /**
     * GET /api/v1/articles
     *
     * 查询参数：page / pageSize / category / status
     *
     * @summary 文章列表
     * @description 支持分页 + 分类/状态筛选
     * @tag Articles
     * @parameter page integer 页码
     * @parameter pageSize integer 每页条数
     * @parameter category string 分类筛选（php/java/db/other）
     * @parameter status string 状态筛选（published/draft）
     * @response 200 ArticlePage 分页列表
     */
    #[OA\Get(
        path: '/articles',
        description: '支持分页 + 分类/状态筛选',
        summary: '文章列表',
        tags: ['Articles'],
        parameters: [
            new OA\Parameter(
                name: 'page',
                description: '页码',
                in: 'query',
                schema: new OA\Schema(type: 'integer', default: 1),
            ),
            new OA\Parameter(
                name: 'pageSize',
                description: '每页条数',
                in: 'query',
                schema: new OA\Schema(type: 'integer', default: 8, maximum: 100),
            ),
            new OA\Parameter(
                name: 'category',
                description: '分类筛选（php/java/db/other）',
                in: 'query',
                schema: new OA\Schema(type: 'string', enum: ['php', 'java', 'db', 'other']),
            ),
            new OA\Parameter(
                name: 'status',
                description: '状态筛选（published/draft）',
                in: 'query',
                schema: new OA\Schema(type: 'string', enum: ['draft', 'published']),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '分页列表',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ArticlePage',
                ),
            ),
        ],
    )]
    public function index(ServerRequestInterface $psr7): ResponseInterface
    {
        // GET 请求没有 body，所有参数从 query string 读
        // Slim 4 用 getQueryParams() 返回整个数组（不像 Slim 3 的 getQueryParam()）
        $params   = $psr7->getQueryParams();
        $page     = max(1, (int)($params['page'] ?? 1));
        $pageSize = min(100, max(1, (int)($params['pageSize'] ?? Config::get('paging.per_page', 8))));
        $category = (string)($params['category'] ?? '');
        $status   = (string)($params['status'] ?? '');

        // 分类白名单校验（非法值直接 422，不进入 DB 查询）
        if ($category !== '' && !in_array($category, [
            'php', 'java', 'db', 'other',
        ], true)) {
            return JsonResponse::validation([
                'category' => ['分类不合法，可选值：php, java, db, other'],
            ]);
        }

        $repo  = $this->em->getRepository(Article::class);
        $list  = $repo->paginate($page, $pageSize, $category, $status);
        $total = $repo->countFiltered($category, $status);

        $pageData = [
            'list'      => ArticleResource::collection($list),
            'total'     => $total,
            'page'      => $page,
            'pageSize'  => $pageSize,
            'totalPage' => $pageSize > 0 ? (int)ceil($total / $pageSize) : 0,
        ];

        return JsonResponse::ok($pageData);
    }

    /**
     * GET /api/v1/articles/{id}
     *
     * @summary 文章详情
     * @description 获取指定 ID 的文章详情
     * @tag Articles
     * @parameter id integer 文章 ID
     * @response 200 Article 文章详情
     * @response 404 文章不存在
     */
    #[OA\Get(
        path: '/articles/{id}',
        description: '获取指定 ID 的文章详情',
        summary: '文章详情',
        tags: ['Articles'],
        parameters: [
            new OA\PathParameter(
                name: 'id',
                description: '文章 ID',
                schema: new OA\Schema(type: 'integer', minimum: 1),
                example: 1,
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '文章详情',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/Article',
                ),
            ),
            new OA\Response(
                response: 404,
                description: '文章不存在',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                ),
            ),
        ],
    )]
    public function show(ServerRequestInterface $psr7): ResponseInterface
    {
        $id = (int)$psr7->getAttribute('id', 0);
        $article = $this->em->find(Article::class, $id);

        if ($article === null) {
            throw new ResourceNotFoundException('Article', $id);
        }

        return JsonResponse::ok(ArticleResource::make($article));
    }

    /**
     * POST /api/v1/articles（需鉴权）
     *
     * @summary 新建文章
     * @description 需要 Bearer Token
     * @tag Articles
     * @security
     * @requestBody ArticleCreateRequest
     * @response 201 Article 创建成功
     * @response 401 未登录
     * @response 422 参数校验失败
     */
    #[OA\Post(
        path: '/articles',
        description: '需要 Bearer Token',
        summary: '新建文章',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/ArticleCreateRequest',
            ),
        ),
        tags: ['Articles'],
        responses: [
            new OA\Response(
                response: 201,
                description: '创建成功',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/Article',
                ),
            ),
            new OA\Response(
                response: 401,
                description: '未登录',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                ),
            ),
            new OA\Response(
                response: 422,
                description: '参数校验失败',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                ),
            ),
        ],
    )]
    public function store(ServerRequestInterface $psr7): ResponseInterface
    {
        $body   = RequestBody::json($psr7);
        $errors = self::validateCreate($body);

        if ($errors !== []) {
            return JsonResponse::validation($errors);
        }

        $article = new Article();
        $article->setTitle(trim($body['title']))
                ->setBody(trim($body['body']))
                ->setCategory($body['category'])
                ->setStatus($body['status'] ?? 'published');

        $this->em->persist($article);
        $this->em->flush();

        Logger::info('文章创建成功', [
            'articleId' => $article->getId(),
            'title'     => $article->getTitle(),
            'userId'    => (int)(ApiAuthenticate::currentUser()['userId'] ?? 0),
        ]);

        return JsonResponse::ok(ArticleResource::make($article), '创建成功', 201);
    }

    /**
     * PUT /api/v1/articles/{id}（需鉴权）
     *
     * @summary 更新文章
     * @description PATCH 语义：只更新提交了字段
     * @tag Articles
     * @security
     * @parameter id integer 文章 ID
     * @requestBody ArticleUpdateRequest
     * @response 200 Article 更新成功
     * @response 401 未登录
     * @response 404 文章不存在
     * @response 422 参数校验失败
     */
    #[OA\Put(
        path: '/articles/{id}',
        description: 'PATCH 语义：只更新提交了字段',
        summary: '更新文章',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: '#/components/schemas/ArticleUpdateRequest',
            ),
        ),
        tags: ['Articles'],
        parameters: [
            new OA\PathParameter(
                name: 'id',
                description: '文章 ID',
                schema: new OA\Schema(type: 'integer', minimum: 1),
                example: 1,
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: '更新成功',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/Article',
                ),
            ),
            new OA\Response(
                response: 401,
                description: '未登录',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                ),
            ),
            new OA\Response(
                response: 404,
                description: '文章不存在',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                ),
            ),
            new OA\Response(
                response: 422,
                description: '参数校验失败',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                ),
            ),
        ],
    )]
    public function update(ServerRequestInterface $psr7): ResponseInterface
    {
        $id      = (int)$psr7->getAttribute('id', 0);
        $article = $this->em->find(Article::class, $id);
        if ($article === null) {
            throw new ResourceNotFoundException('Article', $id);
        }

        $body   = RequestBody::json($psr7);
        $errors = self::validateUpdate($body);

        if ($errors !== []) {
            return JsonResponse::validation($errors);
        }

        if (isset($body['title']))    $article->setTitle(trim($body['title']));
        if (isset($body['body']))     $article->setBody(trim($body['body']));
        if (isset($body['category'])) $article->setCategory($body['category']);
        if (isset($body['status']))   $article->setStatus($body['status']);

        $this->em->flush();

        Logger::info('文章更新成功', [
            'articleId' => $id,
            'fields'    => array_keys(array_filter($body, static fn($k) => in_array($k, ['title','body','category','status'], true), ARRAY_FILTER_USE_KEY)),
            'userId'    => (int)(ApiAuthenticate::currentUser()['userId'] ?? 0),
        ]);

        return JsonResponse::ok(ArticleResource::make($article), '更新成功');
    }

    /**
     * DELETE /api/v1/articles/{id}（需鉴权）
     *
     * @summary 删除文章
     * @tag Articles
     * @security
     * @parameter id integer 文章 ID
     * @response 204 删除成功（无 body）
     * @response 401 未登录
     * @response 404 文章不存在
     */
    #[OA\Delete(
        path: '/articles/{id}',
        summary: '删除文章',
        security: [['bearerAuth' => []]],
        tags: ['Articles'],
        parameters: [
            new OA\PathParameter(
                name: 'id',
                description: '文章 ID',
                schema: new OA\Schema(type: 'integer', minimum: 1),
                example: 1,
            ),
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: '删除成功（无 body）',
            ),
            new OA\Response(
                response: 401,
                description: '未登录',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                ),
            ),
            new OA\Response(
                response: 404,
                description: '文章不存在',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                ),
            ),
        ],
    )]
    public function destroy(ServerRequestInterface $psr7): ResponseInterface
    {
        $id      = (int)$psr7->getAttribute('id', 0);
        $article = $this->em->find(Article::class, $id);

        if ($article === null) {
            throw new ResourceNotFoundException('Article', $id);
        }

        $this->em->remove($article);
        $this->em->flush();

        Logger::info('文章删除成功', [
            'articleId' => $id,
            'userId'    => (int)(ApiAuthenticate::currentUser()['userId'] ?? 0),
        ]);

        return JsonResponse::noContent();
    }

    // =====================================================================
    // 校验
    // =====================================================================

    /**
     * 校验新建数据
     *
     * @return array 字段级错误；空数组表示通过
     */
    private static function validateCreate(array $body): array
    {
        $errors = self::validateFields($body);

        foreach (['title', 'body', 'category'] as $required) {
            if (!isset($body[$required]) || $body[$required] === '') {
                $errors[$required][] = "{$required} 不能为空";
            }
        }

        return $errors;
    }

    /**
     * 校验更新数据（PATCH 语义：至少要提供一个可更新字段）
     */
    private static function validateUpdate(array $body): array
    {
        $errors = self::validateFields($body);

        $editable = array_intersect_key($body, array_flip(['title', 'body', 'category', 'status']));
        if ($editable === []) {
            $errors['_'] = ['未提供任何可更新字段'];
        }

        return $errors;
    }

    /**
     * 通用字段校验
     */
    private static function validateFields(array $body): array
    {
        $errors = [];

        $title = (string)($body['title'] ?? '');
        if ($title !== '') {
            if (Html::charLen($title) < 2) {
                $errors['title'][] = '标题至少 2 个字符';
            } elseif (Html::charLen($title) > 80) {
                $errors['title'][] = '标题最多 80 个字符';
            }
        }

        $bodyText = (string)($body['body'] ?? '');
        if ($bodyText !== '') {
            if (Html::charLen($bodyText) < 5) {
                $errors['body'][] = '正文至少 5 个字符';
            }
        }

        $category = (string)($body['category'] ?? '');
        if ($category !== '' && !in_array($category, ['php', 'java', 'db', 'other'], true)) {
            $errors['category'][] = '分类不合法，可选值：php, java, db, other';
        }

        $status = (string)($body['status'] ?? '');
        if ($status !== '' && !in_array($status, ['draft', 'published'], true)) {
            $errors['status'][] = '状态不合法，可选值：draft, published';
        }

        return $errors;
    }
}
