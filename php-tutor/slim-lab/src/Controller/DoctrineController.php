<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\ApiException;
use App\Service\DoctrineDemoService;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * /api/doctrine 控制器（兼容别名 /api/doctrine.php）：仅做 action 分发。
 */

final class DoctrineController
{
    public function __construct(
        private readonly DoctrineDemoService $service,
    ) {
    }
    #[OA\Get(
        path: '/api/doctrine',
        tags: ['Doctrine'],
        summary: 'Doctrine ORM 演示',
        parameters: [
            new OA\Parameter(
                name: 'action',
                in: 'query',
                required: true,
                description: '演示项',
                schema: new OA\Schema(
                    type: 'string',
                    enum: [
                        'init',
                        'create_users',
                        'create_products',
                        'find_user',
                        'dql_query',
                        'join_query',
                        'update_user',
                        'delete_product',
                        'repository',
                        'count',
                        'transaction',
                        'stats',
                    ],
                    example: 'stats'
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: '成功',
                content: new OA\JsonContent(ref: '#/components/schemas/DoctrineData')
            ),
            new OA\Response(response: '400', description: '未知操作'),
            new OA\Response(response: '500', description: '服务器错误'),
        ]
    )]
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
