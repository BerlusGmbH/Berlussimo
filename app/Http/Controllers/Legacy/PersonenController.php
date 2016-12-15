<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\PersonenRequest;
use App\Models\Personen;

class PersonenController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.person.php';
    protected $include = 'legacy/options/modules/person.php';

    public function request(PersonenRequest $request)
    {
        return $this->render();
    }

    public function index() {
        if (request()->has('q')) {
            $builder = Personen::search(request()->input('q'));
        } else {
            $builder = Personen::query();
        }
        $personen = $builder->with(['mietvertraege' => function($builder) {
            $builder->wherePivot('PERSON_MIETVERTRAG_AKTUELL', '1');
        }, 'mietvertraege.einheit.haus.objekt', 'eigentuemer' => function($builder) {
            $builder->wherePivot('AKTUELL', '1');
        }, 'eigentuemer.einheit.haus.objekt'])->paginate(6);
        return view('modules.personen.index', ['personen' => $personen]);
    }
}