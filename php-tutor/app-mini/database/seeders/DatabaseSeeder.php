<?php
declare(strict_types=1);

namespace App\Database\Seeders;

use App\Database\Manager;
use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * 填充演示数据:5 个用户,每人 3~5 篇已发布文章。
 */
final class DatabaseSeeder
{
    public function __construct(private readonly Manager $manager)
    {
    }

    public function run(): void
    {
        // 关闭 Eloquent 守卫,使填充可批量赋值受保护字段
        Model::unguarded(function (): void {
            $faker = FakerFactory::create('zh_CN');

            for ($userIndex = 0; $userIndex < 5; $userIndex++) {
                $user = User::create([
                    'name' => $faker->name(),
                    'email' => $faker->unique()->safeEmail(),
                    'password' => password_hash('password123', PASSWORD_DEFAULT),
                ]);

                $postCount = random_int(3, 5);
                for ($postIndex = 0; $postIndex < $postCount; $postIndex++) {
                    $title = $faker->sentence(random_int(3, 8));
                    Post::create([
                        'title' => $title,
                        'slug' => Str::slug($title) . '-' . Str::lower(Str::random(6)),
                        'content' => $faker->paragraphs(random_int(2, 6), true),
                        'status' => PostStatus::Published,
                        'author_id' => (int) $user->id,
                    ]);
                }
            }
        });
    }
}
