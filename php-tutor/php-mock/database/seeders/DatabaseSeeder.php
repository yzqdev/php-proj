<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use App\Services\DataGeneratorService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * 默认数据：demo 用户 + demo 项目 + users 资源 + 50 条生成好的数据
     */
    public function run(DataGeneratorService $generator): void
    {
        $user = User::create([
            'name' => 'demo',
            'email' => 'demo@mock.dev',
            'password' => 'password',
        ]);

        $project = Project::create([
            'name' => 'demo',
            'slug' => 'demo',
            'api_key' => Project::makeApiKey(),
            'user_id' => $user->id,
        ]);

        $resource = $project->resources()->create([
            'name' => 'users',
            'fields' => [
                ['name' => 'nickname', 'type' => 'name'],
                ['name' => 'email', 'type' => 'email'],
                ['name' => 'phone', 'type' => 'phone'],
                ['name' => 'age', 'type' => 'number', 'min' => 18, 'max' => 60],
                ['name' => 'status', 'type' => 'enum', 'options' => ['active', 'disabled']],
                ['name' => 'avatar', 'type' => 'image', 'width' => 200, 'height' => 200],
                ['name' => 'bio', 'type' => 'text', 'length' => 200],
            ],
            'total' => 0,
        ]);

        $generator->generate($resource, 50);
    }
}
