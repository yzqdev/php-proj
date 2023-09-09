<?php

use Discuz\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddUrlToGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->table('groups', function (Blueprint $table) {
            $table->text('url')->nullable()->after('name')->comment('网址');
            $table->unsignedDecimal('discount', 10, 2)->default(0.00)->after('name')->comment('优惠价格');
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
        $this->schema()->table('groups', function (Blueprint $table) {
            //
            $table->dropColumn('url');
            $table->dropColumn('discount');
        });
    }
}
