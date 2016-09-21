<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\PersonenRequest;

class PersonenController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.person.php';
    protected $include = 'legacy/options/modules/person.php';

    public function request(PersonenRequest $request)
    {
        return $this->render();
    }
}