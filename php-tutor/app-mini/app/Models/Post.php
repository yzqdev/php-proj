<?php
declare(strict_types=1);

namespace App\Models;

use App\Enums\PostStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 文章模型。
 *
 * @property int            $id
 * @property string         $title
 * @property string         $slug
 * @property string         $content
 * @property PostStatus     $status
 * @property int            $author_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Post extends Model
{
    protected $table = 'posts';

    protected $fillable = ['title', 'slug', 'content', 'status', 'author_id'];

    protected $casts = [
        'id' => 'int',
        'author_id' => 'int',
        'status' => PostStatus::class,
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** 仅已发布文章。 */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PostStatus::Published->value);
    }

    /** 标题模糊搜索。 */
    public function scopeSearch(Builder $query, ?string $q): Builder
    {
        if ($q === null || $q === '') {
            return $query;
        }

        return $query->where('title', 'like', '%' . $q . '%');
    }
}
