<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MockResource;
use Illuminate\Support\Facades\DB;

/**
 * Mock 数据生成器：按资源 Schema 批量生成记录并落库
 *
 * 核心思路：所有资源的数据统一写 mock_records.data（JSON 列），
 * 用分块批量 insert 避免 10000 条上限时逐条插入造成的性能问题。
 */
class DataGeneratorService
{
    /** 单次生成的记录数上限 */
    public const MAX_COUNT = 10000;

    /** 每批 insert 的行数（类比 MyBatis 的 batch insert size） */
    private const CHUNK_SIZE = 500;

    public function __construct(private readonly FieldResolver $resolver)
    {
    }

    /**
     * 清空旧数据后按 Schema 生成 count 条新记录
     *
     * @return int 实际生成并写入的记录数
     */
    public function generate(MockResource $resource, int $count): int
    {
        $count = min($count, self::MAX_COUNT);

        // 生成前先清空该资源全部旧记录（重新生成的语义是整体替换）
        $resource->records()->delete();

        $fields = $resource->fields ?? [];
        $now = now();

        collect(range(1, $count))
            ->map(fn () => [
                'mock_resource_id' => $resource->id,
                'data' => $this->buildRow($fields),
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->chunk(self::CHUNK_SIZE)
            ->each(fn ($chunk) => DB::table('mock_records')->insert($chunk->all()));

        $resource->update(['total' => $count]);

        return $count;
    }

    /**
     * 清空某资源的全部记录（不重新生成），并同步 total
     */
    public function clear(MockResource $resource): int
    {
        $deleted = $resource->records()->delete();
        $resource->update(['total' => 0]);

        return (int) $deleted;
    }

    /**
     * 按字段 Schema 生成一行数据，序列化为 JSON 字符串
     *
     * @param  array<int, array{name:string, type:string, ...}>  $fields
     * @return string JSON（DB::table 批量 insert 不走 Eloquent cast，需手动编码）
     */
    private function buildRow(array $fields): string
    {
        $row = [];
        foreach ($fields as $field) {
            $row[$field['name']] = $this->resolver->resolve($field);
        }

        return json_encode($row, JSON_UNESCAPED_UNICODE);
    }
}
