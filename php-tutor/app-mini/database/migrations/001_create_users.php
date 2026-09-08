<?php
declare(strict_types=1);

use App\Database\Manager;
use Illuminate\Database\Schema\Blueprint;

/**
 * users 表:用户基础信息。
 *
 * @return object{up: callable(Manager): void, down: callable(Manager): void}
 */
return new class {
    public function up(Manager $manager): void
    {
        $manager->schema()->create('users', static function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name', 100);
            $table->string('email', 190)->unique();
            $table->string('password', 255);
            $table->timestamps();
        });
    }

    public function down(Manager $manager): void
    {
        $manager->schema()->dropIfExists('users');
    }
};
