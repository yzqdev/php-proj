<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Article;
use App\Models\User;
use App\Support\ForbiddenException;
use App\Support\NotFoundException;
use App\Validation\Validator as InputValidator;
use App\Validation\Rules\ArticleCreate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Article business logic.
 */
final class ArticleService
{
    public function list(int $page, int $perPage): LengthAwarePaginator
    {
        return Article::query()
            ->with('author:id,username')
            ->orderByDesc('created_at')
            ->paginate(
                perPage: max(1, min($perPage, 100)),
                page: max(1, $page)
            );
    }

    public function get(int $id): Article
    {
        $article = Article::query()->with('author:id,username')->find($id);
        if ($article === null) {
            throw new NotFoundException('文章不存在');
        }
        return $article;
    }

    public function create(int $userId, array $input): Article
    {
        $data = InputValidator::validate($input, ArticleCreate::rules());

        return Article::query()->create([
            'user_id' => $userId,
            'title'   => $data['title'],
            'body'    => $data['body'],
        ]);
    }

    public function update(int $userId, int $id, array $input): Article
    {
        $article = $this->get($id);

        if ((int) $article->user_id !== $userId) {
            throw new ForbiddenException('只有作者才能修改该文章');
        }

        $data = InputValidator::validate($input, ArticleCreate::rules());

        $article->fill(['title' => $data['title'], 'body' => $data['body']]);
        $article->save();

        return $article;
    }

    public function delete(int $userId, int $id): void
    {
        $article = $this->get($id);

        if ((int) $article->user_id !== $userId) {
            throw new ForbiddenException('只有作者才能删除该文章');
        }

        $article->delete();
    }
}
