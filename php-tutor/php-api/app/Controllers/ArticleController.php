<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Article;
use App\Services\ArticleService;
use App\Support\Response;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Single-action controller dispatching by HTTP method:
 *   GET    /api/v1/articles        list
 *   GET    /api/v1/articles/{id}   show
 *   POST   /api/v1/articles        create   (JWT)
 *   PUT    /api/v1/articles/{id}   update   (JWT)
 *   DELETE /api/v1/articles/{id}   delete   (JWT)
 */
final class ArticleController
{
    public function __construct(private readonly ArticleService $articles)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $id = isset($request->getAttribute('routingArgs', [])['id'])
            ? (int) $request->getAttribute('routingArgs')['id']
            : (int) $request->getAttribute('id', 0);

        return match ($request->getMethod()) {
            'GET'    => $id > 0 ? $this->show($id) : $this->list($request),
            'POST'   => $this->create($request),
            'PUT'    => $this->update($request, $id),
            'DELETE' => $this->delete($request, $id),
            default  => Response::error(405, 'Method Not Allowed', null, 405),
        };
    }

    #[OA\Get(
        path: '/api/v1/articles',
        tags: ['Articles'],
        summary: '获取文章列表（分页）',
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 15)),
        ],
        responses: [
            new OA\Response(response: '200', description: '成功返回文章列表',
                content: new OA\JsonContent(ref: '#/components/schemas/ArticleList')
            ),
        ]
    )]
    private function list(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $page = (int) ($params['page'] ?? 1);
        $perPage = (int) ($params['per_page'] ?? 15);

        $paginator = $this->articles->list($page, $perPage);

        return Response::success([
            'items' => collect($paginator->items())->map(
                fn ($a) => $this->serialize($a)
            )->all(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
        ]);
    }

    #[OA\Get(
        path: '/api/v1/articles/{id}',
        tags: ['Articles'],
        summary: '获取单篇文章',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: '200', description: '成功返回文章详情',
                content: new OA\JsonContent(ref: '#/components/schemas/Article')
            ),
            new OA\Response(response: '404', description: '文章不存在'),
        ]
    )]
    private function show(int $id): ResponseInterface
    {
        return Response::success($this->serialize($this->articles->get($id)));
    }

    #[OA\Post(
        path: '/api/v1/articles',
        tags: ['Articles'],
        summary: '创建文章（需要 JWT）',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'body'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: '文章标题'),
                    new OA\Property(property: 'body', type: 'string', example: '文章内容'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: '201', description: '创建成功',
                content: new OA\JsonContent(ref: '#/components/schemas/Article')
            ),
            new OA\Response(response: '422', description: '验证失败'),
            new OA\Response(response: '401', description: '未认证'),
        ]
    )]
    private function create(ServerRequestInterface $request): ResponseInterface
    {
        $userId = (int) $request->getAttribute('user_id', 0);
        $input = (array) $request->getParsedBody();

        $article = $this->articles->create($userId, $input);

        return Response::success($this->serialize($article), 201);
    }

    #[OA\Put(
        path: '/api/v1/articles/{id}',
        tags: ['Articles'],
        summary: '更新文章（需要 JWT）',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: '新标题'),
                    new OA\Property(property: 'body', type: 'string', example: '新内容'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: '200', description: '更新成功',
                content: new OA\JsonContent(ref: '#/components/schemas/Article')
            ),
            new OA\Response(response: '404', description: '文章不存在'),
            new OA\Response(response: '401', description: '未认证'),
        ]
    )]
    private function update(ServerRequestInterface $request, int $id): ResponseInterface
    {
        $userId = (int) $request->getAttribute('user_id', 0);
        $input = (array) $request->getParsedBody();

        return Response::success($this->serialize($this->articles->update($userId, $id, $input)));
    }

    #[OA\Delete(
        path: '/api/v1/articles/{id}',
        tags: ['Articles'],
        summary: '删除文章（需要 JWT）',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: '200', description: '删除成功'),
            new OA\Response(response: '404', description: '文章不存在'),
            new OA\Response(response: '401', description: '未认证'),
        ]
    )]
    private function delete(ServerRequestInterface $request, int $id): ResponseInterface
    {
        $userId = (int) $request->getAttribute('user_id', 0);

        $this->articles->delete($userId, $id);

        return Response::success(['deleted' => true]);
    }

    private function serialize(object $a): array
    {
        return [
            'id'         => $a->id,
            'title'      => $a->title,
            'body'       => $a->body,
            'created_at' => (string) $a->created_at,
            'updated_at' => (string) $a->updated_at,
            'author'     => $a->relationLoaded('author')
                ? ['id' => $a->author->id, 'username' => $a->author->username]
                : null,
        ];
    }
}
