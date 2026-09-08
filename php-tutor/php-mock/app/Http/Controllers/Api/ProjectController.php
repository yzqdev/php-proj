<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 项目管理接口（需登录）
 */
class ProjectController extends Controller
{
    /**
     * 我的项目列表（附带每个项目的资源数）
     */
    public function index(Request $request): JsonResponse
    {
        $projects = $request->user()
            ->projects()
            ->withCount('resources')
            ->latest()
            ->get();

        return ApiResponse::success($projects);
    }

    /**
     * 创建项目：自动生成唯一 slug 与 64 位 api_key
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = Project::create([
            'name' => (string) $request->input('name'),
            'slug' => Project::makeUniqueSlug((string) $request->input('name')),
            'api_key' => Project::makeApiKey(),
            'user_id' => $request->user()->id,
        ]);

        return ApiResponse::success($project, status: 201);
    }

    public function show(Request $request, Project $project): JsonResponse
    {
        $this->assertOwned($request, $project);

        return ApiResponse::success($project->load('resources'));
    }

    /**
     * 只能操作自己的项目，越权按 404 处理（不泄露存在性）
     */
    private function assertOwned(Request $request, Project $project): void
    {
        abort_if($project->user_id !== $request->user()->id, 404, '资源不存在');
    }
}
