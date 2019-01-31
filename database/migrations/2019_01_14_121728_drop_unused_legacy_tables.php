<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DropUnusedLegacyTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('BERICHTE_USER');
        Schema::dropIfExists('BK_ABRECHNUNGEN');
        Schema::dropIfExists('BK_ABRECHNUNGEN_KONTEN');
        Schema::dropIfExists('BK_EINZEL_ABRECHNUNGEN');
        Schema::dropIfExists('BK_EINZEL_ABR_ZEILEN');
        Schema::dropIfExists('BLZ');
        Schema::dropIfExists('FEIERTAGE');
        Schema::dropIfExists('KUNDEN_LOGFILE');
        Schema::dropIfExists('LEERSTAND_INTERESSENT');
        Schema::dropIfExists('LIEFERSCHEINE_OK');
        Schema::dropIfExists('LV');
        Schema::dropIfExists('LV_GLIEDERUNG');
        Schema::dropIfExists('LV_K_ARTIKEL');
        Schema::dropIfExists('LV_K_POSITIONEN');
        Schema::dropIfExists('LV_POSITIONEN');
        Schema::dropIfExists('LV_PROJEKTE');
        Schema::dropIfExists('MOB_ZE_PROTOKOLL');
        Schema::dropIfExists('MONATSABSCHLUSS');
        Schema::dropIfExists('PHP_ABFRAGEN');
        Schema::dropIfExists('SEPA_KONTOS');
        Schema::dropIfExists('SEPA_MANDATE_1_2_14');
        Schema::dropIfExists('SEPA_MANDATE_SEQ_03_frst');
        Schema::dropIfExists('URLAUB_BELOW');
        Schema::dropIfExists('WARTUNG_ZUWEISUNG');
        Schema::dropIfExists('WEG_WPLAN_ZEILEN1_ALT');

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
