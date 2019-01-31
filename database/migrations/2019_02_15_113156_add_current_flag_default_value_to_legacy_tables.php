<?php

use Illuminate\Database\Migrations\Migration;

class AddCurrentFlagDefaultValueToLegacyTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws Throwable
     */
    public function up()
    {
        $flags = [
            'BAUSTELLEN_EXT' => 'AKTUELL',
            'BK_ANPASSUNG' => 'AKTUELL',
            'BK_BERECHNUNG_BUCHUNGEN' => 'AKTUELL',
            'BK_GENERAL_KEYS' => 'AKTUELL',
            'BK_KONTEN' => 'AKTUELL',
            'BK_PROFILE' => 'AKTUELL',
            'DETAIL' => 'DETAIL_AKTUELL',
            'DETAIL_KATEGORIEN' => 'DETAIL_KAT_AKTUELL',
            'DETAIL_UNTERKATEGORIEN' => 'AKTUELL',
            'EINHEIT' => 'EINHEIT_AKTUELL',
            'FOOTER_ZEILE' => 'AKTUELL',
            'GELD_KONTEN' => 'AKTUELL',
            'GELD_KONTEN_ZUWEISUNG' => 'AKTUELL',
            'GELD_KONTO_BUCHUNGEN' => 'AKTUELL',
            'GEO_ENTFERNUNG' => 'AKTUELL',
            'GEO_LON_LAT' => 'AKTUELL',
            'GEO_TERMINE' => 'AKTUELL',
            'HAUS' => 'HAUS_AKTUELL',
            'KASSEN' => 'AKTUELL',
            'KASSEN_BUCH' => 'AKTUELL',
            'KASSEN_PARTNER' => 'AKTUELL',
            'KAUTION_DATEN' => 'AKTUELL',
            'KAUTION_FELD' => 'AKTUELL',
            'KONTENRAHMEN' => 'AKTUELL',
            'KONTENRAHMEN_GRUPPEN' => 'AKTUELL',
            'KONTENRAHMEN_KONTEN' => 'AKTUELL',
            'KONTENRAHMEN_KONTOARTEN' => 'AKTUELL',
            'KONTENRAHMEN_ZUWEISUNG' => 'AKTUELL',
            'KONTIERUNG_POSITIONEN' => 'AKTUELL',
            'KUNDEN_LOG_BER' => 'AKTUELL',
            'KUNDEN_LOGIN' => 'AKTUELL',
            'LAGER' => 'AKTUELL',
            'LAGER_PARTNER' => 'AKTUELL',
            'LEISTUNGSKATALOG' => 'AKTUELL',
            'LIEFERSCHEINE' => 'AKTUELL',
            'MIETENTWICKLUNG' => 'MIETENTWICKLUNG_AKTUELL',
            'MIETER_MAHNLISTEN' => 'AKTUELL',
            'MIETVERTRAG' => 'MIETVERTRAG_AKTUELL',
            'OBJEKT' => 'OBJEKT_AKTUELL',
            'OBJEKT_PARTNER' => 'AKTUELL',
            'PARTNER_LIEFERANT' => 'AKTUELL',
            'PARTNER_STICHWORT' => 'AKTUELL',
            'PDF_VORLAGEN' => 'AKTUELL',
            'PERSON_MIETVERTRAG' => 'PERSON_MIETVERTRAG_AKTUELL',
            'POS_GRUPPE' => 'AKTUELL',
            'POSITIONEN_KATALOG' => 'AKTUELL',
            'POS_POOL' => 'AKTUELL',
            'POS_POOLS' => 'AKTUELL',
            'RECHNUNGEN' => 'AKTUELL',
            'RECHNUNGEN_POSITIONEN' => 'AKTUELL',
            'RECHNUNGEN_SCHLUSS' => 'AKTUELL',
            'RECHNUNG_KUERZEL' => 'AKTUELL',
            'SEPA_MANDATE' => 'AKTUELL',
            'SEPA_MANDATE_SEQ' => 'AKTUELL',
            'SEPA_UEBERWEISUNG' => 'AKTUELL',
            'START_STOP' => 'AKTUELL',
            'STUNDENZETTEL' => 'AKTUELL',
            'STUNDENZETTEL_POS' => 'AKTUELL',
            'TODO_LISTE' => 'AKTUELL',
            'UEBERWEISUNG' => 'AKTUELL',
            'URLAUB' => 'AKTUELL',
            'VERPACKUNGS_E' => 'AKTUELL',
            'WARTUNGEN' => 'AKTUELL',
            'WARTUNGSPLAN' => 'AKTUELL',
            'WEG_EIGENTUEMER_PERSON' => 'AKTUELL',
            'WEG_HGA_HK' => 'AKTUELL',
            'WEG_HGA_PROFIL' => 'AKTUELL',
            'WEG_HGA_ZEILEN' => 'AKTUELL',
            'WEG_HG_ZAHLUNGEN' => 'AKTUELL',
            'WEG_IHR_III' => 'AKTUELL',
            'WEG_KONTOSTAND' => 'AKTUELL',
            'WEG_MITEIGENTUEMER' => 'AKTUELL',
            'WEG_WG_DEF' => 'AKTUELL',
            'WEG_WPLAN' => 'AKTUELL',
            'WEG_WPLAN_ZEILEN' => 'AKTUELL',
            'WERKZEUGE' => 'AKTUELL',
            'W_GERAETE' => 'AKTUELL',
            'W_GRUPPE' => 'AKTUELL',
            'WIRT_EINHEITEN' => 'AKTUELL',
            'WIRT_EIN_TAB' => 'AKTUELL',
            'W_TEAM_PROFILE' => 'AKTUELL',
            'W_TEAMS' => 'AKTUELL',
            'W_TEAMS_BENUTZER' => 'AKTUELL',
            'W_TERMINE' => 'AKTUELL',
        ];
        DB::transaction(function () use ($flags) {
            foreach ($flags as $table => $field) {
                DB::unprepared("ALTER TABLE `$table` ALTER `$field` SET DEFAULT '1';");
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
