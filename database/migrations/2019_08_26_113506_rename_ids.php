<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameIds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws \Doctrine\DBAL\DBALException
     */
    public function up()
    {
        $tables = [
            'BAUSTELLEN_EXT' => 'ID',
            'EINHEIT' => 'EINHEIT_ID',
            'HAUS' => 'HAUS_ID',
            'MIETVERTRAG' => 'MIETVERTRAG_ID',
            'OBJEKT' => 'OBJEKT_ID',
            'PARTNER_LIEFERANT' => 'PARTNER_ID',
            'WEG_MITEIGENTUEMER' => 'ID',
            'WIRT_EINHEITEN' => 'W_ID',
        ];
        DB::transaction(function () use ($tables) {
            foreach ($tables as $tableName => $previousIdName) {
                if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, $previousIdName)) {
                    Schema::getConnection()
                        ->getDoctrineSchemaManager()
                        ->getDatabasePlatform()
                        ->registerDoctrineTypeMapping('enum', 'string');

                    Schema::table($tableName, function (Blueprint $table) use ($previousIdName) {
                        $table->renameColumn($previousIdName, 'id');
                    });

                    DB::unprepared("DROP TRIGGER IF EXISTS `new_id_on_$tableName`;");

                    DB::unprepared("
CREATE TRIGGER new_id_on_$tableName 
BEFORE INSERT
   ON $tableName FOR EACH ROW

BEGIN
	
	IF NEW.id = 0 || NEW.id IS NULL THEN
		SET NEW.id = (SELECT COALESCE(MAX(id), 0) + 1 FROM $tableName);
	END IF;

END
");
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public
    function down()
    {
    }
}
