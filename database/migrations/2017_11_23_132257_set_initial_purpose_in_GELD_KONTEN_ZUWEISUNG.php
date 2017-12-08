<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class SetInitialPurposeInGELDKONTENZUWEISUNG extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = DB::table('GELD_KONTEN_ZUWEISUNG');
        if ($table->exists()) {
            $table->where('KOSTENTRAEGER_TYP', 'Benutzer')
                ->update(['VERWENDUNGSZWECK' => 'Lohnzahlung']);
            DB::table('GELD_KONTEN_ZUWEISUNG')
                ->where('KOSTENTRAEGER_TYP', 'Eigentuemer')
                ->update(['VERWENDUNGSZWECK' => 'Eigentümerentnahme']);
            DB::table('GELD_KONTEN_ZUWEISUNG')
                ->where('KOSTENTRAEGER_TYP', 'Objekt')
                ->update(['VERWENDUNGSZWECK' => 'Hausgeld']);
            DB::table('GELD_KONTEN_ZUWEISUNG')
                ->where('KOSTENTRAEGER_TYP', 'Partner')
                ->update(['VERWENDUNGSZWECK' => 'Kreditor']);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('GELD_KONTEN_ZUWEISUNG', function (Blueprint $table) {
            //
        });
    }
}
