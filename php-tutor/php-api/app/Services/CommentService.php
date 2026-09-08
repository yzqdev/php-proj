<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Article;
use App\Models\Comment;
use App\Support\ForbiddenException;
use App\Support\NotFoundException;
use App\Validation\Validator as InputValidator;
use App\Validation\Rules\CommentCreate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Comment business logic.
 */
final class CommentService
{
    public function listForArticle(int $articleId, int $page, int $perPage): LengthAwarePaginator
    {
        // Ensure the article exists (404 otherwise).
        if (!Article::query()->whereKey($articleId)->exists()) {
            throw new NotFoundException('文章不存在');
        }

        return Comment::query()
            ->with('author:id,username')
            ->where('article_id', $articleId)
            ->orderByDesc('created_at')
            ->paginate(
                perPage: max(1, min($perPage, 100)),
                page: max(1, $page)
            );
    }

    public function create(int $userId, int $articleId, array $input): Comment
    {
        if (!Article::query()->whereKey($articleId)->exists()) {
            throw new NotFoundException('文章不存在');
        }

        $data = InputValidator::validate($input, CommentCreate::rules());

        return Comment::query()->create([
            'article_id' => $articleId,
            'user_id'    => $userId,
            'content'    => $data['content'],
        ]);
    }

    public function delete(int $userId, int $id): void
    {
        $comment = Comment::query()->find($id);
        if ($comment === null) {
            throw new NotFoundException('评论不存在');
        }

        if ((int) $comment->user_id !== $userId) {
            throw new ForbiddenException('只有作者才能删除该评论');
        }

        $comment->delete();
    }
}
