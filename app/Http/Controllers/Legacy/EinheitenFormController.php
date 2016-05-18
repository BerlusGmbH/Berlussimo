<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\EinheitenRequest;

class EinheitenFormController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.form_einheit.php';
    protected $include = 'legacy/options/formulare/form_einheit.php';

    public function request(EinheitenRequest $request)
    {
        return $this->render();
    }
}