<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeLEISTUNGSKATALOGTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws \Doctrine\DBAL\DBALException
     */
    public function up()
    {
        if (Schema::hasTable('LEISTUNGSKATALOG')) {
            Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->getDatabasePlatform()
                ->registerDoctrineTypeMapping('enum', 'string');
            Schema::table('LEISTUNGSKATALOG', function (Blueprint $table) {
                $table->string('BEZEICHNUNG', 3000)->change();
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
        if (Schema::hasTable('LEISTUNGSKATALOG')) {
            Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->getDatabasePlatform()
                ->registerDoctrineTypeMapping('enum', 'string');
            Schema::table('LEISTUNGSKATALOG', function (Blueprint $table) {
                $table->string('BEZEICHNUNG', 160)->change();
            });
        }
    }
}
