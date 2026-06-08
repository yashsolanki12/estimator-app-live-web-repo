<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shopify_session', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 191)->unique();
            $table->string('shop');
            $table->boolean('is_online');
            $table->string('state')->nullable();
            $table->text('scope')->nullable();
            $table->text('access_token')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->string('user_first_name')->nullable();
            $table->string('user_last_name')->nullable();
            $table->string('user_email')->nullable();
            $table->boolean('user_email_verified')->nullable();
            $table->boolean('account_owner')->nullable();
            $table->string('locale')->nullable();
            $table->boolean('collaborator')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shopify_session');
    }
};