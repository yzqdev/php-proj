<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Mock 记录：所有资源生成的数据统一存本表的 data JSON 列，禁止动态建表
 */
class MockRecord extends Model
{
    protected $fillable = ['mock_resource_id', 'data'];

    // data JSON 列自动在 数组 <=> JSON 之间转换（类比 Java 的 Jackson @JsonRawValue 场景）
    protected $casts = [
        'data' => 'array',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(MockResource::class, 'mock_resource_id');
    }
}
