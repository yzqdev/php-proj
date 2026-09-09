<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\ApiException;
use App\Service\DoctrineDemoService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * /api/doctrine 控制器（兼容别名 /api/doctrine.php）：仅做 action 分发，业务逻辑在 DoctrineDemoService。
 */
final class DoctrineController
{
    public function __construct(
        private readonly DoctrineDemoService $service,
    ) {
    }

    public function handle(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $action = (string) ($request->getQueryParams()['action'] ?? '');

        $data = match ($action) {
            'init' => $this->service->init(),
            'create_users' => $this->service->createUsers(),
            'create_products' => $this->service->createProducts(),
            'find_user' => $this->service->findUser(),
            'dql_query' => $this->service->dqlQuery(),
            'join_query' => $this->service->joinQuery(),
            'update_user' => $this->service->updateUser(),
            'delete_product' => $this->service->deleteProduct(),
            'repository' => $this->service->repository(),
            'count' => $this->service->count(),
            'transaction' => $this->service->transaction(),
            'stats' => $this->service->stats(),
            default => throw new ApiException('未知操作'),
        };

        return ResponseFactory::ok($response, $data);
    }
}
