<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\RechnungenRequest;
use DB;
use URL;

class RechnungenController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.rechnungen.php';
    protected $include = 'legacy/options/modules/rechnungen.php';

    public function request(RechnungenRequest $request)
    {
        return $this->render();
    }

    public function belegpool_destroy(RechnungenRequest $request, $id)
    {
        DB::delete('DELETE FROM BELEG2RG WHERE DAT=' . $id);
        return redirect(URL::previous());
    }
}