<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddTimespanAndPurposeToGELDKONTENZUWEISUNG extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('GELD_KONTEN_ZUWEISUNG', function (Blueprint $table) {
            $table->date('VON')->default('2006-01-01');
            $table->date('BIS')->nullable();
            $table->string('VERWENDUNGSZWECK', 30)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('GELD_KONTEN_ZUWEISUNG', function (Blueprint $table) {
            $table->dropColumn('VON');
            $table->dropColumn('BIS');
            $table->dropColumn('VERWENDUNGSZWECK');
        });
    }
}
