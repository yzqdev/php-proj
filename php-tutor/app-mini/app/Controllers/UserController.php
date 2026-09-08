<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Http\ApiResponder;
use App\Services\UserService;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * 用户接口:公开资料 + 已发布文章。
 */
final class UserController
{
    public function __construct(private readonly UserService $userService)
    {
    }

    #[OA\Get(path: '/api/v1/users/{id}', summary: '用户公开资料及已发布文章', tags: ['Users'])]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', minimum: 1))]
    #[OA\Response(response: 200, description: '用户公开资料', content: new OA\JsonContent(ref: '#/components/schemas/UserProfile'))]
    #[OA\Response(response: 404, description: '用户不存在', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'))]
    public function show(ServerRequestInterface $request, ResponseInterface $response, string $id): ResponseInterface
    {
        $user = $this->userService->profile((int) $id);

        return ApiResponder::success($response, $user);
    }
}
