<?php

declare(strict_types=1);

namespace Yzqde\Fox\Controllers;

use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yzqde\Fox\Services\Logger;

class HomeController
{
    public function __construct(private Logger $logger)
    {
    }

    #[OA\Get(
        path: '/',

        summary: 'Home page',

        tags: ['Home'],

        responses: [
            new OA\Response(response: '200', description: 'Success', content: new OA\JsonContent(type: 'string'))
        ]
    )]
    public function index(Request $request, Response $response, $args): Response
    {
        $this->logger->info('Home page accessed');
        $response->getBody()->write("Hello, Fox!");
        return $response;
    }

    #[OA\Get(
        path: '/hello/{name}',
        summary: 'Say hello to someone',
        tags: ['Home'],
        parameters: [
            new OA\Parameter(name: 'name', in: 'path', required: true, schema: new OA\Schema(type: 'string'))
        ],
        responses: [
            new OA\Response(response: '200', description: 'Success', content: new OA\JsonContent(type: 'string'))
        ]
    )]
    public function hello(Request $request, Response $response, $args): Response
    {
        $name = $args['name'];
        $this->logger->info('Hello endpoint accessed', ['name' => $name]);
        $response->getBody()->write("Hello, $name!");
        return $response;
    }
}
