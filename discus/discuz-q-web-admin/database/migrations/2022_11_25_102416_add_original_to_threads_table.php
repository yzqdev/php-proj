<?php

use Discuz\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddOriginalToThreadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->table('threads', function (Blueprint $table) {
            //
            $table->tinyInteger('is_original')->unsigned()->default(0)->after('is_approved')->comment('是否设置原创');
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
            $table->dropColumn('is_original');
            //
        });
    }
}
