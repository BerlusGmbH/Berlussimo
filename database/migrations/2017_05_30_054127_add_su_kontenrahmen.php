<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddSuKontenrahmen extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('KONTENRAHMEN_KONTEN', function (Blueprint $table) {
            $table->boolean('SONDERUMLAGE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('KONTENRAHMEN_KONTEN', function (Blueprint $table) {
            $table->dropColumn('SONDERUMLAGE');
        });
    }
}
