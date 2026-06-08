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
        Schema::create('timer_configuration_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_store_id')->index('timer_configuration_details_user_store_id_foreign');
            $table->boolean('status')->default(false);
            $table->string('timer_visibility', 255)->nullable();
            $table->string('timezone', 255)->nullable();
            $table->string('cutoff_hour', 255)->nullable();
            $table->string('cutoff_minutes', 255)->nullable();
            $table->string('countdown_format', 255)->nullable();
            $table->tinyInteger('enable_second')->default(1)->comment('0 = No, 1 = Yes');
            $table->tinyInteger('show_timer_past_cutoff')->default(1)->comment('0 = No, 1 = Yes');
            $table->tinyInteger('hide_comma_separator')->default(0)->comment('0 = No, 1 = Yes');
            $table->string('delivery_lead_time', 255)->nullable();
            $table->tinyInteger('enable_delivery_add_range')->default(0)->comment('0 = No, 1 = Yes');
            $table->string('delivery_range_days', 255)->nullable();
            $table->string('delivery_handling_time', 255)->nullable();
            $table->tinyInteger('enable_dispatch_days')->default(1)->comment('0 = No, 1 = Yes');
            $table->string('dispatch_days', 255)->nullable();
            $table->string('active_delivery_days', 255)->nullable();
            $table->string('delivery_date_format', 255)->nullable();
            $table->text('custom_message')->nullable();
            $table->tinyInteger('text_position')->default(0)->comment('0 = Below Add to cart, 1 = Above Add to cart');
            $table->string('text_font_size', 255)->nullable();
            $table->string('text_align', 255)->nullable();
            $table->string('text_background_color', 255)->nullable();
            $table->string('text_font_color', 255)->nullable();
            $table->string('text_countdown_color', 255)->nullable();
            $table->string('text_deliverydate_color', 255)->nullable();
            $table->string('text_border_size', 255)->nullable();
            $table->string('text_border_color', 255)->nullable();
            $table->string('text_border_radius', 255)->nullable();
            $table->string('text_border_style', 255)->nullable();
            $table->string('text_margin_top', 255)->nullable();
            $table->string('text_margin_bottom', 255)->nullable();
            $table->string('text_margin_left', 255)->nullable();
            $table->string('text_margin_right', 255)->nullable();
            $table->string('visual_icon_color', 255)->nullable();
            $table->string('visual_accent_color', 255)->nullable();
            $table->string('visual_font_color', 255)->nullable();
            $table->string('visual_text_color', 255)->nullable();
            $table->string('visual_background_color', 255)->nullable();
            $table->string('visual_margin_top', 255)->nullable();
            $table->string('visual_margin_bottom', 255)->nullable();
            $table->string('visual_margin_left', 255)->nullable();
            $table->string('visual_margin_right', 255)->nullable();
            $table->tinyInteger('enable_tbtimer')->default(1)->comment('0 = No, 1 = Yes');
            $table->tinyInteger('enable_vtimer')->default(0)->comment('0 = No, 1 = Yes');
            $table->string('hide_on_collection', 255)->nullable();
            $table->string('product_tags', 255)->nullable();
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
        Schema::dropIfExists('timer_configuration_details');
    }
};
