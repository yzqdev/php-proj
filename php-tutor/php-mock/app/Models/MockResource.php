<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 资源定义：描述一类 Mock 数据的字段 Schema（如 users）
 * fields 为 JSON 列，通过 $casts 自动序列化/反序列化
 */
class MockResource extends Model
{
    protected $fillable = ['name', 'fields', 'total'];

    protected $casts = [
        'fields' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function records(): HasMany
    {
        return $this->hasMany(MockRecord::class);
    }

    /**
     * Schema 中可用的字段类型白名单（FormRequest 校验与 FieldResolver 共用）
     */
    public const FIELD_TYPES = [
        'name', 'email', 'phone', 'address', 'company',
        'number', 'enum', 'bool', 'date', 'text', 'uuid', 'url', 'image',
    ];
}
