<?php

use Discuz\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddTypeToPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->table('posts', function (Blueprint $table) {
            $table->tinyInteger('type')->unsigned()->default(0)->after('ip')->comment('类型：0普通 1长文 2视频 3图片，10圈子，11圈贴，12课题，13课贴');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema()->table('posts', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
