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
        Schema::create('translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_store_id')->index('translations_user_store_id_foreign');
            $table->string('text_days', 255)->nullable();
            $table->string('text_hours', 255)->nullable();
            $table->string('text_minutes', 255)->nullable();
            $table->string('text_seconds', 255)->nullable();
            $table->string('visual_estimated_arrival', 255)->nullable();
            $table->string('visual_order_placed', 255)->nullable();
            $table->string('visual_order_dispatches', 255)->nullable();
            $table->string('visual_delivered', 255)->nullable();
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
        Schema::dropIfExists('translations');
    }
};
