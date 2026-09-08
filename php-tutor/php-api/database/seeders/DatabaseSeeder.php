<?php
declare(strict_types=1);

namespace App\Database\Seeders;

use App\Models\User;
use App\Models\Article;
use App\Models\Comment;
use Faker\Factory as FakerFactory;
use Faker\Generator;

/**
 * Seeds users / articles / comments with Faker zh_CN data.
 * Idempotent: clears tables first, then inserts.
 */
final class DatabaseSeeder
{
    public function run(): void
    {
        // Faker MUST be created with an explicit locale (hard constraint 9),
        // otherwise English names/addresses will be generated.
        $faker = FakerFactory::create('zh_CN');

        // Truncate in FK-safe order.
        Comment::query()->delete();
        Article::query()->delete();
        User::query()->delete();

        // ---- Users -----------------------------------------------------
        $users = collect();
        for ($i = 0; $i < 10; $i++) {
            $users->push(User::query()->create([
                'username' => $faker->unique()->userName(),
                'email'    => $faker->unique()->safeEmail(),
                'password' => password_hash('password123', PASSWORD_BCRYPT),
            ]));
        }

        // One well-known test account.
        $demo = User::query()->create([
            'username' => 'demo',
            'email'    => 'demo@example.com',
            'password' => password_hash('demo1234', PASSWORD_BCRYPT),
        ]);
        $users->push($demo);

        // ---- Articles --------------------------------------------------
        $articles = collect();
        foreach ($users as $user) {
            for ($j = 0; $j < 3; $j++) {
                $articles->push(Article::query()->create([
                    'user_id' => $user->id,
                    'title'   => $faker->sentence(6),
                    'body'    => implode("\n\n", [
                        $faker->paragraph(4),
                        $faker->paragraph(4),
                        $faker->paragraph(2),
                    ]),
                ]));
            }
        }

        // ---- Comments --------------------------------------------------
        foreach ($articles as $article) {
            $count = random_int(0, 4);
            for ($k = 0; $k < $count; $k++) {
                Comment::query()->create([
                    'article_id' => $article->id,
                    'user_id'    => $users->random()->id,
                    'content'    => $faker->paragraph(2),
                ]);
            }
        }

        echo sprintf(
            'Seeded %d users, %d articles, %d comments.' . PHP_EOL,
            User::query()->count(),
            Article::query()->count(),
            Comment::query()->count(),
        );
        echo 'Test account: demo / demo1234' . PHP_EOL;
    }
}
