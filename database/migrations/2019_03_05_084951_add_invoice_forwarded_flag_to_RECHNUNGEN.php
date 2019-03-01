<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvoiceForwardedFlagToRECHNUNGEN extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('RECHNUNGEN', function (Blueprint $table) {
            $table->string('forwarded')->default('auto');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('RECHNUNGEN', function (Blueprint $table) {
            $table->dropColumn('forwarded');
        });
    }
}
