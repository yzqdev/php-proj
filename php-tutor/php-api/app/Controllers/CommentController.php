<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\CommentService;
use App\Support\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Single-action controller dispatching by HTTP method:
 *   GET    /api/v1/articles/{id}/comments  list for article
 *   POST   /api/v1/articles/{id}/comments  create (JWT)
 *   DELETE /api/v1/comments/{id}           delete (JWT)
 */
final class CommentController
{
    public function __construct(private readonly CommentService $comments)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        // Route args are injected as request attributes by the PHP-DI bridge.
        $id = (int) ($request->getAttribute('routingArgs', [])['id']
            ?? $request->getAttribute('id', 0));

        return match ($request->getMethod()) {
            'GET'    => $this->list($request, $id),
            'POST'   => $this->create($request, $id),
            'DELETE' => $this->delete($request, $id),
            default  => Response::error(405, 'Method Not Allowed', null, 405),
        };
    }

    private function list(ServerRequestInterface $request, int $articleId): ResponseInterface
    {
        $params = $request->getQueryParams();
        $page = (int) ($params['page'] ?? 1);
        $perPage = (int) ($params['per_page'] ?? 15);

        $paginator = $this->comments->listForArticle($articleId, $page, $perPage);

        return Response::success([
            'items' => collect($paginator->items())->map(
                fn ($c) => $this->serialize($c)
            )->all(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
        ]);
    }

    private function create(ServerRequestInterface $request, int $articleId): ResponseInterface
    {
        $userId = (int) $request->getAttribute('user_id', 0);
        $input = (array) $request->getParsedBody();

        $comment = $this->comments->create($userId, $articleId, $input);

        return Response::success($this->serialize($comment), 201);
    }

    private function delete(ServerRequestInterface $request, int $id): ResponseInterface
    {
        $userId = (int) $request->getAttribute('user_id', 0);

        $this->comments->delete($userId, $id);

        return Response::success(['deleted' => true]);
    }

    private function serialize(object $c): array
    {
        return [
            'id'         => $c->id,
            'article_id' => $c->article_id,
            'content'    => $c->content,
            'created_at' => (string) $c->created_at,
            'author'     => $c->relationLoaded('author')
                ? ['id' => $c->author->id, 'username' => $c->author->username]
                : null,
        ];
    }
}
