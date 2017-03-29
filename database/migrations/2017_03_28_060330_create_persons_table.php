<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Person;
use Carbon\Carbon;

class CreatePersonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('persons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('first_name');
            $table->date('birthday')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::transaction(function () {
            $table = DB::table('PERSON');
            if($table->exists()) {
                $persons = $table->where('PERSON_AKTUELL', '1')->get();
                $carbon = Carbon::parse('1900-01-01');
                foreach ($persons as $person) {
                    $new_person = new Person();
                    $old_persons = DB::table('PERSON')
                        ->where('PERSON_AKTUELL', '0')
                        ->where('PERSON_ID', $person['PERSON_ID'])
                        ->orderBy('PERSON_DAT', 'asc')->get();
                    foreach ($old_persons as $old_person) {
                        $new_person->id = $old_person['PERSON_ID'];
                        $new_person->name = trim($old_person['PERSON_NACHNAME']);
                        $new_person->first_name = trim($old_person['PERSON_VORNAME']);
                        if(is_null($old_person['PERSON_GEBURTSTAG']) || Carbon::parse($old_person['PERSON_GEBURTSTAG'])->lte($carbon) ) {
                            $new_person->birthday = null;
                        } else {
                            $new_person->birthday = $old_person['PERSON_GEBURTSTAG'];
                        }
                        $new_person->save();
                    }
                    $new_person->id = $person['PERSON_ID'];
                    $new_person->name = trim($person['PERSON_NACHNAME']);
                    $new_person->first_name = trim($person['PERSON_VORNAME']);
                    if(is_null($person['PERSON_GEBURTSTAG']) || Carbon::parse($person['PERSON_GEBURTSTAG'])->lte($carbon) ) {
                        $new_person->birthday = null;
                    } else {
                        $new_person->birthday = $person['PERSON_GEBURTSTAG'];
                    }
                    $new_person->save();
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
        Schema::drop('persons');
    }
}
