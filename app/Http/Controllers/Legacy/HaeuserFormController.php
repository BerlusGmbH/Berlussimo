<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\HaeuserRequest;

class HaeuserFormController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.form_haus.php';
    protected $include = 'legacy/options/formulare/form_haus.php';

    public function request(HaeuserRequest $request)
    {
        return $this->render();
    }
}