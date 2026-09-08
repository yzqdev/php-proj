<?php

use Discuz\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddUrlToCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->table('categories', function (Blueprint $table) {
            $table->text('url')->after('description')->comment('网址');
            $table->unsignedTinyInteger('type')->default(1)->after('sort')->comment('类型：1 导航 2 顶部 3尾部，4标签，5圈子');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema()->table('categories', function (Blueprint $table) {
            $table->dropColumn('url');
            $table->dropColumn('type');
        });
    }
}
