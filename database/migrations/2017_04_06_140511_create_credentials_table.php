<?php

use App\Libraries\Role;
use App\Models\Credential;
use App\Models\Details;
use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCredentialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credentials', function (Blueprint $table) {
            $table->unsignedInteger('id')->references('id')->on('persons')->onDelete('cascade');
            $table->string('password');
            $table->string('api_token', 60)->unique();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::transaction(function () {
            $user_table = DB::table('users');
            if ($user_table->exists()) {
                $min_date = new Carbon('1900-01-01');
                $users = $user_table->get();
                foreach ($users as $user) {
                    $person = new Person();
                    $person->timestamps = false;
                    $person->name = $user['name'];
                    if (Carbon::parse($user['birthday'])->lte($min_date)) {
                        $person->birthday = null;
                    } else {
                        $person->birthday = $user['birthday'];
                    }
                    $person->created_at = $user['created_at'];
                    $person->updated_at = $user['updated_at'];
                    $person->save();
                    $credential = new Credential();
                    $credential->password = $user['password'];
                    $credential->api_token = $user['api_token'];
                    $credential->remember_token = $user['remember_token'];
                    $person->credential()->save($credential);
                    $person->details()->create([
                        'DETAIL_ID' => Details::max('DETAIL_ID') + 1,
                        'DETAIL_NAME' => 'Email',
                        'DETAIL_INHALT' => $user['email'],
                        'DETAIL_BEMERKUNG' => 'Stand: ' . Carbon::today()->toDateString(),
                        'DETAIL_AKTUELL' => '1'
                    ]);
                    $module_table = DB::table('BENUTZER_MODULE');
                    if ($module_table->exists()) {
                        $module = $module_table->where('BENUTZER_ID', $user['id'])
                            ->where('AKTUELL', '1')
                            ->first();
                        if ($module['MODUL_NAME'] == '*') {
                            $person->assignRole(Role::ROLE_ADMINISTRATOR);
                        }
                    }
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credentials');
    }
}
