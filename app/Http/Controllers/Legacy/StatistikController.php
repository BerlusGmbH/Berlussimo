<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\StatistikRequest;

class StatistikController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.statistik.php';
    protected $include = 'legacy/options/modules/statistik.php';

    public function request(StatistikRequest $request)
    {
        return $this->render();
    }
}