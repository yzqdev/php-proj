<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mock_resources', function (Blueprint $table) {
            $table->id();
            // 项目删除时级联删除其下所有资源
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name')->comment('资源名，如 users/orders');
            $table->json('fields')->comment('字段 Schema JSON，如 [{"name":"email","type":"email"}]');
            $table->unsignedInteger('total')->default(0)->comment('当前已生成的记录数');
            $table->timestamps();

            // 同一项目下资源名唯一
            $table->unique(['project_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mock_resources');
    }
};
