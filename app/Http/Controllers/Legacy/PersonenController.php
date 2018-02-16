<?php

namespace App\Http\Controllers\Legacy;


use App;
use App\Http\Requests\Legacy\PersonenRequest;
use App\Models\Person;

class PersonenController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.person.php';
    protected $include = 'legacy/options/modules/person.php';

    public function request(PersonenRequest $request)
    {
        return $this->render();
    }

    public function index(PersonenRequest $request)
    {
        return view('modules.personen.index');
    }

    public function show(Person $personen, PersonenRequest $request)
    {
        return view('modules.personen.show', ['person' => $personen]);
    }
}