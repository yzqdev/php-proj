<?php
declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;

// Idempotent: checks table/column existence before creating.
// Receives the Schema Builder instance from the runner — no Facades.

return [
    'up' => function (Illuminate\Database\Schema\Builder $schema) {
        if ($schema->hasTable('users')) {
            return;
        }

        $schema->create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username', 50)->unique();
            $table->string('email', 100)->unique();
            $table->string('password', 255);
            $table->rememberToken();
            $table->timestamps();
        });
    },

    'down' => function (Illuminate\Database\Schema\Builder $schema) {
        $schema->disableForeignKeyConstraints();
        $schema->dropIfExists('users');
        $schema->enableForeignKeyConstraints();
    },
];