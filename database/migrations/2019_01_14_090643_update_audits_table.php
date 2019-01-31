<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateAuditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('audits', 'user_type')) {
            Schema::table('audits', function (Blueprint $table) {
                $table->string('user_type')->nullable();
            });
            DB::table('audits')->update([
                'user_type' => \App\Models\Person::class,
                'created_at' => DB::raw('created_at'),
                'updated_at' => DB::raw('updated_at'),
            ]);
        }

        if (!Schema::hasColumn('audits', 'tags')) {
            Schema::table('audits', function (Blueprint $table) {
                $table->string('tags')->nullable();
            });
        }

        if (!Schema::hasColumn('audits', 'auditable_type') && !Schema::hasColumn('audits', 'auditable_id')) {
            Schema::table('audits', function (Blueprint $table) {
                $table->morphs('auditable');
            });
        }

        if (Schema::hasColumn('audits', 'person_id') && !Schema::hasColumn('audits', 'user_id')) {
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
        // There's no turning back
    }
}
