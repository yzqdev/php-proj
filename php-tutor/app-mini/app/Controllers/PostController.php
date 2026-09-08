<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\ValidationException;
use App\Http\ApiResponder;
use App\Services\PostService;
use App\Validation\Rules;
use App\Validation\Validator;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * 文章接口:公开列表/详情,作者创建/更新/删除。
 */
final class PostController
{
    public function __construct(
        private readonly PostService $postService,
        private readonly Validator $validator,
    ) {
    }

    #[OA\Get(path: '/api/v1/posts', tags: ['Posts'], summary: '文章分页列表(仅已发布)')]
    #[OA\Parameter(name: 'page', in: 'query', description: '页码', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, default: 1))]
    #[OA\Parameter(name: 'per_page', in: 'query', description: '每页数量', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, default: 15))]
    #[OA\Parameter(name: 'author_id', in: 'query', description: '按作者过滤', required: false, schema: new OA\Schema(type: 'integer', minimum: 1))]
    #[OA\Parameter(name: 'q', in: 'query', description: '标题模糊搜索', required: false, schema: new OA\Schema(type: 'string', maxLength: 100))]
    #[OA\Response(response: 200, description: '分页列表', content: new OA\JsonContent(ref: '#/components/schemas/PostList'))]
    #[OA\Response(response: 422, description: '查询参数校验失败', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $query = array_filter(
            $request->getQueryParams(),
            static fn ($value) => $value !== '' && $value !== null,
        );
        $validated = $this->validator->validate($query, Rules::postListQuery());

        $page = isset($validated['page']) ? (int) $validated['page'] : 1;
        $perPage = isset($validated['per_page']) ? (int) $validated['per_page'] : 15;
        $authorId = isset($validated['author_id']) ? (int) $validated['author_id'] : null;
        $q = $validated['q'] ?? null;

        $paginator = $this->postService->paginatePublished($page, $perPage, $authorId, $q);

        return ApiResponder::paginated($response, $paginator);
    }

    #[OA\Get(path: '/api/v1/posts/{id}', tags: ['Posts'], summary: '文章详情(仅已发布)')]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1))]
    #[OA\Response(response: 200, description: '文章详情', content: new OA\JsonContent(ref: '#/components/schemas/Post'))]
    #[OA\Response(response: 404, description: '文章不存在', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function show(ServerRequestInterface $request, ResponseInterface $response, string $id): ResponseInterface
    {
        $post = $this->postService->findPublished((int) $id);

        return ApiResponder::success($response, $post);
    }

    #[OA\Post(path: '/api/v1/posts', tags: ['Posts'], summary: '创建文章(需登录)', security: [['bearerAuth' => []]])]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/PostCreateRequest'))]
    #[OA\Response(response: 201, description: '创建成功', content: new OA\JsonContent(ref: '#/components/schemas/Post'))]
    #[OA\Response(response: 401, description: '未认证', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    #[OA\Response(response: 422, description: '参数校验失败', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function create(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userId = (int) $request->getAttribute('userId');
        $data = $this->validator->validate((array) $request->getParsedBody(), Rules::postCreate());
        $post = $this->postService->create($userId, $data);

        return ApiResponder::success($response, $post, 201);
    }

    #[OA\Put(path: '/api/v1/posts/{id}', tags: ['Posts'], summary: '更新文章(需作者)', security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1))]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/PostUpdateRequest'))]
    #[OA\Response(response: 200, description: '更新成功', content: new OA\JsonContent(ref: '#/components/schemas/Post'))]
    #[OA\Response(response: 401, description: '未认证', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    #[OA\Response(response: 403, description: '无权操作(非作者)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    #[OA\Response(response: 404, description: '文章不存在', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    #[OA\Response(response: 422, description: '参数校验失败', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function update(ServerRequestInterface $request, ResponseInterface $response, string $id): ResponseInterface
    {
        $userId = (int) $request->getAttribute('userId');
        $data = $this->validator->validate((array) $request->getParsedBody(), Rules::postUpdate());

        // 全部字段缺失时拒绝空更新
        if (array_filter($data, static fn ($value) => $value !== null) === []) {
            throw new ValidationException('至少提供一个待更新字段');
        }

        $post = $this->postService->update((int) $id, $userId, $data);

        return ApiResponder::success($response, $post);
    }

    #[OA\Delete(path: '/api/v1/posts/{id}', tags: ['Posts'], summary: '删除文章(需作者)', security: [['bearerAuth' => []]])]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1))]
    #[OA\Response(response: 204, description: '删除成功')]
    #[OA\Response(response: 401, description: '未认证', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    #[OA\Response(response: 403, description: '无权操作(非作者)', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    #[OA\Response(response: 404, description: '文章不存在', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function delete(ServerRequestInterface $request, ResponseInterface $response, string $id): ResponseInterface
    {
        $userId = (int) $request->getAttribute('userId');
        $this->postService->delete((int) $id, $userId);

        return $response->withStatus(204);
    }
}
