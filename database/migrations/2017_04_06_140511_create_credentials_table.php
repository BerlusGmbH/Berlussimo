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

class CreateCredentialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws Exception|Throwable
     */
    public function up()
    {
        DB::transaction(function () {
            Schema::create('credentials', function (Blueprint $table) {
                $table->unsignedInteger('id');
                $table->primary('id');
                $table->foreign('id')->references('id')->on('persons')->onDelete('cascade');
                $table->string('password');
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


            if (Schema::hasTable('GEWERKE')) {
                $trades = DB::table('GEWERKE')->where('AKTUELL', '1')->get();
                foreach ($trades as $trade) {
                    JobTitle::create([
                        'id' => $trade['G_ID'],
                        'title' => $trade['BEZEICHNUNG'],
                        'employer_id' => 1
                    ]);
                }
            }

            if (Schema::hasTable('PROTOKOLL')) {
                DB::statement(
                    'ALTER TABLE `PROTOKOLL` 
                    CHANGE COLUMN `PROTOKOLL_WANN` `PROTOKOLL_WANN` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;'
                );
            }

            if (Schema::hasTable('BENUTZER')) {
                $min_date = new Carbon('1900-01-01');
                $users = DB::table('BENUTZER')->get();
                foreach ($users as $user) {
                    $person = new Person();
                    $person->timestamps = false;
                    $person->name = $user['benutzername'];
                    if (Carbon::parse($user['GEB_DAT'])->lte($min_date)) {
                        $person->birthday = null;
                    } else {
                        $person->birthday = $user['GEB_DAT'];
                    }
                    $person->save();
                    $credential = new Credential();
                    $credential->password = Hash::make($user['passwort']);
                    $person->credential()->save($credential);
                    $person->details()->create([
                        'DETAIL_ID' => Details::max('DETAIL_ID') + 1,
                        'DETAIL_NAME' => 'Email',
                        'DETAIL_INHALT' => $user['benutzername'] . '@berlussimo',
                        'DETAIL_BEMERKUNG' => 'Stand: ' . Carbon::today()->toDateString(),
                        'DETAIL_AKTUELL' => '1'
                    ]);
                    if (Schema::hasTable('BENUTZER_MODULE')) {
                        $module = DB::table('BENUTZER_MODULE')->where('BENUTZER_ID', $user['benutzer_id'])
                            ->where('AKTUELL', '1')
                            ->first();
                        if ($module['MODUL_NAME'] == '*') {
                            $person->assignRole(Role::ROLE_ADMINISTRATOR);
                        }
                    }
                    if (Schema::hasTable('BENUTZER_PARTNER')) {
                        $benutzer_partner = DB::table('BENUTZER_PARTNER')->where('BP_BENUTZER_ID', $user['benutzer_id'])
                            ->where('AKTUELL', '1')->first();
                        if (is_null($benutzer_partner)) {
                            $benutzer_partner = ['BP_PARTNER_ID' => 1];
                        }
                        $person->jobsAsEmployee()->create([
                            'join_date' => $user['EINTRITT'],
                            'leave_date' => $user['AUSTRITT'] == '0000-00-00' ? null : $user['AUSTRITT'],
                            'hourly_rate' => $user['STUNDENSATZ'],
                            'hours_per_week' => $user['STUNDEN_PW'],
                            'holidays' => $user['URLAUB'],
                            'job_title_id' => $user['GEWERK_ID'] == 0 ? null : $user['GEWERK_ID'],
                            'employer_id' => $benutzer_partner['BP_PARTNER_ID']
                        ]);
                    }

                    Details::where('DETAIL_ZUORDNUNG_TABELLE', 'Benutzer')
                        ->where('DETAIL_ZUORDNUNG_ID', $user['benutzer_id'])
                        ->update([
                            'DETAIL_ZUORDNUNG_TABELLE' => 'Person',
                            'DETAIL_ZUORDNUNG_ID' => $person->id
                        ]);

                    if (Schema::hasTable('GELD_KONTEN_ZUWEISUNG')) {
                        DB::table('GELD_KONTEN_ZUWEISUNG')->where('KOSTENTRAEGER_TYP', 'Benutzer')
                            ->where('KOSTENTRAEGER_ID', $user['benutzer_id'])
                            ->update([
                                'KOSTENTRAEGER_TYP' => 'Person',
                                'KOSTENTRAEGER_ID' => $person->id
                            ]);
                    }

                    if (Schema::hasTable('GELD_KONTO_BUCHUNGEN')) {
                        DB::table('GELD_KONTO_BUCHUNGEN')->where('KOSTENTRAEGER_TYP', 'Benutzer')
                            ->where('KOSTENTRAEGER_ID', $user['benutzer_id'])
                            ->update([
                                'KOSTENTRAEGER_TYP' => 'Person',
                                'KOSTENTRAEGER_ID' => $person->id
                            ]);
                    }

                    if (Schema::hasTable('GEO_TERMINE')) {
                        DB::table('GEO_TERMINE')->where('BENUTZER_ID', $user['benutzer_id'])
                            ->update([
                                'BENUTZER_ID' => $person->id
                            ]);
                    }

                    if (Schema::hasTable('PROTOKOLL')) {
                        DB::table('PROTOKOLL')->where('PROTOKOLL_WER', $user['benutzername'])
                            ->update([
                                'person_id' => $person->id
                            ]);
                        DB::table('PROTOKOLL')->where('PROTOKOLL_WER', $user['benutzername'] . '@berlussimo')
                            ->update([
                                'person_id' => $person->id
                            ]);
                    }

                    if (Schema::hasTable('SEPA_UEBERWEISUNG')) {
                        DB::table('SEPA_UEBERWEISUNG')->where('KOS_TYP', 'Benutzer')
                            ->where('KOS_ID', $user['benutzer_id'])
                            ->update([
                                'KOS_TYP' => 'Person',
                                'KOS_ID' => $person->id
                            ]);
                    }

                    if (Schema::hasTable('START_STOP')) {
                        DB::table('START_STOP')->where('BENUTZER_ID', $user['benutzer_id'])
                            ->update([
                                'BENUTZER_ID' => $person->id
                            ]);
                    }

                    if (Schema::hasTable('STUNDENZETTEL')) {
                        DB::table('STUNDENZETTEL')->where('BENUTZER_ID', $user['benutzer_id'])
                            ->update([
                                'BENUTZER_ID' => $person->id
                            ]);
                    }

                    if (Schema::hasTable('STUNDENZETTEL_POS')) {
                        DB::table('STUNDENZETTEL_POS')->where('KOSTENTRAEGER_TYP', 'Benutzer')
                            ->where('KOSTENTRAEGER_ID', $user['benutzer_id'])
                            ->update([
                                'KOSTENTRAEGER_TYP' => 'Person',
                                'KOSTENTRAEGER_ID' => $person->id
                            ]);
                    }

                    if (Schema::hasTable('TODO_LISTE')) {
                        DB::table('TODO_LISTE')->where('KOS_TYP', 'Benutzer')
                            ->where('KOS_ID', $user['benutzer_id'])
                            ->update([
                                'KOS_TYP' => 'Person',
                                'KOS_ID' => $person->id
                            ]);
                        DB::table('TODO_LISTE')->where('BENUTZER_TYP', 'Benutzer')
                            ->where('BENUTZER_ID', $user['benutzer_id'])
                            ->update([
                                'BENUTZER_TYP' => 'Person',
                                'BENUTZER_ID' => $person->id
                            ]);
                        DB::table('TODO_LISTE')->where('VERFASSER_ID', $user['benutzer_id'])
                            ->update([
                                'VERFASSER_ID' => $person->id
                            ]);
                    }


                    if (Schema::hasTable('URLAUB')) {
                        DB::table('URLAUB')->where('BENUTZER_ID', $user['benutzer_id'])
                            ->update([
                                'BENUTZER_ID' => $person->id
                            ]);
                    }

                    if (Schema::hasTable('W_TEAM_PROFILE')) {
                        DB::table('W_TEAM_PROFILE')->where('BENUTZER_ID', $user['benutzer_id'])
                            ->update([
                                'BENUTZER_ID' => $person->id
                            ]);
                    }

                    if (Schema::hasTable('W_TEAMS_BENUTZER')) {
                        DB::table('W_TEAMS_BENUTZER')->where('BENUTZER_ID', $user['benutzer_id'])
                            ->update([
                                'BENUTZER_ID' => $person->id
                            ]);
                    }

                    if (Schema::hasTable('WERKZEUGE')) {
                        DB::table('WERKZEUGE')->where('BENUTZER_ID', $user['benutzer_id'])
                            ->update([
                                'BENUTZER_ID' => $person->id
                            ]);
                    }

                    if (Schema::hasTable('ZUGRIFF_ERROR')) {
                        DB::table('ZUGRIFF_ERROR')->where('BENUTZER_ID', $user['benutzer_id'])
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
        if (Schema::hasColumn('PROTOKOLL', 'person_id')) {
            Schema::table('PROTOKOLL', function (Blueprint $table) {
                $table->dropColumn('person_id');
            });
        }
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_titles');
        Schema::dropIfExists('credentials');
    }
}
