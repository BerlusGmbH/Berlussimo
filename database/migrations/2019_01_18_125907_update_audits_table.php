<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAuditsTableForV8 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('audits', 'person_id')) {
            Schema::table('audits', function (Blueprint $table) {
                $table->renameColumn('person_id', 'user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('audits', 'user_id')) {
            Schema::table('audits', function (Blueprint $table) {
                $table->renameColumn('user_id', 'person_id');
            });
        }
    }
}
