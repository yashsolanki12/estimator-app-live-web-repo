<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('timer_dispatch_disable_dates', function (Blueprint $table) {
            $table->foreign(['user_store_id'])->references(['id'])->on('users')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('timer_dispatch_disable_dates', function (Blueprint $table) {
            $table->dropForeign('timer_dispatch_disable_dates_user_store_id_foreign');
        });
    }
};
