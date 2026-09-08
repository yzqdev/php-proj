<?php
declare(strict_types=1);

namespace App\Services;

use App\Enums\PostStatus;
use App\Exceptions\NotFoundException;
use App\Models\User;

/**
 * 用户公开资料:公开信息 + 其已发布文章。
 */
final class UserService
{
    /**
     * @throws NotFoundException
     */
    public function profile(int $id): User
    {
        $user = User::query()
            ->with([
                'posts' => static fn ($query) => $query
                    ->where('status', PostStatus::Published->value)
                    ->orderByDesc('id'),
            ])
            ->find($id);
        if ($user === null) {
            throw new NotFoundException('用户不存在');
        }

        // 公开资料不暴露邮箱
        return $user->makeHidden('email');
    }
}
