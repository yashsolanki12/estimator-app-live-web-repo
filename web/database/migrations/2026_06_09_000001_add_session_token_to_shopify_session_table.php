<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('shopify_session', function (Blueprint $table) {
            $table->string('session_token', 191)->nullable()->after('access_token');
        });
    }

    public function down()
    {
        Schema::table('shopify_session', function (Blueprint $table) {
            $table->dropColumn('session_token');
        });
    }
};
