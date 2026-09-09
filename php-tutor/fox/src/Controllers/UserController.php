<?php

declare(strict_types=1);

namespace Yzqde\Fox\Controllers;

use Doctrine\ORM\EntityManager;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yzqde\Fox\Entity\User;
use Yzqde\Fox\Services\Logger;
use Yzqde\Fox\Support\BaseResponse;
use Yzqde\Fox\Util\Cache;

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
    public function __construct(
        private Logger $logger,
        private EntityManager $em,
    ) {
    }

    #[OA\Get(
        path: '/api/users',
        summary: 'Get all users',
        tags: ['User'],
        responses: [
            new OA\Response(response: '200', description: 'Success', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse'))
        ]
    )]
    #[Cache(ttl: 120)]
    public function index(Request $request, Response $response): Response
    {
        $this->logger->info('List users');
        $users = $this->em->getRepository(User::class)->findAll();

        $list = array_map(fn(User $u) => $u->toArray(), $users);

        return BaseResponse::list($response, $list, count($list));
    }

    #[OA\Get(
        path: '/api/users/{id}',
        summary: 'Get user by ID',
        tags: ['User'],
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
        $user = $this->em->find(User::class, $id);

        if (!$user) {
            return BaseResponse::error($response, 'User not found', BaseResponse::NOT_FOUND);
        }

        $this->logger->info('Get user', ['id' => $id]);

        return BaseResponse::success($response, $user->toArray());
    }

    #[OA\Post(
        path: '/api/users',
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
        tags: ['User'],
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

        $user = new User($data['name'], $data['email']);
        $this->em->persist($user);
        $this->em->flush();

        $this->logger->info('User created', ['id' => $user->getId()]);

        return BaseResponse::success($response, $user->toArray(), 'User created', BaseResponse::CREATED);
    }

    #[OA\Put(
        path: '/api/users/{id}',
        summary: 'Update a user',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'email', type: 'string'),
                ]
            )
        ),
        tags: ['User'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: '200', description: 'Success', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse')),
            new OA\Response(response: '404', description: 'Not found', content: new OA\JsonContent(ref: '#/components/schemas/ApiResponse'))
        ]
    )]
    public function update(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $user = $this->em->find(User::class, $id);

        if (!$user) {
            return BaseResponse::error($response, 'User not found', BaseResponse::NOT_FOUND);
        }

        $data = json_decode((string) $request->getBody(), true);

        if (!empty($data['name'])) {
            $user->setName($data['name']);
        }
        if (!empty($data['email'])) {
            $user->setEmail($data['email']);
        }

        $this->em->flush();

        $this->logger->info('User updated', ['id' => $id]);

        return BaseResponse::success($response, $user->toArray());
    }

    #[OA\Delete(
        path: '/api/users/{id}',
        summary: 'Delete a user',
        tags: ['User'],
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
        $user = $this->em->find(User::class, $id);

        if (!$user) {
            return BaseResponse::error($response, 'User not found', BaseResponse::NOT_FOUND);
        }

        $this->em->remove($user);
        $this->em->flush();

        $this->logger->info('User deleted', ['id' => $id]);

        return BaseResponse::noContent($response);
    }
}
