<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mock_records', function (Blueprint $table) {
            $table->id();
            // 资源删除时级联删除记录
            $table->foreignId('mock_resource_id')->constrained('mock_resources')->cascadeOnDelete();
            $table->json('data')->comment('生成/写入的业务数据，JSON 列');
            $table->timestamps();

            // 列表查询按资源定位，建索引避免全表扫
            $table->index('mock_resource_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mock_records');
    }
};
