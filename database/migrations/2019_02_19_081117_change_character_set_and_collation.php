<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Schema;

class ChangeCharacterSetAndCollation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws Throwable
     */
    public function up()
    {
        $potentialTables = [
            'BAUSTELLEN',
            'BAUSTELLEN_EXT',
            'BAU_BELEG',
            'BELEG2RG',
            'BENUTZER_PARTNER',
            'BK_ANPASSUNG',
            'BK_BERECHNUNG_BUCHUNGEN',
            'BK_GENERAL_KEYS',
            'BK_KONTEN',
            'BK_PROFILE',
            'credentials',
            'DETAIL',
            'DETAIL_KATEGORIEN',
            'DETAIL_UNTERKATEGORIEN',
            'EINHEIT',
            'FENSTER_EINGEBAUT',
            'FENSTER_LIEFERUNG',
            'FOOTER_ZEILE',
            'GELD_KONTEN',
            'GELD_KONTEN_ZUWEISUNG',
            'GELD_KONTO_BUCHUNGEN',
            'GEO_ENTFERNUNG',
            'GEO_LON_LAT',
            'GEO_TERMINE',
            'HAUS',
            'jobs',
            'job_titles',
            'KASSEN',
            'KASSEN_BUCH',
            'KASSEN_PARTNER',
            'KAUTION_DATEN',
            'KAUTION_FELD',
            'KONTENRAHMEN',
            'KONTENRAHMEN_GRUPPEN',
            'KONTENRAHMEN_KONTEN',
            'KONTENRAHMEN_KONTOARTEN',
            'KONTENRAHMEN_ZUWEISUNG',
            'KONTIERUNG_POSITIONEN',
            'KUNDEN_LOGIN',
            'KUNDEN_LOG_BER',
            'LAGER',
            'LAGER_PARTNER',
            'LEISTUNGSKATALOG',
            'LIEFERSCHEINE',
            'MIETENTWICKLUNG',
            'MIETER_MAHNLISTEN',
            'MIETSPIEGEL',
            'MIETVERTRAG',
            'MS_SONDERMERKMALE',
            'notifications',
            'OBJEKT',
            'OBJEKT_PARTNER',
            'PARTNER_LIEFERANT',
            'PARTNER_STICHWORT',
            'PDF_VORLAGEN',
            'permissions',
            'persons',
            'person_has_permissions',
            'person_has_roles',
            'PERSON_MIETVERTRAG',
            'POSITIONEN_KATALOG',
            'POS_GRUPPE',
            'POS_POOL',
            'POS_POOLS',
            'PROTOKOLL',
            'RECHNUNGEN',
            'RECHNUNGEN_POSITIONEN',
            'RECHNUNGEN_SCHLUSS',
            'RECHNUNG_KUERZEL',
            'roles',
            'role_has_permissions',
            'SEPA_MANDATE',
            'SEPA_MANDATE_SEQ',
            'SEPA_UEBERWEISUNG',
            'SICH_EINBEHALT',
            'START_STOP',
            'STUNDENZETTEL',
            'STUNDENZETTEL_POS',
            'TODO_LISTE',
            'TRANSFER_TAB',
            'UEBERWEISUNG',
            'URLAUB',
            'URLAUB_EINST',
            'VERPACKUNGS_E',
            'WARTUNGEN',
            'WARTUNGSPLAN',
            'WEG_EIGENTUEMER_PERSON',
            'WEG_HGA_HK',
            'WEG_HGA_PROFIL',
            'WEG_HGA_ZEILEN',
            'WEG_HG_ZAHLUNGEN',
            'WEG_IHR_III',
            'WEG_KONTOSTAND',
            'WEG_MITEIGENTUEMER',
            'WEG_WG_DEF',
            'WEG_WPLAN',
            'WEG_WPLAN_ZEILEN',
            'WERKZEUGE',
            'WIRT_EINHEITEN',
            'WIRT_EIN_TAB',
            'W_GERAETE',
            'W_GRUPPE',
            'W_TEAMS',
            'W_TEAMS_BENUTZER',
            'W_TEAM_PROFILE',
            'W_TERMINE',
            'ZUGRIFF_ERROR'
        ];

        DB::transaction(function () use ($potentialTables) {
            $database = Schema::getConnection()->getDatabaseName();
            DB::unprepared("ALTER DATABASE `$database` CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
            $tables = DB::table('information_schema.COLUMNS')
                ->where('TABLE_SCHEMA', $database)
                ->whereIn('TABLE_NAME', $potentialTables)
                ->where(function (Builder $query) {
                    $query->where(function (Builder $query) {
                        $query->where('CHARACTER_SET_NAME', '!=', 'utf8mb4')
                            ->whereNotNull('CHARACTER_SET_NAME');
                    })->orWhere(function (Builder $query) {
                        $query->where('COLLATION_NAME', '!=', 'utf8mb4_unicode_ci')
                            ->whereNotNull('COLLATION_NAME');
                    });
                })->distinct()
                ->get(['TABLE_NAME'])
                ->pluck('TABLE_NAME');
            if ($tables->contains('EINHEIT')) {
                DB::unprepared("
ALTER TABLE `EINHEIT` CHANGE `TYP` `TYP`
ENUM('Wohnraum', 'Gewerbe', 'Stellplatz', 'Garage', 'Keller', 'Freiflaeche', 'Wohneigentum', 'Werbeflaeche', 'Kinderwagenbox', 'Zimmer (möbliert)') CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci NOT NULL;
");
            }
            foreach ($tables as $table) {
                DB::unprepared("ALTER TABLE `$table` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
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
        //
    }
}
