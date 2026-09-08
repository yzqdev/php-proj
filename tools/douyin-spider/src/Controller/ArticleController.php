<?php

declare(strict_types=1);

namespace Yzqde\DouyinSpider\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yzqde\DouyinSpider\DTO\ArticleCreateDto;
use Yzqde\DouyinSpider\Exception\AuthorizationException;
use Yzqde\DouyinSpider\Service\ArticleService;

use function Yzqde\DouyinSpider\Support\json;

class ArticleController
{
    public function __construct(
        private readonly ArticleService $articleService,
    ) {
    }

    /**
     * GET /api/articles?page=1&limit=20
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $query = $request->getQueryParams();
        $page = max(1, (int) ($query['page'] ?? 1));
        $limit = min(100, max(1, (int) ($query['limit'] ?? 20)));
        $result = $this->articleService->list($page, $limit);

        $items = array_map(fn ($article) => $this->formatArticle($article), $result['items']);

        return json($response, [
            'code' => 0,
            'message' => 'success',
            'data' => [
                'list' => $items,
                'total' => $result['total'],
                'page' => $result['page'],
                'limit' => $result['limit'],
            ],
        ]);
    }

    /**
     * GET /api/articles/{id}
     */
    public function show(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $article = $this->articleService->findById($id);

        if ($article === null) {
            return json($response, [
                'code' => -4,
                'message' => '文章不存在',
                'data' => null,
            ], 404);
        }

        $this->articleService->incrementViewCount($article);

        return json($response, [
            'code' => 0,
            'message' => 'success',
            'data' => $this->formatArticle($article),
        ]);
    }

    /**
     * POST /api/articles
     */
    public function store(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $authUser = $request->getAttribute('authUser');
        $this->checkPermission($authUser['role'] ?? '');

        $body = (array) $request->getParsedBody();
        $dto = new ArticleCreateDto(
            title: $body['title'] ?? '',
            content: $body['content'] ?? '',
            summary: $body['summary'] ?? null,
        );

        if ($dto->title === '' || $dto->content === '') {
            return json($response, [
                'code' => -1,
                'message' => '标题和内容不能为空',
                'data' => null,
            ], 400);
        }

        $article = $this->articleService->create($dto, $authUser['id']);

        return json($response, [
            'code' => 0,
            'message' => '创建成功',
            'data' => [
                'id' => $article->getId(),
                'title' => $article->getTitle(),
                'created_at' => $article->getCreatedAt()->format('Y-m-d H:i:s'),
            ],
        ], 201);
    }

    /**
     * PUT /api/articles/{id}
     */
    public function update(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $authUser = $request->getAttribute('authUser');
        $this->checkPermission($authUser['role'] ?? '');

        $id = (int) $args['id'];
        $article = $this->articleService->findById($id);
        if ($article === null) {
            return json($response, [
                'code' => -4,
                'message' => '文章不存在',
                'data' => null,
            ], 404);
        }

        $body = (array) $request->getParsedBody();
        $dto = new ArticleCreateDto(
            title: $body['title'] ?? $article->getTitle(),
            content: $body['content'] ?? $article->getContent(),
            summary: $body['summary'] ?? $article->getSummary(),
        );

        $this->articleService->update($article, $dto);

        return json($response, [
            'code' => 0,
            'message' => '更新成功',
            'data' => [
                'id' => $article->getId(),
                'title' => $article->getTitle(),
                'updated_at' => $article->getUpdatedAt()->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * DELETE /api/articles/{id}
     */
    public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $authUser = $request->getAttribute('authUser');
        $this->checkPermission($authUser['role'] ?? '');

        $id = (int) $args['id'];
        $article = $this->articleService->findById($id);
        if ($article === null) {
            return json($response, [
                'code' => -4,
                'message' => '文章不存在',
                'data' => null,
            ], 404);
        }

        $this->articleService->delete($article);

        return json($response, [
            'code' => 0,
            'message' => '删除成功',
            'data' => null,
        ]);
    }

    private function checkPermission(string $role): void
    {
        if (!in_array($role, ['editor', 'admin'], true)) {
            throw new AuthorizationException('无权执行此操作');
        }
    }

    /**
     * 将文章实体格式化为响应数组
     */
    private function formatArticle(object $article): array
    {
        $author = $article->getAuthor();
        return [
            'id' => $article->getId(),
            'title' => $article->getTitle(),
            'content' => $article->getContent(),
            'summary' => $article->getSummary(),
            'view_count' => $article->getViewCount(),
            'author' => $author ? [
                'id' => $author->getId(),
                'username' => $author->getUsername(),
            ] : null,
            'created_at' => $article->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $article->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
