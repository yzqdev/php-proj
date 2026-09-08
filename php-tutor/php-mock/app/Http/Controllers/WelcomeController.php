<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\MockRecord;
use App\Models\MockResource;

/**
 * 欢迎页面控制器
 *
 * 演示 Blade 模板的数据传递：变量、循环、条件判断、
 * 路由辅助函数、组件插槽等用法。
 */
class WelcomeController extends Controller
{
    public function index()
    {
        return view('welcome', [
            // 页面标题
            'title' => 'Mock API 平台',
            // 功能特性列表（演示 foreach）
            'features' => [
                ['icon' => '🔌', 'name' => '定义 Schema', 'desc' => '通过 JSON 描述资源字段与类型'],
                ['icon' => '🎭', 'name' => '批量生成假数据', 'desc' => '基于 fakerphp 生成真实感测试数据'],
                ['icon' => '🌐', 'name' => '公开 CRUD 接口', 'desc' => '无需认证即可增删改查 Mock 数据'],
                ['icon' => '📚', 'name' => 'OpenAPI 文档', 'desc' => 'Swagger UI 实时查看接口文档'],
                ['icon' => '🔑', 'name' => 'Sanctum 认证', 'desc' => 'Bearer Token 管理项目与资源'],
            ],
            // 统计数据（演示条件判断 + 空值处理）
            'stats' => [
                'projects' => Project::count(),
                'resources' => MockResource::count(),
                'records' => MockRecord::count(),
            ],
            // 演示用户是否登录（auth() 门面）
            'user' => auth()->user(),
        ]);
    }
}