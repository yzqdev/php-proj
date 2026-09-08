<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * 项目：Mock 数据平台的顶层归属单位
 * 与 Java 概念类比：类似 JPA 的 @Entity，Eloquent Model 自带 Active Record 能力
 */
class Project extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'api_key', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(MockResource::class);
    }

    /**
     * 生成全局唯一的 slug（用于公开 Mock URL 路径）
     */
    public static function makeUniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: Str::lower(Str::random(6));
        $slug = $base;
        $i = 1;
        while (self::where('slug', $slug)->exists()) {
            $slug = $base.'-'.++$i;
        }

        return $slug;
    }

    /**
     * 生成 64 位随机 api_key（类似 Java 中 UUID.randomUUID() 的无横线版本，但更长）
     */
    public static function makeApiKey(): string
    {
        return bin2hex(random_bytes(32));
    }
}
