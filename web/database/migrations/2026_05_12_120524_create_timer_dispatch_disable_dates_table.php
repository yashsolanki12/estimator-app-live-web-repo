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
        Schema::create('timer_dispatch_disable_dates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_store_id')->index('timer_dispatch_disable_dates_user_store_id_foreign');
            $table->date('date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('timer_dispatch_disable_dates');
    }
};
