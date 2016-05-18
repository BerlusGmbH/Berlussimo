<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\MietvertraegeRequest;
use App\Http\Requests\Modules\Mietvertraege\StoreMietvertraegeRequest;
use berlussimo_global;
use kautionen;
use mietvertraege;
use personen;
use DB;

class MietvertraegeController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.mietvertrag.php';
    protected $include = 'legacy/options/modules/mietvertrag.php';

    public function request(MietvertraegeRequest $request)
    {
        return $this->render();
    }

    public function create()
    {
        $units = DB::select("SELECT EINHEIT.EINHEIT_ID, TYP, EINHEIT_KURZNAME, IF(MIN(MIETVERTRAG_BIS) = '0000-00-00', MIN(MIETVERTRAG_BIS), MAX(MIETVERTRAG_BIS)) AS MIETVERTRAG_BIS FROM MIETVERTRAG RIGHT JOIN EINHEIT ON (EINHEIT.EINHEIT_ID = MIETVERTRAG.EINHEIT_ID) WHERE MIETVERTRAG_AKTUELL = '1' AND EINHEIT_AKTUELL = '1' GROUP BY EINHEIT.EINHEIT_ID HAVING MIETVERTRAG_BIS != '0000-00-00'");
        $p = new personen();
        $personen = $p->personen_arr();
        return view('modules.mietvertraege.create', ['units' => $units, 'tenants' => $personen]);
    }

    public function store(StoreMietvertraegeRequest $request)
    {
        $contract_id = mietvertrag_anlegen(request()->input('move-in-date'), request()->input('move-out-date'), request()->input('unit'));

        foreach ($request->input('tenants') as $person_id => $person_name) {
            person_zu_mietvertrag($person_id, $contract_id);
        }

        $mv_info = new mietvertraege ();
        $mv_info->mieten_speichern($contract_id, request()->input('move-in-date'), request()->input('move-out-date'), 'Miete kalt', request()->input('rent'), 0);

        if (request()->has('bk-advance')) {
            $mv_info->mieten_speichern($contract_id, request()->input('move-in-date'), request()->input('move-out-date'), 'Heizkosten Vorauszahlung', request()->input('bk-advance'), 0);
        }

        if (request()->has('nk-advance')) {
            $mv_info->mieten_speichern($contract_id, request()->input('move-in-date'), request()->input('move-out-date'), 'Nebenkosten Vorauszahlung', request()->input('nk-advance'), 0);
        }

        if (request()->has('deposit')) {
            $k = new kautionen ();
            $k->feld_wert_speichern($contract_id, 'SOLL', request()->input('deposit'));
        }

        return redirect(route('legacy::uebersicht::index', ['anzeigen' => 'einheit', 'einheit_id' => request()->input('unit')], false));
    }
}