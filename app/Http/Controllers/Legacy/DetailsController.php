<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\DetailsRequest;

class DetailsController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.details.php';
    protected $include = 'legacy/options/modules/details.php';

    public function request(DetailsRequest $request)
    {
        return $this->render();
    }
}