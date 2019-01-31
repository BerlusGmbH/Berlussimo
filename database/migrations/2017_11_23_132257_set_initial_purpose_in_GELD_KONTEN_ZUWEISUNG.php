<?php

use Illuminate\Database\Migrations\Migration;

class SetInitialPurposeInGELDKONTENZUWEISUNG extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('GELD_KONTEN_ZUWEISUNG')) {
            DB::table('GELD_KONTEN_ZUWEISUNG')->where('KOSTENTRAEGER_TYP', 'Benutzer')
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
    }
}
