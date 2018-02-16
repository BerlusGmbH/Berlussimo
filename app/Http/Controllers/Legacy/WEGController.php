<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\WEGRequest;
use DB;
use URL;

class WEGController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.weg.php';
    protected $include = 'legacy/options/modules/weg.php';

    public function request(WEGRequest $request)
    {
        return $this->render();
    }

    public function change_su(WEGRequest $request)
    {
        $id = request()->input('profil_id');
        $konto = request()->input('konto');
        DB::update(
            'UPDATE WEG_HGA_ZEILEN SET SU_AUSZAHLEN=!SU_AUSZAHLEN 
            WHERE WEG_HG_P_ID=' . $id . ' 
            AND KONTO=' . $konto . ' 
            AND AKTUELL=\'1\'');
        return redirect(URL::previous());
    }
}