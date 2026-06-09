<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterSessionTokenColumnTypeInShopifySessionTable extends Migration
{
    public function up()
    {
        $missing = '2019_12_14_000001_create_personal_access_tokens_table';
        $exists = DB::table('migrations')->where('migration', $missing)->exists();
        if (!$exists) {
            DB::table('migrations')->insert([
                'migration' => $missing,
                'batch' => DB::table('migrations')->max('batch') + 1,
            ]);
        }

        Schema::table('shopify_session', function (Blueprint $table) {
            $table->text('session_token')->nullable()->after('access_token')->change();
        });
    }

    public function down()
    {
        Schema::table('shopify_session', function (Blueprint $table) {
            $table->string('session_token', 191)->nullable()->after('access_token')->change();
        });
    }
}


