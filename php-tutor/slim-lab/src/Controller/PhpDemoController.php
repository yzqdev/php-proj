<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\ApiException;
use App\Service\PhpDemoService;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * /api/php-demo 控制器（兼容别名 /api/php-demo.php）：仅做 action 分发。
 */
final class PhpDemoController
{
    public function __construct(
        private readonly PhpDemoService $service,
    ) {
    }

    #[OA\Get(
        path: '/api/php-demo',
        summary: 'PHP 演示',
        tags: ['PHP Demo'],
        parameters: [
            new OA\Parameter(
                name: 'action',
                in: 'query',
                required: true,
                description: '演示项',
                schema: new OA\Schema(
                    type: 'string',
                    enum: [
                        'array_ops',
                        'string_ops',
                        'date_ops',
                        'json_ops',
                        'regex_ops',
                        'file_ops',
                        'random_ops',
                        'filter_ops',
                        'closure_ops',
                    ],
                    example: 'array_ops'
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: '成功',
                content: new OA\JsonContent(ref: '#/components/schemas/PhpDemoData')
            ),
            new OA\Response(response: '400', description: '未知操作'),
            new OA\Response(response: '500', description: '服务器错误'),
        ]
    )]
    public function handle(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $action = (string) ($request->getQueryParams()['action'] ?? '');

        $data = match ($action) {
            'array_ops' => $this->service->arrayOps(),
            'string_ops' => $this->service->stringOps(),
            'date_ops' => $this->service->dateOps(),
            'json_ops' => $this->service->jsonOps(),
            'regex_ops' => $this->service->regexOps(),
            'file_ops' => $this->service->fileOps(),
            'random_ops' => $this->service->randomOps(),
            'filter_ops' => $this->service->filterOps(),
            'closure_ops' => $this->service->closureOps(),
            default => throw new ApiException('未知操作'),
        };

        return ResponseFactory::ok($response, $data);
    }
}
