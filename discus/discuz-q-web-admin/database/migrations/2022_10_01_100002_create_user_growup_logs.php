<?php

/**
 * Copyright (C) 2020 Tencent Cloud.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use Discuz\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserGrowupLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->create('user_growup_logs', function (Blueprint $table) {
            $table->id()->comment('成长值明细 id');
            $table->unsignedBigInteger('user_id')->comment('成长值明细所属用户 id');
            $table->string('change_value', 255)->default('')->comment('成长值');
            $table->unsignedSmallInteger('change_type')->default(0)->comment('10：登录成长值，');
            $table->string('change_desc', 255)->default('')->comment('变动描述');
            $table->unsignedBigInteger('order_id')->nullable()->comment('关联订单记录ID');
            $table->unsignedBigInteger('user_wallet_cash_id')->nullable()->comment('关联提现记录ID');
            $table->dateTime('created_at')->comment('创建时间');
            $table->dateTime('updated_at')->comment('更新时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema()->dropIfExists('user_growup_logs');
    }
}
