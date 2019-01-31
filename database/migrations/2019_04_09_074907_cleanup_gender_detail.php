<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanupGenderDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('DETAIL')) {
            DB::table('DETAIL')
                ->where('DETAIL_NAME', 'Geschlecht')
                ->where('DETAIL_INHALT', 'like', "%\r\n%")
                ->update([
                    'DETAIL_INHALT' => DB::raw("TRIM('\r\n' FROM DETAIL_INHALT)")
                ]);
        }
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
