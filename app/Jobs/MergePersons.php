<?php

namespace App\Jobs;

use App\Models\Details;
use App\Models\Person;
use Auth;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use OwenIt\Auditing\Models\Audit;

class MergePersons implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $parameters, $left, $right, $user;

    /**
     * Create a new job instance.
     *
     * @param array $parameters
     * @param Person $left
     * @param Person $right
     * @param Authenticatable $user
     */
    public function __construct(array $parameters, Person $left, Person $right, Authenticatable $user)
    {
        $this->parameters = $parameters;
        $this->left = $left;
        $this->right = $right;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $guest = false;
        if (Auth::guest()) {
            Auth::login($this->user);
            $guest = true;
        }
        $this->switchLeftToLowerId();
        DB::transaction(function () {
            $this->updatePerson();
            $this->updateAudits();
            $this->updateSepaUeberweisung();
            $this->updateToDoListe();
            $this->updateDetail();
            $this->updateStundenzettel();
            $this->updateStundenzettelPos();
            $this->updateWerkzeuge();
            $this->updateGeldKontenZuweisung();
            $this->updateWTeamsBenutzer();
            $this->updatePersonMietvertrag();
            $this->updateWEGEigentuemerPerson();
            $this->updateCredentials();
            $this->updateProtokoll();
            $this->updateJobs();
            $this->updateUrlaub();
            $this->updatePersonHasPermission();
            $this->updatePersonHasRoles();
            $this->updateGeoTermine();
            $this->updateStartStop();
            $this->updateZugriffError();
            $this->updateWTeamProfile();
            $this->updateWTermine();
            $this->right->delete();
        });
        if ($guest) {
            Auth::logout();
        }
    }

    protected function switchLeftToLowerId()
    {
        if ($this->left->id > $this->right->id) {
            $tmp = $this->left;
            $this->left = $this->right;
            $this->right = $tmp;
        }
    }

    protected function updatePerson()
    {
        $this->left->name = $this->parameters['name'];
        $this->left->first_name = $this->parameters['first_name'];
        $this->left->birthday = $this->parameters['birthday'];
        $this->left->sex = $this->parameters['sex'];
        $this->left->save();
    }

    protected function updateAudits()
    {
        Audit::where('user_id', $this->right->id)->update(['user_id' => $this->left->id]);
    }

    protected function updateSepaUeberweisung()
    {
        $sepa_ueberweisung_table = DB::table('SEPA_UEBERWEISUNG');
        if ($sepa_ueberweisung_table->exists()) {
            $sepa_ueberweisung_table->where('KOS_TYP', 'Person')
                ->where('KOS_ID', $this->right->id)
                ->update([
                    'KOS_ID' => $this->left->id
                ]);
        }
    }

    protected function updateToDoListe()
    {
        $todo_liste_table = DB::table('TODO_LISTE');
        if ($todo_liste_table->exists()) {
            $todo_liste_table->where('KOS_TYP', 'Person')
                ->where('KOS_ID', $this->right->id)
                ->update([
                    'KOS_ID' => $this->left->id
                ]);
            DB::table('TODO_LISTE')->where('BENUTZER_TYP', 'Person')
                ->where('BENUTZER_ID', $this->right->id)
                ->update([
                    'BENUTZER_ID' => $this->left->id
                ]);
            DB::table('TODO_LISTE')->where('VERFASSER_ID', $this->right->id)
                ->update([
                    'VERFASSER_ID' => $this->left->id
                ]);
        }
    }

    protected function updateDetail()
    {
        Details::where('DETAIL_ZUORDNUNG_TABELLE', 'Person')
            ->where('DETAIL_ZUORDNUNG_ID', $this->right->id)
            ->where('DETAIL_NAME', 'Geschlecht')
            ->delete();

        Details::where('DETAIL_ZUORDNUNG_TABELLE', 'Person')
            ->where('DETAIL_ZUORDNUNG_ID', $this->right->id)
            ->update([
                'DETAIL_ZUORDNUNG_ID' => $this->left->id
            ]);
    }

    protected function updateStundenzettel()
    {
        $stundenzettel_table = DB::table('STUNDENZETTEL');
        if ($stundenzettel_table->exists()) {
            $stundenzettel_table->where('BENUTZER_ID', $this->right->id)
                ->update([
                    'BENUTZER_ID' => $this->left->id
                ]);
        }
    }

    protected function updateStundenzettelPos()
    {
        $stundenzettel_pos_table = DB::table('STUNDENZETTEL_POS');
        if ($stundenzettel_pos_table->exists()) {
            $stundenzettel_pos_table->where('KOSTENTRAEGER_TYP', 'Person')
                ->where('KOSTENTRAEGER_ID', $this->right->id)
                ->update([
                    'KOSTENTRAEGER_ID' => $this->left->id
                ]);
        }
    }

    protected function updateWerkzeuge()
    {
        $werkzeuge_table = DB::table('WERKZEUGE');
        if ($werkzeuge_table->exists()) {
            $werkzeuge_table->where('BENUTZER_ID', $this->right->id)
                ->update([
                    'BENUTZER_ID' => $this->left->id
                ]);
        }
    }

    protected function updateGeldKontenZuweisung()
    {
        $geld_konten_zuweisung_table = DB::table('GELD_KONTEN_ZUWEISUNG');
        if ($geld_konten_zuweisung_table->exists()) {
            $geld_konten_zuweisung_table->where('KOSTENTRAEGER_TYP', 'Person')
                ->where('KOSTENTRAEGER_ID', $this->right->id)
                ->update([
                    'KOSTENTRAEGER_ID' => $this->left->id
                ]);
        }
    }

    protected function updateWTeamsBenutzer()
    {
        $w_teams_benutzer_table = DB::table('W_TEAMS_BENUTZER');
        if ($w_teams_benutzer_table->exists()) {
            $w_teams_benutzer_table->where('BENUTZER_ID', $this->right->id)
                ->update([
                    'BENUTZER_ID' => $this->left->id
                ]);
        }
    }

    protected function updatePersonMietvertrag()
    {
        $person_mietvertrag_table = DB::table('PERSON_MIETVERTRAG');
        if ($person_mietvertrag_table->exists()) {
            $person_mietvertrag_table->where('PERSON_MIETVERTRAG_PERSON_ID', $this->right->id)
                ->update([
                    'PERSON_MIETVERTRAG_PERSON_ID' => $this->left->id
                ]);
        }
    }

    protected function updateWEGEigentuemerPerson()
    {
        $weg_eigentuemer_person_table = DB::table('WEG_EIGENTUEMER_PERSON');
        if ($weg_eigentuemer_person_table->exists()) {
            $weg_eigentuemer_person_table->where('PERSON_ID', $this->right->id)
                ->update([
                    'PERSON_ID' => $this->left->id
                ]);
        }
    }

    protected function updateCredentials()
    {
        if (!$this->left->credential && $this->right->credential) {
            $this->left->credential()->save($this->right->credential);
        } elseif ($this->right->credential) {
            $this->right->credential->delete();
        }
    }

    protected function updateProtokoll()
    {
        $protokoll_table = DB::table('PROTOKOLL');
        if ($protokoll_table->exists()) {
            $protokoll_table->where('person_id', $this->right->id)
                ->update([
                    'person_id' => $this->left->id
                ]);
        }
    }

    protected function updateJobs()
    {
        $jobs_table = DB::table('jobs');
        if ($jobs_table->exists()) {
            $jobs_table->where('employee_id', $this->right->id)
                ->update([
                    'employee_id' => $this->left->id
                ]);
        }
    }

    protected function updateUrlaub()
    {
        $urlaub_table = DB::table('URLAUB');
        if ($urlaub_table->exists()) {
            $urlaub_table->where('BENUTZER_ID', $this->right->id)
                ->update([
                    'BENUTZER_ID' => $this->left->id
                ]);
        }
    }

    protected function updatePersonHasPermission()
    {
        $permissions = $this->right->permissions()->pluck('name');
        $this->left->givePermissionTo($permissions);
    }

    protected function updatePersonHasRoles()
    {
        $roles = $this->right->roles()->pluck('name');
        $this->left->assignRole($roles);
    }

    protected function updateGeoTermine()
    {
        $geo_termine_table = DB::table('GEO_TERMINE');
        if ($geo_termine_table->exists()) {
            $geo_termine_table->where('BENUTZER_ID', $this->right->id)
                ->update([
                    'BENUTZER_ID' => $this->left->id
                ]);
        }
    }

    protected function updateStartStop()
    {
        $start_stop_table = DB::table('START_STOP');
        if ($start_stop_table->exists()) {
            $start_stop_table->where('BENUTZER_ID', $this->right->id)
                ->update([
                    'BENUTZER_ID' => $this->left->id
                ]);
        }
    }

    protected function updateZugriffError()
    {
        $zugriff_error_table = DB::table('ZUGRIFF_ERROR');
        if ($zugriff_error_table->exists()) {
            $zugriff_error_table->where('BENUTZER_ID', $this->right->id)
                ->update([
                    'BENUTZER_ID' => $this->left->id
                ]);
        }
    }

    protected function updateWTeamProfile()
    {
        $w_team_profile_table = DB::table('W_TEAM_PROFILE');
        if ($w_team_profile_table->exists()) {
            $w_team_profile_table->where('BENUTZER_ID', $this->right->id)
                ->update([
                    'BENUTZER_ID' => $this->left->id
                ]);
        }
    }

    protected function updateWTermine()
    {
        $w_termine = DB::table('W_TERMINE');
        if ($w_termine->exists()) {
            $w_termine->where('BENUTZER_ID', $this->right->id)
                ->update([
                    'BENUTZER_ID' => $this->left->id
                ]);
        }
    }
}
