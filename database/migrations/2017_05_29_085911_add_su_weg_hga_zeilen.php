<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddSuWegHgaZeilen extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('WEG_HGA_ZEILEN', function (Blueprint $table) {
            $table->boolean('SU_AUSZAHLEN');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('WEG_HGA_ZEILEN', function (Blueprint $table) {
            $table->dropColumn('SU_AUSZAHLEN');
        });
    }
}
