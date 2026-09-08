<?php
declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;

// Idempotent: checks table/column existence before creating.
// Receives the Schema Builder instance from the runner — no Facades.

return [
    'up' => function (Illuminate\Database\Schema\Builder $schema) {
        if ($schema->hasTable('articles')) {
            return;
        }

        $schema->create('articles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title', 200);
            $table->text('body');
            $table->timestamps();
        });

        // Indexes for query performance
        $schema->table('articles', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
        });
    },

    'down' => function (Illuminate\Database\Schema\Builder $schema) {
        $schema->disableForeignKeyConstraints();
        $schema->dropIfExists('articles');
        $schema->enableForeignKeyConstraints();
    },
];