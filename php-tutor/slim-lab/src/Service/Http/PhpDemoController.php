<?php

declare(strict_types=1);

namespace Service\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Service\ApiException;
use Service\PhpDemoService;

/**
 * /api/php-demo 控制器（兼容别名 /api/php-demo.php）：仅做 action 分发，业务逻辑在 PhpDemoService。
 */
final class PhpDemoController
{
    public function __construct(
        private readonly PhpDemoService $service,
    ) {
    }

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
