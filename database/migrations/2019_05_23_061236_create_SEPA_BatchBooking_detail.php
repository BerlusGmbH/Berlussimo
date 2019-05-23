<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateSEPABatchBookingDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('DETAIL_KATEGORIEN')) {
            DB::table('DETAIL_KATEGORIEN')->insert([
                'DETAIL_KAT_NAME' => 'SEPA-Einzeltransaktionen',
                'DETAIL_KAT_KATEGORIE' => 'GELD_KONTEN',
                'DETAIL_KAT_AKTUELL' => '1'
            ]);

            if (Schema::hasTable('DETAIL_UNTERKATEGORIEN')) {
                $category = DB::table('DETAIL_KATEGORIEN')->where('DETAIL_KAT_NAME', 'SEPA-Einzeltransaktionen')
                    ->where('DETAIL_KAT_KATEGORIE', 'GELD_KONTEN')
                    ->where('DETAIL_KAT_AKTUELL', '1')->first();
                if (isset($category)) {
                    DB::table('DETAIL_UNTERKATEGORIEN')->insert([
                        'KATEGORIE_ID' => $category['DETAIL_KAT_ID'],
                        'UNTERKATEGORIE_NAME' => 'ja',
                        'AKTUELL' => '1'
                    ]);
                    DB::table('DETAIL_UNTERKATEGORIEN')->insert([
                        'KATEGORIE_ID' => $category['DETAIL_KAT_ID'],
                        'UNTERKATEGORIE_NAME' => 'nein',
                        'AKTUELL' => '1'
                    ]);
                }
            }
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
