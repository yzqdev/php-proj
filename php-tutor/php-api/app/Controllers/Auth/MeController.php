<?php
declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Models\User;
use App\Support\NotFoundException;
use App\Support\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * GET /api/v1/auth/me — current authenticated user.
 */
final class MeController
{
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $userId = (int) $request->getAttribute('user_id', 0);

        $user = User::query()->find($userId);
        if ($user === null) {
            throw new NotFoundException('用户不存在');
        }

        return Response::success(
            $user->only(['id', 'username', 'email', 'created_at', 'updated_at'])
        );
    }
}
