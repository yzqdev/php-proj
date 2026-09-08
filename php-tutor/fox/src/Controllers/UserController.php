<?php

declare(strict_types=1);

namespace Yzqde\Fox\Controllers;

use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yzqde\Fox\Services\Logger;
use Yzqde\Fox\Services\Store;
use Yzqde\Fox\Support\BaseResponse;

#[OA\Schema(
    schema: 'User',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'email', type: 'string'),
        new OA\Property(property: 'created_at', type: 'string'),
    ]
)]
class UserController
{
    private Store $store;

    public function __construct(private Logger $logger)
    {
        $this->store = new Store();
    }

    #[OA\Get(
        path: '/api/users',
        tags: ['User'],
        summary: 'Get all users',
        responses: [
            new OA\Response(response: '200', description: 'Success', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse'))
        ]
    )]
    public function index(Request $request, Response $response): Response
    {
        $this->logger->info('List users');
        $users = $this->store->all();

        return BaseResponse::list($response, $users, count($users));
    }

    #[OA\Get(
        path: '/api/users/{id}',
        tags: ['User'],
        summary: 'Get user by ID',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: '200', description: 'Success', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: '404', description: 'Not found', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse'))
        ]
    )]
    public function show(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $user = $this->store->find($id);

        if (!$user) {
            return BaseResponse::error($response, 'User not found', BaseResponse::NOT_FOUND);
        }

        $this->logger->info('Get user', ['id' => $id]);

        return BaseResponse::success($response, $user);
    }

    #[OA\Post(
        path: '/api/users',
        tags: ['User'],
        summary: 'Create a new user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'email', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: '201', description: 'Created', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: '400', description: 'Bad request', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse'))
        ]
    )]
    public function store(Request $request, Response $response): Response
    {
        $data = json_decode((string) $request->getBody(), true);

        if (empty($data['name']) || empty($data['email'])) {
            return BaseResponse::error($response, 'Name and email are required');
        }

        $user = $this->store->create([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        $this->logger->info('User created', ['id' => $user['id']]);

        return BaseResponse::success($response, $user, 'User created', BaseResponse::CREATED);
    }

    #[OA\Put(
        path: '/api/users/{id}',
        tags: ['User'],
        summary: 'Update a user',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'email', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: '200', description: 'Success', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: '404', description: 'Not found', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse'))
        ]
    )]
    public function update(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $data = json_decode((string) $request->getBody(), true);
        $user = $this->store->update($id, $data);

        if (!$user) {
            return BaseResponse::error($response, 'User not found', BaseResponse::NOT_FOUND);
        }

        $this->logger->info('User updated', ['id' => $id]);

        return BaseResponse::success($response, $user);
    }

    #[OA\Delete(
        path: '/api/users/{id}',
        tags: ['User'],
        summary: 'Delete a user',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: '204', description: 'Deleted'),
            new OA\Response(response: '404', description: 'Not found', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse'))
        ]
    )]
    public function destroy(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];

        if (!$this->store->delete($id)) {
            return BaseResponse::error($response, 'User not found', BaseResponse::NOT_FOUND);
        }

        $this->logger->info('User deleted', ['id' => $id]);

        return BaseResponse::noContent($response);
    }
}
