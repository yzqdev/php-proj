<?php

use Discuz\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddShowToCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->table('categories', function (Blueprint $table) {
            //
            $table->unsignedSmallInteger('show')->unsigned()->default(1)->comment('是否显示');
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
            //
        });
    }
}
