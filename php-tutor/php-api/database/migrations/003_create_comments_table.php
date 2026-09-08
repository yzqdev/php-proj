<?php
declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;

// Idempotent: checks table/column existence before creating.
// Receives the Schema Builder instance from the runner — no Facades.

return [
    'up' => function (Illuminate\Database\Schema\Builder $schema) {
        if ($schema->hasTable('comments')) {
            return;
        }

        $schema->create('comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('article_id')->constrained('articles')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('content');
            $table->timestamps();
        });

        $schema->table('comments', function (Blueprint $table) {
            $table->index(['article_id', 'created_at']);
        });
    },

    'down' => function (Illuminate\Database\Schema\Builder $schema) {
        $schema->disableForeignKeyConstraints();
        $schema->dropIfExists('comments');
        $schema->enableForeignKeyConstraints();
    },
];