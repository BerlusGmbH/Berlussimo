<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCredentialRemoveApiToken extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (
            Schema::hasTable('credentials') &&
            Schema::hasColumn('credentials', 'api_token')
        ) {
            Schema::table('credentials', function (Blueprint $table) {
                $table->dropColumn('api_token');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
