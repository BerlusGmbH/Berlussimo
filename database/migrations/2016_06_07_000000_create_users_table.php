<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('api_token', 60)->unique();
            $table->date('birthday');
            $table->date('join_date');
            $table->date('leave_date');
            $table->decimal('hourly_rate');
            $table->decimal('hours_per_week');
            $table->decimal('holidays');
            $table->integer('trade_id');
            $table->integer('menu_nodes_id')->unsigned();
            $table->foreign('menu_nodes_id')->references('id')->on('menu_nodes')->onDelete('cascade');
            $table->rememberToken();
            $table->timestamps();
        });

        $table = DB::table('BENUTZER');
        if ($table->exists()) {
            DB::transaction(function () use ($table) {
                $benutzers = $table->get();
                foreach ($benutzers as $benutzer) {
                    $user = new User();
                    $user->id = $benutzer->benutzer_id;
                    $user->name = $benutzer->benutzername;
                    $user->email = Str::lower($benutzer->benutzername) . "@berlussimo";
                    $user->password = Hash::make($benutzer->passwort);
                    $user->api_token = str_random(60);
                    $user->birthday = $benutzer->GEB_DAT;
                    $user->join_date = $benutzer->EINTRITT;
                    $user->leave_date = $benutzer->AUSTRITT;
                    $user->hourly_rate = $benutzer->STUNDENSATZ;
                    $user->hours_per_week = $benutzer->STUNDEN_PW;
                    $user->holidays = $benutzer->URLAUB;
                    $user->trade_id = $benutzer->GEWERK_ID;
                    $user->save();
                }
            });
        };

        $user = new User();
        $user->name = 'admin';
        $user->email = 'admin@berlussimo';
        $user->password = Hash::make('admin');
        $user->api_token = str_random(60);
        $user->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
