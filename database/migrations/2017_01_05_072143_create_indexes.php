<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('WEG_MITEIGENTUEMER', function ($table) {
            $table->index('ID');
            $table->index('EINHEIT_ID');
        });
        Schema::table('WEG_EIGENTUEMER_PERSON', function ($table) {
            $table->index('ID');
            $table->index('WEG_EIG_ID');
            $table->index('PERSON_ID');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('WEG_MITEIGENTUEMER', function ($table) {
            $table->dropIndex('weg_miteigentuemer_id_index');
            $table->dropIndex('weg_miteigentuemer_einheit_id_index');
        });
        Schema::table('WEG_EIGENTUEMER_PERSON', function ($table) {
            $table->dropIndex('weg_eigentuemer_person_id_index');
            $table->dropIndex('weg_eigentuemer_person_weg_eig_id_index');
            $table->dropIndex('weg_eigentuemer_person_person_id_index');
        });
    }
}
