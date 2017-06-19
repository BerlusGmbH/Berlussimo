<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class RemoveUnusedTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('menu_nodes');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('PERSON');
        Schema::dropIfExists('BENUTZER');
        Schema::dropIfExists('BENUTZER_MODULE');
        Schema::dropIfExists('BENUTZER_PARTNER');
        Schema::dropIfExists('BERICHTE_USER');
        Schema::dropIfExists('GEWERKE');
        Schema::dropIfExists('KONTENRAHMEN_IMPORT');
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
