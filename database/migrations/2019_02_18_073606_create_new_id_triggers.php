<?php

use Illuminate\Database\Migrations\Migration;

class CreateNewIdTriggers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws Throwable
     */
    public function up()
    {
        $tables = [
            'BAUSTELLEN_EXT' => 'ID',
            'BK_ANPASSUNG' => 'AN_ID',
            'BK_BERECHNUNG_BUCHUNGEN' => 'BK_BE_ID',
            'BK_GENERAL_KEYS' => 'GKEY_ID',
            'BK_KONTEN' => 'BK_K_ID',
            'BK_PROFILE' => 'BK_ID',
            'DETAIL' => 'DETAIL_ID',
            'DETAIL_KATEGORIEN' => 'DETAIL_KAT_ID',
            'EINHEIT' => 'EINHEIT_ID',
            'FOOTER_ZEILE' => 'FOOTER_ID',
            'GELD_KONTEN' => 'KONTO_ID',
            'GELD_KONTEN_ZUWEISUNG' => 'ZUWEISUNG_ID',
            'GELD_KONTO_BUCHUNGEN' => 'GELD_KONTO_BUCHUNGEN_ID',
            'HAUS' => 'HAUS_ID',
            'KASSEN' => 'KASSEN_ID',
            'KASSEN_BUCH' => 'KASSEN_BUCH_ID',
            'KONTENRAHMEN' => 'KONTENRAHMEN_ID',
            'KONTENRAHMEN_GRUPPEN' => 'KONTENRAHMEN_GRUPPEN_ID',
            'KONTENRAHMEN_KONTEN' => 'KONTENRAHMEN_KONTEN_ID',
            'KONTENRAHMEN_KONTOARTEN' => 'KONTENRAHMEN_KONTOART_ID',
            'KONTENRAHMEN_ZUWEISUNG' => 'ID',
            'KONTIERUNG_POSITIONEN' => 'KONTIERUNG_ID',
            'KUNDEN_LOG_BER' => 'ID',
            'KUNDEN_LOGIN' => 'ID',
            'LAGER' => 'LAGER_ID',
            'LEISTUNGSKATALOG' => 'LK_ID',
            'LIEFERSCHEINE' => 'L_ID',
            'MIETENTWICKLUNG' => 'MIETENTWICKLUNG_ID',
            'MIETVERTRAG' => 'MIETVERTRAG_ID',
            'OBJEKT' => 'OBJEKT_ID',
            'PARTNER_LIEFERANT' => 'PARTNER_ID',
            'PARTNER_STICHWORT' => 'ID',
            'PERSON_MIETVERTRAG' => 'PERSON_MIETVERTRAG_ID',
            'POS_GRUPPE' => 'B_ID',
            'POSITIONEN_KATALOG' => 'KATALOG_ID',
            'POS_POOLS' => 'ID',
            'RECHNUNGEN' => 'BELEG_NR',
            'RECHNUNGEN_POSITIONEN' => 'RECHNUNGEN_POS_ID',
            'RECHNUNGEN_SCHLUSS' => 'ID',
            'SEPA_MANDATE' => 'M_ID',
            'SEPA_UEBERWEISUNG' => 'ID',
            'STUNDENZETTEL' => 'ZETTEL_ID',
            'STUNDENZETTEL_POS' => 'ST_ID',
            'TODO_LISTE' => 'T_ID',
            'WARTUNGSPLAN' => 'PLAN_ID',
            'WEG_EIGENTUEMER_PERSON' => 'ID',
            'WEG_HGA_HK' => 'ID',
            'WEG_HGA_PROFIL' => 'ID',
            'WEG_HGA_ZEILEN' => 'ID',
            'WEG_HG_ZAHLUNGEN' => 'ID',
            'WEG_IHR_III' => 'ID',
            'WEG_KONTOSTAND' => 'ID',
            'WEG_MITEIGENTUEMER' => 'ID',
            'WEG_WG_DEF' => 'ID',
            'WEG_WPLAN' => 'PLAN_ID',
            'WEG_WPLAN_ZEILEN' => 'ID',
            'WERKZEUGE' => 'ID',
            'W_GERAETE' => 'GERAETE_ID',
            'W_GRUPPE' => 'GRUPPE_ID',
            'WIRT_EINHEITEN' => 'W_ID',
            'WIRT_EIN_TAB' => 'WZ_ID',
            'W_TEAM_PROFILE' => 'ID',
            'W_TEAMS' => 'TEAM_ID',
            'W_TEAMS_BENUTZER' => 'ID',
            'W_TERMINE' => 'PLAN_ID',
        ];
        DB::transaction(function () use ($tables) {
            foreach ($tables as $table => $field) {
                DB::unprepared("
CREATE TRIGGER new_id_on_$table 
BEFORE INSERT
   ON $table FOR EACH ROW

BEGIN
	
	IF NEW.$field = 0 || NEW.$field IS NULL THEN
		SET NEW.$field = (SELECT COALESCE(MAX($field), 0) + 1 FROM $table);
	END IF;

END
");
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
