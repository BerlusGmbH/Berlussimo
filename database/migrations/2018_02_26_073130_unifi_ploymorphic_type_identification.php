<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class UnifiPloymorphicTypeIdentification extends Migration
{

    private $tables = [
        'BAUSTELLEN' => ['KOSTENTRAEGER_TYP'],
        'BK_BERECHNUNG_BUCHUNGEN' => ['KOSTENTRAEGER_TYP'],
        'BK_PROFILE' => ['TYP'],
        'DETAIL' => ['DETAIL_ZUORDNUNG_TABELLE'],
        'DETAIL_KATEGORIEN' => ['DETAIL_KAT_KATEGORIE'],
        'FOOTER_ZEILE' => ['FOOTER_TYP'],
        'GELD_KONTEN_ZUWEISUNG' => ['KOSTENTRAEGER_TYP'],
        'GELD_KONTO_BUCHUNGEN' => ['KOSTENTRAEGER_TYP'],
        'KASSEN_BUCH' => ['KOSTENTRAEGER_TYP'],
        'KONTENRAHMEN_ZUWEISUNG' => ['TYP'],
        'KONTIERUNG_POSITIONEN' => ['KOSTENTRAEGER_TYP'],
        'MIETENTWICKLUNG' => ['KOSTENTRAEGER_TYP'],
        'MOB_ZE_PROTOKOLL' => ['KOS_TYP'],
        'RECHNUNGEN' => ['AUSSTELLER_TYP', 'EMPFAENGER_TYP'],
        'RECHNUNG_KUERZEL' => ['AUSSTELLER_TYP'],
        'SEPA_KONTOS' => ['KOS_TYP'],
        'SEPA_MANDATE' => ['M_KOS_TYP'],
        'SEPA_UEBERWEISUNG' => ['KOS_TYP'],
        'STUNDENZETTEL_POS' => ['KOSTENTRAEGER_TYP'],
        'TODO_LISTE' => ['BENUTZER_TYP', 'KOS_TYP'],
        'UEBERWEISUNG' => ['BEZUGSTAB'],
        'WEG_HGA_HK' => ['KOS_TYP'],
        'WEG_HGA_ZEILEN' => ['KOS_TYP'],
        'WEG_HG_ZAHLUNGEN' => ['KOS_TYP'],
        'WEG_WG_DEF' => ['KOS_TYP'],
        'W_GERAETE' => ['KOSTENTRAEGER_TYP']
    ];

    /**
     * Run the migrations.
     *
     * @return void
     * @throws Exception
     * @throws Throwable
     */
    public function up()
    {
        DB::transaction(function () {
            foreach ($this->tables as $table => $colons) {
                if (Schema::hasTable($table)) {
                    foreach ($colons as $colon) {
                        DB::table($table)->where($colon, 'PERSON')->update([$colon => 'Person']);
                        DB::table($table)->where($colon, 'BENUTZER')->update([$colon => 'Benutzer']);
                        DB::table($table)->where($colon, 'PARTNER')->update([$colon => 'Partner']);
                        DB::table($table)->where($colon, 'PARTNER_LIEFERANT')->update([$colon => 'Partner']);
                        DB::table($table)->where($colon, 'EINHEIT')->update([$colon => 'Einheit']);
                        DB::table($table)->where($colon, 'HAUS')->update([$colon => 'Haus']);
                        DB::table($table)->where($colon, 'OBJEKT')->update([$colon => 'Objekt']);
                        DB::table($table)->where($colon, 'MIETVERTRAG')->update([$colon => 'Mietvertrag']);
                        DB::table($table)->where($colon, 'WIRT_EINHEITEN')->update([$colon => 'Wirtschaftseinheit']);
                    }
                }
            }
        });
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
