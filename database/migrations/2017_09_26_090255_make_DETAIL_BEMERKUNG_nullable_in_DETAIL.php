<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeDETAILBEMERKUNGNullableInDETAIL extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws \Doctrine\DBAL\DBALException
     */
    public function up()
    {
        if (Schema::hasTable('DETAIL')) {
            Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->getDatabasePlatform()
                ->registerDoctrineTypeMapping('enum', 'string');

            Schema::table('DETAIL', function (Blueprint $table) {
                $table->string('DETAIL_BEMERKUNG', 400)->nullable()->default(null)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     * @throws \Doctrine\DBAL\DBALException
     */
    public function down()
    {
        if (Schema::hasTable('DETAIL')) {
            Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->getDatabasePlatform()
                ->registerDoctrineTypeMapping('enum', 'string');

            Schema::table('DETAIL', function (Blueprint $table) {
                $table->string('DETAIL_BEMERKUNG', 400)->nullable(false)->default('')->change();
            });
        }
    }
}
