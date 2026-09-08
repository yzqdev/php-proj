<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\ArticleService;
use App\Support\Response;
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
        // Route args are injected as request attributes by the PHP-DI bridge.
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

    private function show(int $id): ResponseInterface
    {
        return Response::success($this->serialize($this->articles->get($id)));
    }

    private function create(ServerRequestInterface $request): ResponseInterface
    {
        $userId = (int) $request->getAttribute('user_id', 0);
        $input = (array) $request->getParsedBody();

        $article = $this->articles->create($userId, $input);

        return Response::success($this->serialize($article), 201);
    }

    private function update(ServerRequestInterface $request, int $id): ResponseInterface
    {
        $userId = (int) $request->getAttribute('user_id', 0);
        $input = (array) $request->getParsedBody();

        return Response::success($this->serialize($this->articles->update($userId, $id, $input)));
    }

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
