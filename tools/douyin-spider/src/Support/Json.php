<?php

declare(strict_types=1);

namespace Yzqde\DouyinSpider\Support;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\StreamFactory;

/**
 * 辅助：将 JSON 数据写入 PSR-7 Response
 *
 * 统一放在 Support 命名空间，供 Controller 与 Middleware 共享，
 * 避免同一命名空间内多文件定义同名函数导致 Cannot redeclare。
 */
function json(ResponseInterface $response, mixed $data, int $status = 200): ResponseInterface
{
    $streamFactory = new StreamFactory();
    $stream = $streamFactory->createStream(json_encode($data, JSON_UNESCAPED_UNICODE));

    return (new ResponseFactory())
        ->createResponse($status)
        ->withHeader('Content-Type', 'application/json; charset=utf-8')
        ->withBody($stream);
}
