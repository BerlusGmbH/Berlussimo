<?php

namespace App\Models\Traits;


use App\Models\Details;
use Config;
use DB;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Models\Audit;

trait MergePersons
{
    protected function updateRelations(Model $person)
    {
        $this->updateAudits($person);
        $this->updateSepaUeberweisung($person);
        $this->updateToDoListe($person);
        $this->updateDetail($person);
        $this->updateStundenzettel($person);
        $this->updateStundenzettelPos($person);
        $this->updateWerkzeuge($person);
        $this->updateGeldKontenZuweisung($person);
        $this->updateWTeamsBenutzer($person);
        $this->updatePersonMietvertrag($person);
        $this->updateWEGEigentuemerPerson($person);
        $this->updateCredentials($person);
        $this->updateProtokoll($person);
        $this->updateJobs($person);
        $this->updateUrlaub($person);
        $this->updatePersonHasPermission($person);
        $this->updatePersonHasRoles($person);
        $this->updateGeoTermine($person);
        $this->updateStartStop($person);
        $this->updateZugriffError($person);
        $this->updateWTeamProfile($person);
        $this->updateWTermine($person);
    }

    protected function updateAudits($person)
    {
        $key = Config::get('audit.user.foreign_key', 'user_id');
        Audit::where($key, $person->id)->update([$key => $this->id]);
    }

    protected function updateSepaUeberweisung($person)
    {
        $sepa_ueberweisung_table = DB::table('SEPA_UEBERWEISUNG');
        if ($sepa_ueberweisung_table->exists()) {
            $sepa_ueberweisung_table->where('KOS_TYP', 'Person')
                ->where('KOS_ID', $person->id)
                ->update([
                    'KOS_ID' => $this->id
                ]);
        }
    }

    protected function updateToDoListe($person)
    {
        $todo_liste_table = DB::table('TODO_LISTE');
        if ($todo_liste_table->exists()) {
            $todo_liste_table->where('KOS_TYP', 'Person')
                ->where('KOS_ID', $person->id)
                ->update([
                    'KOS_ID' => $this->id
                ]);
            DB::table('TODO_LISTE')->where('BENUTZER_TYP', 'Person')
                ->where('BENUTZER_ID', $person->id)
                ->update([
                    'BENUTZER_ID' => $this->id
                ]);
            DB::table('TODO_LISTE')->where('VERFASSER_ID', $person->id)
                ->update([
                    'VERFASSER_ID' => $this->id
                ]);
        }
    }

    protected function updateDetail($person)
    {
        Details::where('DETAIL_ZUORDNUNG_TABELLE', 'Person')
            ->where('DETAIL_ZUORDNUNG_ID', $person->id)
            ->where('DETAIL_NAME', 'Geschlecht')
            ->delete();

        Details::where('DETAIL_ZUORDNUNG_TABELLE', 'Person')
            ->where('DETAIL_ZUORDNUNG_ID', $person->id)
            ->update([
                'DETAIL_ZUORDNUNG_ID' => $this->id
            ]);
    }

    protected function updateStundenzettel($person)
    {
        $stundenzettel_table = DB::table('STUNDENZETTEL');
        if ($stundenzettel_table->exists()) {
            $stundenzettel_table->where('BENUTZER_ID', $person->id)
                ->update([
                    'BENUTZER_ID' => $this->id
                ]);
        }
    }

    protected function updateStundenzettelPos($person)
    {
        $stundenzettel_pos_table = DB::table('STUNDENZETTEL_POS');
        if ($stundenzettel_pos_table->exists()) {
            $stundenzettel_pos_table->where('KOSTENTRAEGER_TYP', 'Person')
                ->where('KOSTENTRAEGER_ID', $person->id)
                ->update([
                    'KOSTENTRAEGER_ID' => $this->id
                ]);
        }
    }

    protected function updateWerkzeuge($person)
    {
        $werkzeuge_table = DB::table('WERKZEUGE');
        if ($werkzeuge_table->exists()) {
            $werkzeuge_table->where('BENUTZER_ID', $person->id)
                ->update([
                    'BENUTZER_ID' => $this->id
                ]);
        }
    }

