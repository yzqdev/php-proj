<?php

use Discuz\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddContentToGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->table('groups', function (Blueprint $table) {
            $table->text('content')->nullable()->after('name')->comment('简介');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema()->table('groups', function (Blueprint $table) {
            $table->dropColumn('content');
        });
    }
}
