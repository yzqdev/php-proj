<?php

use Discuz\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddIsPinglunToThreadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->table('threads', function (Blueprint $table) {
            $table->tinyInteger('is_pinglun')->unsigned()->default(1)->after('is_approved')->comment('是否设置关闭评论');
            //
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema()->table('threads', function (Blueprint $table) {
           $table->dropColumn('is_pinglun');
        });
    }
}
