<?php

use Discuz\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddIntegralToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->table('users', function (Blueprint $table) {
            $table->string('nickname', 100)->default('')->after('username')->comment('昵称');
            $table->unsignedInteger('integral_count')->default(0)->after('register_reason')->comment('积分数');
            $table->unsignedInteger('growup_count')->default(0)->after('register_reason')->comment('成长数');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema()->table('users', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('integral_count');
            $table->dropColumn('growup_count');
        });
    }
}
