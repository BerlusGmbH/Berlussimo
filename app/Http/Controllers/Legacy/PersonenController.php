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
        $personen = $builder->with(['mietvertraege.einheit.haus.objekt', 'kaufvertraege.einheit.haus.objekt'])->paginate(6);
        return view('modules.personen.index', ['personen' => $personen]);
    }

    public function show($id) {
        $person = Personen::with(['mietvertraege.einheit.haus.objekt', 'kaufvertraege.einheit.haus.objekt', 'details'])->find($id);
        return view('modules.personen.show', ['person' => $person]);
    }
}