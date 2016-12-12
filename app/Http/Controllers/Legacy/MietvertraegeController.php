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
        $units = DB::select("
          SELECT EINHEIT.EINHEIT_ID, TYP, EINHEIT_KURZNAME, DETAIL_INHALT, MIETVERTRAG_AKTUELL, EINHEIT_AKTUELL,
            IF(MIN(MIETVERTRAG_BIS) = '0000-00-00', MIN(MIETVERTRAG_BIS), MAX(MIETVERTRAG_BIS)) AS MIETVERTRAG_BIS 
          FROM MIETVERTRAG 
            RIGHT JOIN EINHEIT ON (EINHEIT.EINHEIT_ID = MIETVERTRAG.EINHEIT_ID AND MIETVERTRAG_AKTUELL = '1')
            LEFT JOIN DETAIL ON (EINHEIT.EINHEIT_ID = DETAIL.DETAIL_ZUORDNUNG_ID AND DETAIL_ZUORDNUNG_TABELLE = 'EINHEIT' AND DETAIL_NAME = 'Fertigstellung in Prozent' AND DETAIL_AKTUELL = '1')
          WHERE EINHEIT_AKTUELL = '1'
			AND (DETAIL_INHALT > 99 OR DETAIL_INHALT IS NULL)
          GROUP BY EINHEIT.EINHEIT_ID 
          HAVING (MIETVERTRAG_BIS != '0000-00-00' OR MIETVERTRAG_BIS IS NULL)
        ");
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
            $mv_info->mieten_speichern($contract_id, request()->input('move-in-date'), request()->input('move-out-date'), 'Nebenkosten Vorauszahlung', request()->input('bk-advance'), 0);
        }

        if (request()->has('hk-advance')) {
            $mv_info->mieten_speichern($contract_id, request()->input('move-in-date'), request()->input('move-out-date'), 'Heizkosten Vorauszahlung', request()->input('hk-advance'), 0);
        }

        if (request()->has('deposit')) {
            $k = new kautionen ();
            $k->feld_wert_speichern($contract_id, 'SOLL', request()->input('deposit'));
        }

        return redirect(route('legacy::uebersicht::index', ['anzeigen' => 'einheit', 'einheit_id' => request()->input('unit')], false));
    }
}