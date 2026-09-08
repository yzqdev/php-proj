<?php
declare(strict_types=1);

namespace App\Services;

use App\Enums\PostStatus;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Http\Pagination;
use App\Models\Post;
use Illuminate\Support\Str;

/**
 * 文章业务:公开查询、作者管理(创建/更新/删除)。
 * 所有查询走 Eloquent,不写原生 SQL;业务异常统一抛自定义异常。
 * 分页手写 count + forPage,避免依赖 illuminate/pagination(属 Laravel 其他组件)。
 */
final class PostService
{
    /**
     * 公开分页列表:仅已发布文章,支持作者过滤与标题模糊搜索。
     */
    public function paginatePublished(int $page, int $perPage, ?int $authorId, ?string $q): Pagination
    {
        $base = Post::query()
            ->published()
            ->when($authorId !== null, static fn ($query) => $query->where('author_id', $authorId))
            ->when($q !== null && $q !== '', static fn ($query) => $query->where('title', 'like', '%' . $q . '%'));

        $total = (int) $base->count();
        $items = $base->with('author')
            ->orderByDesc('id')
            ->forPage($page, $perPage)
            ->get()
            ->all();

        $lastPage = $perPage > 0 ? (int) ceil($total / $perPage) : 1;

        return new Pagination(
            items: $items,
            total: $total,
            page: $page,
            perPage: $perPage,
            lastPage: max(1, $lastPage),
        );
    }

    /**
     * 公开详情:仅已发布文章。
     *
     * @throws NotFoundException
     */
    public function findPublished(int $id): Post
    {
        $post = Post::query()->published()->with('author')->find($id);
        if ($post === null) {
            throw new NotFoundException('文章不存在');
        }

        return $post;
    }

    /**
     * 作者创建文章。
     *
     * @param array{title: string, content: string, status?: ?string} $data 已校验数据
     */
    public function create(int $authorId, array $data): Post
    {
        $status = $data['status'] ?? PostStatus::Draft->value;

        return Post::create([
            'title' => trim($data['title']),
            'slug' => $this->generateUniqueSlug($data['title']),
            'content' => $data['content'],
            'status' => $status,
            'author_id' => $authorId,
        ])->load('author');
    }

    /**
     * 作者更新文章;标题变化时重新生成 slug 并校验唯一性。
     *
     * @param array{title?: ?string, content?: ?string, status?: ?string} $data 已校验数据
     *
     * @throws NotFoundException
     * @throws ForbiddenException 非作者
     */
    public function update(int $id, int $authorId, array $data): Post
    {
        $post = $this->requireAuthorPost($id, $authorId);

        if (array_key_exists('title', $data) && $data['title'] !== null) {
            $post->title = trim($data['title']);
            $post->slug = $this->generateUniqueSlug($data['title'], (int) $post->id);
        }
        if (array_key_exists('content', $data) && $data['content'] !== null) {
            $post->content = $data['content'];
        }
        if (array_key_exists('status', $data) && $data['status'] !== null) {
            $post->status = $data['status'];
        }
        $post->save();

        return $post->load('author');
    }

    /**
     * @throws NotFoundException
     * @throws ForbiddenException 非作者
     */
    public function delete(int $id, int $authorId): void
    {
        $this->requireAuthorPost($id, $authorId)->delete();
    }

    /**
     * 生成唯一 slug:标题拼音化,冲突时追加 -2/-3...
     */
    private function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        if ($base === '') {
            $base = 'post';
        }

        $slug = $base;
        $suffix = 2;
        while ($this->slugExists($slug, $ignoreId)) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $ignoreId): bool
    {
        $query = Post::query()->where('slug', $slug);
        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    /**
     * 取回文章并校验作者归属,失败抛 404/403。
     *
     * @throws NotFoundException
     * @throws ForbiddenException
     */
    private function requireAuthorPost(int $id, int $authorId): Post
    {
        $post = Post::query()->find($id);
        if ($post === null) {
            throw new NotFoundException('文章不存在');
        }
        if ((int) $post->author_id !== $authorId) {
            throw new ForbiddenException('无权操作该文章');
        }

        return $post;
    }
}
