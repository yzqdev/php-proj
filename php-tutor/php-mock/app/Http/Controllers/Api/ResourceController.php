<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreResourceRequest;
use App\Models\MockResource;
use App\Models\Project;
use App\Services\DataGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 资源（Schema）管理接口（需登录）
 *
 * 鉴权约定：登录用户只能操作自己项目下的资源，
 * 越权访问一律 404，不泄露资源是否存在。
 */
class ResourceController extends Controller
{
    public function __construct(private readonly DataGeneratorService $generator)
    {
    }

    /**
     * 定义资源，定义成功后立即按 count（缺省 20，上限 10000）生成数据
     */
    public function store(Request $request, Project $project, StoreResourceRequest $resourceRequest): JsonResponse
    {
        $this->assertOwned($request, $project);

        $name = (string) $resourceRequest->input('name');

        // 同一 project 下资源名唯一，重复定义返回 409
        if ($project->resources()->where('name', $name)->exists()) {
            return ApiResponse::error(409, "资源 [{$name}] 已存在");
        }

        $resource = $project->resources()->create([
            'name' => $name,
            'fields' => $resourceRequest->input('fields'),
            'total' => 0,
        ]);

        $count = $this->generator->generate($resource, (int) $resourceRequest->input('count', 20));

        return ApiResponse::success([
            'resource' => $resource->refresh(),
            'generated' => $count,
        ], status: 201);
    }

    /**
     * 资源详情：Schema + 数据量
     */
    public function show(Request $request, MockResource $resource): JsonResponse
    {
        $this->assertResourceOwned($request, $resource);

        return ApiResponse::success($resource->loadCount('records'));
    }

    /**
     * 重新生成：先清空旧数据，再按 count 生成
     */
    public function generate(Request $request, MockResource $resource): JsonResponse
    {
        $this->assertResourceOwned($request, $resource);

        $count = $this->generator->generate($resource, (int) $request->input('count', 20));

        return ApiResponse::success(['resource' => $resource->refresh(), 'generated' => $count]);
    }

    /**
     * 清空数据（保留 Schema，不重新生成）
     */
    public function clearRecords(Request $request, MockResource $resource): JsonResponse
    {
        $this->assertResourceOwned($request, $resource);

        $deleted = $this->generator->clear($resource);

        return ApiResponse::success(['deleted' => $deleted]);
    }

    private function assertOwned(Request $request, Project $project): void
    {
        if ($project->user_id !== $request->user()->id) {
            abort(404, '资源不存在');
        }
    }

    private function assertResourceOwned(Request $request, MockResource $resource): void
    {
        $this->assertOwned($request, $resource->project);
    }
}
