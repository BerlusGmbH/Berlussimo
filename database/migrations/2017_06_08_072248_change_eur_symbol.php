<?php

use Illuminate\Database\Migrations\Migration;

class ChangeEurSymbol extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::update('UPDATE BK_GENERAL_KEYS SET ME=\'EUR\' WHERE GKEY_ID=4');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::update('UPDATE BK_GENERAL_KEYS SET ME=\'€\' WHERE GKEY_ID=4');
    }
}
