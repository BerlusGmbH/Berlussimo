<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class MakeDETAILBEMERKUNGNullableInDETAIL extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('DETAIL')) {
            DB::statement('ALTER TABLE `DETAIL` CHANGE COLUMN `DETAIL_BEMERKUNG` `DETAIL_BEMERKUNG` VARCHAR(400) NULL;');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('DETAIL')) {
            DB::statement('ALTER TABLE `DETAIL` CHANGE COLUMN `DETAIL_BEMERKUNG` `DETAIL_BEMERKUNG` VARCHAR(400) NOT NULL;');
        }
    }
}
