<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\SepaRequest;

class SepaController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.sepa.php';
    protected $include = 'legacy/options/modules/sepa.php';

    /**
     * @param $kto
     * @param $blz
     */
    public function calculateIbanBic($kto, $blz)
    {
        include(base_path($this->include));
        ob_clean();
        $sep->get_iban_bic($kto, $blz);
        echo "$sep->IBAN1|$sep->BIC|$sep->BANKNAME_K";
        die;
    }


    public function request(SepaRequest $request)
    {
        return $this->render();
    }
}