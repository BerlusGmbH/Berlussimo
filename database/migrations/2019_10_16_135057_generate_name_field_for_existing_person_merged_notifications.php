<?php

use App\Models\Person;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class GenerateNameFieldForExistingPersonMergedNotifications extends Migration
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
                    ->where('type', 'App\Notifications\PersonMerged')
                    ->pluck('data', 'id');
                foreach ($notifications as $id => $data) {
                    $json = json_decode($data, true);
                    foreach (['left', 'right', 'merged'] as $prefix) {
                        $this->insertFullName($json[$prefix]);
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

    protected function insertFullName(&$attributes)
    {
        $p = new Person();
        $p->name = $attributes['name'];
        $p->first_name = $attributes['first_name'];
        $attributes['full_name'] = $p->full_name;
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