    protected function updateGeldKontenZuweisung($person)
    {
        $geld_konten_zuweisung_table = DB::table('GELD_KONTEN_ZUWEISUNG');
        if ($geld_konten_zuweisung_table->exists()) {
            $geld_konten_zuweisung_table->where('KOSTENTRAEGER_TYP', 'Person')
                ->where('KOSTENTRAEGER_ID', $person->id)
                ->update([
                    'KOSTENTRAEGER_ID' => $this->id
                ]);
        }
    }

    protected function updateWTeamsBenutzer($person)
    {
        $w_teams_benutzer_table = DB::table('W_TEAMS_BENUTZER');
        if ($w_teams_benutzer_table->exists()) {
            $w_teams_benutzer_table->where('BENUTZER_ID', $person->id)
                ->update([
                    'BENUTZER_ID' => $this->id
                ]);
        }
    }

    protected function updatePersonMietvertrag($person)
    {
        $person_mietvertrag_table = DB::table('PERSON_MIETVERTRAG');
        if ($person_mietvertrag_table->exists()) {
            $person_mietvertrag_table->where('PERSON_MIETVERTRAG_PERSON_ID', $person->id)
                ->update([
                    'PERSON_MIETVERTRAG_PERSON_ID' => $this->id
                ]);
        }
    }

    protected function updateWEGEigentuemerPerson($person)
    {
        $weg_eigentuemer_person_table = DB::table('WEG_EIGENTUEMER_PERSON');
        if ($weg_eigentuemer_person_table->exists()) {
            $weg_eigentuemer_person_table->where('PERSON_ID', $person->id)
                ->update([
                    'PERSON_ID' => $this->id
                ]);
        }
    }

    protected function updateCredentials($person)
    {
        if (!$this->credential && $person->credential) {
            $this->credential()->save($person->credential);
        } elseif ($person->credential) {
            $person->credential->delete();
        }
    }

    protected function updateProtokoll($person)
    {
        $protokoll_table = DB::table('PROTOKOLL');
        if ($protokoll_table->exists()) {
            $protokoll_table->where('person_id', $person->id)
                ->update([
                    'person_id' => $this->id
                ]);
        }
    }

    protected function updateJobs($person)
    {
        $jobs_table = DB::table('jobs');
        if ($jobs_table->exists()) {
            $jobs_table->where('employee_id', $person->id)
                ->update([
                    'employee_id' => $this->id
                ]);
        }
    }

    protected function updateUrlaub($person)
    {
        $urlaub_table = DB::table('URLAUB');
        if ($urlaub_table->exists()) {
            $urlaub_table->where('BENUTZER_ID', $person->id)
                ->update([
                    'BENUTZER_ID' => $this->id
                ]);
        }
    }

    protected function updatePersonHasPermission($person)
    {
        $permissions = $person->permissions()->pluck('name');
        $this->givePermissionTo($permissions);
    }

    protected function updatePersonHasRoles($person)
    {
        $roles = $person->roles()->pluck('name');
        $this->assignRole($roles);
    }

    protected function updateGeoTermine($person)
    {
        $geo_termine_table = DB::table('GEO_TERMINE');
        if ($geo_termine_table->exists()) {
            $geo_termine_table->where('BENUTZER_ID', $person->id)
                ->update([
                    'BENUTZER_ID' => $this->id
                ]);
        }
    }

    protected function updateStartStop($person)
    {
        $start_stop_table = DB::table('START_STOP');
        if ($start_stop_table->exists()) {
            $start_stop_table->where('BENUTZER_ID', $person->id)
                ->update([
                    'BENUTZER_ID' => $this->id
                ]);
        }
    }

    protected function updateZugriffError($person)
    {
        $zugriff_error_table = DB::table('ZUGRIFF_ERROR');
        if ($zugriff_error_table->exists()) {
            $zugriff_error_table->where('BENUTZER_ID', $person->id)
                ->update([
                    'BENUTZER_ID' => $this->id
                ]);
        }
    }

    protected function updateWTeamProfile($person)
    {
        $w_team_profile_table = DB::table('W_TEAM_PROFILE');
        if ($w_team_profile_table->exists()) {
            $w_team_profile_table->where('BENUTZER_ID', $person->id)
                ->update([
                    'BENUTZER_ID' => $this->id
                ]);
        }
    }

    protected function updateWTermine($person)
    {
        $w_termine = DB::table('W_TERMINE');
        if ($w_termine->exists()) {
            $w_termine->where('BENUTZER_ID', $person->id)
                ->update([
                    'BENUTZER_ID' => $this->id
                ]);
        }
    }
}