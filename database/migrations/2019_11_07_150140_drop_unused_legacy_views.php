<?php

use Illuminate\Database\Migrations\Migration;

class DropUnusedLegacyViews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws Throwable
     */
    public function up()
    {
        DB::transaction(function () {
            DB::statement("DROP VIEW IF EXISTS `Thermen in 6 Monaten`;");
            DB::statement("DROP VIEW IF EXISTS `Thermen in 9 Monaten`;");
            DB::statement("DROP VIEW IF EXISTS `UNI_ARTIKELL_PREIS`;");
            DB::statement("DROP VIEW IF EXISTS `W_GERAETE doppelt`;");
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
