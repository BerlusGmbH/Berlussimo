<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnergyCostInflationToHoaBudgetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('WEG_WPLAN', function (Blueprint $table) {
            $table->decimal('ENERGIEKOSTENANPASSUNG', 4, 1)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('WEG_WPLAN', function (Blueprint $table) {
            $table->dropColumn('ENERGIEKOSTENANPASSUNG');
        });
    }
}
