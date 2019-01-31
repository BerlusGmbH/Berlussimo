<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class RenameIdsInNotifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('notifications')) {
            DB::transaction(function () {
                $notifications = DB::table('notifications')
                    ->where('type', 'App\Notifications\PropertyCopied')
                    ->pluck('data', 'id');
                foreach ($notifications as $id => $data) {
                    $json = json_decode($data, true);
                    foreach (['source', 'target'] as $prefix) {
                        $this->moveKey($json, $prefix . '.OBJEKT_ID', $prefix . '.id');
                    }
                    DB::table('notifications')
                        ->where('id', $id)
                        ->update([
                            'data' => json_encode($json)
                        ]);
                }
            });
        }
    }

    protected function moveKey(&$array, $sourcePath, $targetPath)
    {
        if (Arr::has($array, $sourcePath)) {
            Arr::set($array, $targetPath, Arr::pull($array, $sourcePath));
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
