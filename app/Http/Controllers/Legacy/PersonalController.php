<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\PersonalRequest;

class PersonalController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.personal.php';
    protected $include = 'legacy/options/modules/personal.php';

    public function request(PersonalRequest $request)
    {
        return $this->render();
    }
}