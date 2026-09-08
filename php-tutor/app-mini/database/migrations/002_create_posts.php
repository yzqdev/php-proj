<?php
declare(strict_types=1);

use App\Database\Manager;
use Illuminate\Database\Schema\Blueprint;

/**
 * posts 表:多用户博客文章。
 *
 * @return object{up: callable(Manager): void, down: callable(Manager): void}
 */
return new class {
    public function up(Manager $manager): void
    {
        $manager->schema()->create('posts', static function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('title', 190);
            $table->string('slug', 210)->unique();
            $table->text('content');
            $table->string('status', 20)->default('draft')->index();
            $table->unsignedBigInteger('author_id');
            $table->timestamps();

            $table->foreign('author_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(Manager $manager): void
    {
        $manager->schema()->dropIfExists('posts');
    }
};
