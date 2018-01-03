<?php

use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePersonsTable extends Migration
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
            Schema::create('persons', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('first_name')->nullable()->default(null);
                $table->date('birthday')->nullable()->default(null);
                $table->timestamps();
                $table->softDeletes();
            });

            if (Schema::hasTable('PERSON')) {
                $persons = DB::table('PERSON')->where('PERSON_AKTUELL', '1')->get();
                $carbon = Carbon::parse('1900-01-01');
                foreach ($persons as $person) {
                    $new_person = new Person();
                    $old_persons = DB::table('PERSON')
                        ->where('PERSON_AKTUELL', '0')
                        ->where('PERSON_ID', $person['PERSON_ID'])
                        ->orderBy('PERSON_DAT', 'asc')->get();
                    $previous_dat = 0;
                    foreach ($old_persons as $old_person) {
                        $new_person->id = $old_person['PERSON_ID'];
                        $new_person->name = trim($old_person['PERSON_NACHNAME']);
                        $new_person->first_name = trim($old_person['PERSON_VORNAME']) === '' ? null : trim($old_person['PERSON_VORNAME']);
                        if(is_null($old_person['PERSON_GEBURTSTAG']) || Carbon::parse($old_person['PERSON_GEBURTSTAG'])->lte($carbon) ) {
                            $new_person->birthday = null;
                        } else {
                            $new_person->birthday = $old_person['PERSON_GEBURTSTAG'];
                        }
                        $new_person->save();

                        $protokoll = DB::table('PROTOKOLL')
                            ->where('PROTOKOLL_TABELE', 'PERSON')
                            ->where('PROTOKOLL_DAT_NEU', $old_person['PERSON_DAT'])
                            ->where('PROTOKOLL_DAT_ALT', $previous_dat)->first();
                        if ($protokoll) {
                            $audit = $new_person->audits()->orderBy('created_at', 'desc')->orderBy('id', 'desc')->first();
                            $audit->created_at = $protokoll['PROTOKOLL_WANN'];
                            $audit->ip_address = $protokoll['PROTOKOLL_COMPUTER'];
                            $user_id = DB::table('BENUTZER')
                                ->where('benutzername', $protokoll['PROTOKOLL_WER'])->select(['benutzer_id'])->first();
                            if (!$user_id) {
                                $user_id = \App\Models\User::where('email', $protokoll['PROTOKOLL_WER'])->select(['id'])->first();
                                if ($user_id) {
                                    $user_id = $user_id->id;
                                }
                            } else {
                                $user_id = $user_id['benutzer_id'];
                            }
                            if ($user_id) {
                                $audit->person_id = $user_id;
                            }
                            $audit->save();
                        } else {
                            $audit = $new_person->audits()->orderBy('created_at', 'desc')->orderBy('id', 'desc')->first();
                            $audit->created_at = '1970-01-01 12:00:00';
                            $audit->save();
                        }
                        $previous_dat = $old_person['PERSON_DAT'];
                    }
                    $new_person->id = $person['PERSON_ID'];
                    $new_person->name = trim($person['PERSON_NACHNAME']);
                    $new_person->first_name = trim($person['PERSON_VORNAME']) === '' ? null : trim($person['PERSON_VORNAME']);
                    if(is_null($person['PERSON_GEBURTSTAG']) || Carbon::parse($person['PERSON_GEBURTSTAG'])->lte($carbon) ) {
                        $new_person->birthday = null;
                    } else {
                        $new_person->birthday = $person['PERSON_GEBURTSTAG'];
                    }
                    $new_person->save();

                    $protokoll = DB::table('PROTOKOLL')
                        ->where('PROTOKOLL_TABELE', 'PERSON')
                        ->where('PROTOKOLL_DAT_NEU', $person['PERSON_DAT'])
                        ->where('PROTOKOLL_DAT_ALT', $previous_dat)->first();
                    if ($protokoll) {
                        $audit = $new_person->audits()->orderBy('created_at', 'desc')->orderBy('id', 'desc')->first();
                        $audit->created_at = $protokoll['PROTOKOLL_WANN'];
                        $audit->ip_address = $protokoll['PROTOKOLL_COMPUTER'];
                        $user_id = DB::table('BENUTZER')
                            ->where('benutzername', $protokoll['PROTOKOLL_WER'])->select('benutzer_id')->first();
                        if (!$user_id) {
                            $user_id = \App\Models\User::where('email', $protokoll['PROTOKOLL_WER'])->select('id')->first();
                            if ($user_id) {
                                $user_id = $user_id->id;
                            }
                        } else {
                            $user_id = $user_id['benutzer_id'];
                        }
                        if ($user_id) {
                            $audit->person_id = $user_id;
                        }
                        $audit->save();
                    } else {
                        $audit = $new_person->audits()->orderBy('created_at', 'desc')->orderBy('id', 'desc')->first();
                        $audit->created_at = '1970-01-01 12:00:00';
                        $audit->save();
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
        Schema::dropIfExists('persons');
    }
}
