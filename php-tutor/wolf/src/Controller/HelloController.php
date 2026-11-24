<?php

declare(strict_types=1);

namespace Yzqde\Wolf\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class HelloController
{
    public function hello(Request $request, Response $response): Response
    {
        $payload = json_encode(
            ['message' => 'Hello, World!'],
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE,
        );
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
}
