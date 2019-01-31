<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class ChangeTableEngine extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $database = Schema::getConnection()->getDatabaseName();
        DB::unprepared("ALTER DATABASE `$database` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        DB::unprepared("ALTER TABLE `BAUSTELLEN` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `BAUSTELLEN_EXT` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `BAU_BELEG` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `BELEG2RG` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `BK_ANPASSUNG` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `BK_BERECHNUNG_BUCHUNGEN` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `BK_GENERAL_KEYS` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `BK_KONTEN` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `BK_PROFILE` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `DETAIL` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `DETAIL_KATEGORIEN` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `DETAIL_UNTERKATEGORIEN` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `EINHEIT` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `FENSTER_EINGEBAUT` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `FENSTER_LIEFERUNG` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `FOOTER_ZEILE` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `GELD_KONTEN` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `GELD_KONTEN_ZUWEISUNG` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `GELD_KONTO_BUCHUNGEN` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `GEO_ENTFERNUNG` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `GEO_LON_LAT` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `GEO_TERMINE` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `HAUS` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `KASSEN` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `KASSEN_BUCH` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `KASSEN_PARTNER` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `KAUTION_DATEN` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `KAUTION_FELD` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `KONTENRAHMEN` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `KONTENRAHMEN_GRUPPEN` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `KONTENRAHMEN_KONTEN` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `KONTENRAHMEN_KONTOARTEN` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `KONTENRAHMEN_ZUWEISUNG` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `KONTIERUNG_POSITIONEN` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `KUNDEN_LOG_BER` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `LAGER` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `LAGER_PARTNER` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `LEISTUNGSKATALOG` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `LIEFERSCHEINE` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `MIETENTWICKLUNG` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `MIETER_MAHNLISTEN` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `MIETSPIEGEL` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `MIETVERTRAG` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `MS_SONDERMERKMALE` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `OBJEKT` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `OBJEKT_PARTNER` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `PARTNER_LIEFERANT` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `PARTNER_STICHWORT` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `PDF_VORLAGEN` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `PERSON_MIETVERTRAG` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `POSITIONEN_KATALOG` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `POS_GRUPPE` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `POS_POOL` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `POS_POOLS` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `PROTOKOLL` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `RECHNUNGEN` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `RECHNUNGEN_POSITIONEN` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `RECHNUNGEN_SCHLUSS` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `RECHNUNG_KUERZEL` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `SEPA_MANDATE` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `SEPA_MANDATE_SEQ` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `SEPA_UEBERWEISUNG` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `SICH_EINBEHALT` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `START_STOP` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `STUNDENZETTEL` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `STUNDENZETTEL_POS` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `TODO_LISTE` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `TRANSFER_TAB` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `UEBERWEISUNG` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `URLAUB` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `URLAUB_EINST` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `VERPACKUNGS_E` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `WARTUNGEN` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `WARTUNGSPLAN` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `WEG_EIGENTUEMER_PERSON` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `WEG_HGA_HK` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `WEG_HGA_PROFIL` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `WEG_HGA_ZEILEN` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `WEG_HG_ZAHLUNGEN` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `WEG_IHR_III` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `WEG_KONTOSTAND` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `WEG_MITEIGENTUEMER` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `WEG_WG_DEF` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `WEG_WPLAN` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `WEG_WPLAN_ZEILEN` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `WERKZEUGE` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `WIRT_EINHEITEN` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `WIRT_EIN_TAB` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `W_GERAETE` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `W_GRUPPE` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `W_TEAMS` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `W_TEAMS_BENUTZER` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `W_TEAM_PROFILE` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `W_TERMINE` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
        DB::unprepared("ALTER TABLE `ZUGRIFF_ERROR` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci ENGINE=InnoDB;");
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
