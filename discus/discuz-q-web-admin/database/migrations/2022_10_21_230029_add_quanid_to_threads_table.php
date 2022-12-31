<?php

use Discuz\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddQuanidToThreadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->table('threads', function (Blueprint $table) {
            $table->integer('quanid')->unsigned()->nullable()->index()->after('title')->comment('关联圈子 id');
            $table->integer('keid')->unsigned()->nullable()->index()->after('title')->comment('关联课程 id');
            $table->tinyInteger('categorytype')->unsigned()->default(0)->after('title')->comment('分类：0普通');
            $table->tinyInteger('is_poster')->unsigned()->default(0)->after('is_approved')->comment('是否设置为海报');
            $table->tinyInteger('is_reply')->unsigned()->default(1)->after('is_approved')->comment('是否可回复');
            $table->tinyInteger('is_solve')->unsigned()->default(1)->after('is_approved')->comment('是否已解答');
            $table->tinyInteger('is_plan')->unsigned()->default(1)->after('is_approved')->comment('计划状态');
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
            $table->dropColumn('quanid');
            $table->dropColumn('keid');
            $table->dropColumn('categorytype');
            $table->dropColumn('is_poster');
            $table->dropColumn('is_reply');
            $table->dropColumn('is_solve');
            $table->dropColumn('is_plan');
        });
    }
}
