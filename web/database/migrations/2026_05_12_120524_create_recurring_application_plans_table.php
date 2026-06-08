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
        Schema::create('recurring_application_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_store_id')->index('recurring_application_plans_user_store_id_foreign');
            $table->string('charge_id', 255)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('api_client_id', 255)->nullable();
            $table->decimal('price')->nullable();
            $table->integer('limit')->nullable();
            $table->decimal('capped_amount')->nullable();
            $table->string('status', 50)->nullable();
            $table->tinyInteger('test')->nullable();
            $table->integer('trial_days')->nullable();
            $table->text('return_url')->nullable();
            $table->text('decorated_return_url')->nullable();
            $table->text('confirmation_url')->nullable();
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
        Schema::dropIfExists('recurring_application_plans');
    }
};
