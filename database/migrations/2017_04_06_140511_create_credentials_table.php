<?php

use App\Libraries\Role;
use App\Models\Credential;
use App\Models\Details;
use App\Models\JobTitle;
use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use OwenIt\Auditing\Models\Audit;

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
            $table->unsignedInteger('id');
            $table->primary('id');
            $table->foreign('id')->references('id')->on('persons')->onDelete('cascade');
            $table->string('password');
            $table->string('api_token', 60)->unique();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('job_titles', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employer_id');
            $table->string('title');
            $table->unique(['employer_id', 'title']);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employer_id');
            $table->unsignedInteger('employee_id');
            $table->foreign('employee_id')->references('id')->on('persons');
            $table->unsignedInteger('job_title_id')->nullable();
            $table->foreign('job_title_id')->references('id')->on('job_titles');
            $table->date('join_date');
            $table->date('leave_date')->nullable();
            $table->decimal('hourly_rate')->nullable();
            $table->decimal('hours_per_week')->nullable();
            $table->decimal('holidays');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('PROTOKOLL', function (Blueprint $table) {
            $table->unsignedInteger('person_id');
        });

        DB::transaction(function () {
            $trade_table = DB::table('GEWERKE');
            if ($trade_table->exists()) {
                $trades = $trade_table->where('AKTUELL', '1')->get();
                foreach ($trades as $trade) {
                    JobTitle::create([
                        'id' => $trade['G_ID'],
                        'title' => $trade['BEZEICHNUNG'],
                        'employer_id' => 1
                    ]);
                }
            }

            $protokoll_table = DB::table('PROTOKOLL');
            if ($protokoll_table->exists()) {
                DB::statement(
                    'ALTER TABLE `PROTOKOLL` 
                    CHANGE COLUMN `PROTOKOLL_WANN` `PROTOKOLL_WANN` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;'
                );
            }

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
                    $benutzer_partner_table = DB::table('BENUTZER_PARTNER');
                    if ($benutzer_partner_table->exists()) {
                        $benutzer_partner = $benutzer_partner_table->where('BP_BENUTZER_ID', $user['id'])
                            ->where('AKTUELL', '1')->first();
                        if (is_null($benutzer_partner)) {
                            $benutzer_partner = ['BP_PARTNER_ID' => 1];
                        }
                        $person->jobsAsEmployee()->create([
                            'join_date' => $user['join_date'],
                            'leave_date' => $user['leave_date'] == '0000-00-00' ? null : $user['leave_date'],
                            'hourly_rate' => $user['hourly_rate'],
                            'hours_per_week' => $user['hours_per_week'],
                            'holidays' => $user['holidays'],
                            'job_title_id' => $user['trade_id'] == 0 ? null : $user['trade_id'],
                            'employer_id' => $benutzer_partner['BP_PARTNER_ID']
                        ]);
                    }

                    Audit::where('user_id', $user['id'])->update(['user_id' => $person->id]);

                    Details::where('DETAIL_ZUORDNUNG_TABELLE', 'BENUTZER')
                        ->where('DETAIL_ZUORDNUNG_ID', $user['id'])
                        ->update([
                            'DETAIL_ZUORDNUNG_TABELLE' => 'PERSON',
                            'DETAIL_ZUORDNUNG_ID' => $person->id
                        ]);

                    $geld_konten_zuweisung_table = DB::table('GELD_KONTEN_ZUWEISUNG');
                    if ($geld_konten_zuweisung_table->exists()) {
                        $geld_konten_zuweisung_table->where('KOSTENTRAEGER_TYP', 'BENUTZER')
                            ->where('KOSTENTRAEGER_ID', $user['id'])
                            ->update([
                                'KOSTENTRAEGER_TYP' => 'PERSON',
                                'KOSTENTRAEGER_ID' => $person->id
                            ]);
                    }

                    $geld_konto_buchungen_table = DB::table('GELD_KONTO_BUCHUNGEN');
                    if ($geld_konto_buchungen_table->exists()) {
                        $geld_konto_buchungen_table->where('KOSTENTRAEGER_TYP', 'BENUTZER')
                            ->where('KOSTENTRAEGER_ID', $user['id'])
                            ->update([
                                'KOSTENTRAEGER_TYP' => 'PERSON',
                                'KOSTENTRAEGER_ID' => $person->id
                            ]);
                    }

                    $geo_termine_table = DB::table('GEO_TERMINE');
                    if ($geo_termine_table->exists()) {
                        $geo_termine_table->where('BENUTZER_ID', $user['id'])
                            ->update([
                                'BENUTZER_ID' => $person->id
                            ]);
                    }

                    $protokoll_table = DB::table('PROTOKOLL');
                    if ($protokoll_table->exists()) {
                        $protokoll_table->where('PROTOKOLL_WER', $user['name'])
                            ->update([
                                'person_id' => $person->id
                            ]);
                        DB::table('PROTOKOLL')->where('PROTOKOLL_WER', $user['email'])
                            ->update([
                                'person_id' => $person->id
                            ]);
                    }

                    $sepa_ueberweisung_table = DB::table('SEPA_UEBERWEISUNG');
                    if ($sepa_ueberweisung_table->exists()) {
                        $sepa_ueberweisung_table->where('KOS_TYP', 'BENUTZER')
                            ->where('KOS_ID', $user['id'])
                            ->update([
                                'KOS_TYP' => 'Person',
                                'KOS_ID' => $person->id
                            ]);
                    }

                    $start_stop_table = DB::table('START_STOP');
                    if ($start_stop_table->exists()) {
                        $start_stop_table->where('BENUTZER_ID', $user['id'])
                            ->update([
                                'BENUTZER_ID' => $person->id
                            ]);
                    }

                    $stundenzettel_table = DB::table('STUNDENZETTEL');
                    if ($stundenzettel_table->exists()) {
                        $stundenzettel_table->where('BENUTZER_ID', $user['id'])
                            ->update([
                                'BENUTZER_ID' => $person->id
                            ]);
                    }

                    $stundenzettel_pos_table = DB::table('STUNDENZETTEL_POS');
                    if ($stundenzettel_pos_table->exists()) {
                        $stundenzettel_pos_table->where('KOSTENTRAEGER_TYP', 'BENUTZER')
                            ->where('KOSTENTRAEGER_ID', $user['id'])
                            ->update([
                                'KOSTENTRAEGER_TYP' => 'Person',
                                'KOSTENTRAEGER_ID' => $person->id
                            ]);
                    }

                    $todo_liste_table = DB::table('TODO_LISTE');
                    if ($todo_liste_table->exists()) {
                        $todo_liste_table->where('KOS_TYP', 'BENUTZER')
                            ->where('KOS_ID', $user['id'])
                            ->update([
                                'KOS_TYP' => 'Person',
                                'KOS_ID' => $person->id
                            ]);
                        DB::table('TODO_LISTE')->where('BENUTZER_TYP', 'BENUTZER')
                            ->where('BENUTZER_ID', $user['id'])
                            ->update([
                                'BENUTZER_TYP' => 'Person',
                                'BENUTZER_ID' => $person->id
                            ]);
                        DB::table('TODO_LISTE')->where('VERFASSER_ID', $user['id'])
                            ->update([
                                'VERFASSER_ID' => $person->id
                            ]);
                    }

                    $urlaub_table = DB::table('URLAUB');
                    if ($urlaub_table->exists()) {
                        $urlaub_table->where('BENUTZER_ID', $user['id'])
                            ->update([
                                'BENUTZER_ID' => $person->id
                            ]);
                    }

                    $w_team_profile_table = DB::table('W_TEAM_PROFILE');
                    if ($w_team_profile_table->exists()) {
                        $w_team_profile_table->where('BENUTZER_ID', $user['id'])
                            ->update([
                                'BENUTZER_ID' => $person->id
                            ]);
                    }

                    $w_teams_benutzer_table = DB::table('W_TEAMS_BENUTZER');
                    if ($w_teams_benutzer_table->exists()) {
                        $w_teams_benutzer_table->where('BENUTZER_ID', $user['id'])
                            ->update([
                                'BENUTZER_ID' => $person->id
                            ]);
                    }

                    $werkzeuge_table = DB::table('WERKZEUGE');
                    if ($werkzeuge_table->exists()) {
                        $werkzeuge_table->where('BENUTZER_ID', $user['id'])
                            ->update([
                                'BENUTZER_ID' => $person->id
                            ]);
                    }

                    $zugriff_error_table = DB::table('ZUGRIFF_ERROR');
                    if ($zugriff_error_table->exists()) {
                        $zugriff_error_table->where('BENUTZER_ID', $user['id'])
                            ->update([
                                'BENUTZER_ID' => $person->id
                            ]);
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
        Schema::table('PROTOKOLL', function (Blueprint $table) {
            $table->dropColumn('person_id');
        });
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_titles');
        Schema::dropIfExists('credentials');
    }
}
