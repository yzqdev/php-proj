<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Mock;

use App\Http\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\MockRecord;
use App\Models\MockResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 公开 Mock API：一个控制器服务所有资源的 CRUD，不写死任何模板
 *
 * 通过 project slug + resource name 动态定位资源，
 * 数据统一读写 mock_records.data JSON 列（data->field 语法，MySQL JSON 路径查询）。
 */
class MockApiController extends Controller
{
    /** size 参数上限，防止一次性拉全量数据 */
    private const MAX_SIZE = 100;

    private const DEFAULT_SIZE = 20;

    /**
     * GET /api/v1/mock/{project}/{resource}
     *
     * 支持参数：page / size / sort（前缀 - 降序）/ keyword + search_in（JSON 字段模糊搜索）
     */
    public function index(Request $request, string $projectSlug, string $resourceName): JsonResponse
    {
        $resource = $this->locateResource($projectSlug, $resourceName);

        $query = MockRecord::query()->where('mock_resource_id', $resource->id);

        // keyword + search_in：对 data JSON 内的指定字段做 like 模糊搜索
        $keyword = trim((string) $request->query('keyword', ''));
        $searchIn = $this->parseSearchIn($request);
        if ($keyword !== '' && $searchIn !== []) {
            $query->where(function ($q) use ($searchIn, $keyword) {
                foreach ($searchIn as $field) {
                    // where("data->field", ...) => MySQL: json_extract(`data`, '$."field"') LIKE ...
                    $q->orWhere("data->{$field}", 'like', "%{$keyword}%");
                }
            });
        }

        // sort：字段名升序，前缀 - 降序，如 sort=-id；id 视为主键，其余按 JSON 字段排
        $sort = (string) $request->query('sort', '-id');
        [$sortField, $direction] = str_starts_with($sort, '-')
            ? [substr($sort, 1), 'desc']
            : [$sort, 'asc'];
        $sortColumn = $sortField === 'id'
            ? 'id'
            : "data->{$sortField}";
        $query->orderBy($sortColumn, $direction);

        $size = min((int) $request->query('size', self::DEFAULT_SIZE), self::MAX_SIZE);
        $size = max($size, 1);

        // 列表返回时把记录 id 合并进 data 一起输出
        $page = $query->paginate($size, ['id', 'data'])->withQueryString();

        return ApiResponse::paginated(
            $page->through(fn (MockRecord $record) => ['id' => $record->id] + ($record->data ?? []))
        );
    }

    /**
     * GET /api/v1/mock/{project}/{resource}/{id}
     */
    public function show(string $projectSlug, string $resourceName, int $id): JsonResponse
    {
        $resource = $this->locateResource($projectSlug, $resourceName);

        $record = $resource->records()->where('id', $id)->firstOrFail();

        return ApiResponse::success(['id' => $record->id] + ($record->data ?? []));
    }

    /**
     * POST /api/v1/mock/{project}/{resource}：真写库，body 即 data
     */
    public function store(Request $request, string $projectSlug, string $resourceName): JsonResponse
    {
        $resource = $this->locateResource($projectSlug, $resourceName);

        $data = $this->extractData($request);
        if ($data === []) {
            return ApiResponse::error(422, '请求体不能为空，请传入要写入的 JSON 数据');
        }

        $record = $resource->records()->create(['data' => $data]);
        $resource->increment('total');

        return ApiResponse::success(['id' => $record->id] + ($record->data ?? []), status: 201);
    }

    /**
     * PUT|PATCH /api/v1/mock/{project}/{resource}/{id}：合并更新 data
     */
    public function update(Request $request, string $projectSlug, string $resourceName, int $id): JsonResponse
    {
        $resource = $this->locateResource($projectSlug, $resourceName);

        $record = $resource->records()->where('id', $id)->firstOrFail();

        $data = array_merge($record->data ?? [], $this->extractData($request));
        $record->update(['data' => $data]);

        return ApiResponse::success(['id' => $record->id] + ($record->data ?? []));
    }

    /**
     * DELETE /api/v1/mock/{project}/{resource}/{id}
     */
    public function destroy(string $projectSlug, string $resourceName, int $id): JsonResponse
    {
        $resource = $this->locateResource($projectSlug, $resourceName);

        $record = $resource->records()->where('id', $id)->firstOrFail();
        $record->delete();
        $resource->decrement('total');

        return ApiResponse::success(['deleted' => $record->id]);
    }

    /**
     * 按 slug + 资源名定位资源，找不到统一 404
     */
    private function locateResource(string $projectSlug, string $resourceName): MockResource
    {
        $resource = MockResource::query()
            ->whereHas('project', fn ($q) => $q->where('slug', $projectSlug))
            ->where('name', $resourceName)
            ->first();

        if ($resource === null) {
            abort(404, "Mock 资源不存在：/api/v1/mock/{$projectSlug}/{$resourceName}");
        }

        return $resource;
    }

    /**
     * search_in 支持 "name,company"（逗号分隔）或 ?search_in[]=name&search_in[]=company
     *
     * @return string[]
     */
    private function parseSearchIn(Request $request): array
    {
        $raw = $request->query('search_in', []);

        return collect(is_string($raw) ? explode(',', $raw) : (array) $raw)
            ->map(fn ($f) => trim((string) $f))
            ->filter(fn ($f) => $f !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * 取请求体 JSON 为 data（JSON 请求自动解码；表单请求透传）
     *
     * @return array<string, mixed>
     */
    private function extractData(Request $request): array
    {
        $data = $request->json()->all();

        return is_array($data) ? $data : [];
    }
}
