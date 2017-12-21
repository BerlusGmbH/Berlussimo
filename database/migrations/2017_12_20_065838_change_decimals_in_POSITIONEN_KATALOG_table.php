<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDecimalsInPOSITIONENKATALOGTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws \Doctrine\DBAL\DBALException
     */
    public function up()
    {
        if (Schema::hasTable('POSITIONEN_KATALOG')) {
            Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->getDatabasePlatform()
                ->registerDoctrineTypeMapping('enum', 'string');

            Schema::table('POSITIONEN_KATALOG', function (Blueprint $table) {
                $table->decimal('LISTENPREIS', 14, 4)->nullable(false)->default(0)->change();
                $table->decimal('RABATT_SATZ', 5, 2)->nullable(false)->default(0)->change();
                $table->decimal('SKONTO', 5, 2)->nullable(false)->default(0)->change();
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
    }
}
