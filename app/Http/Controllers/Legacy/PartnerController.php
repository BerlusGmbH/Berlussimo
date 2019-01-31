<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Requests\Legacy\PartnerRequest;

class PartnerController extends LegacyController
{
    protected $submenu = 'legacy/options/links/links.partner.php';
    protected $include = 'legacy/options/modules/partner.php';

    public function select($id)
    {
        session()->put('partner_id', $id);

        return redirect()->intended(route('web::legacy', [], false));
    }

    public function request(PartnerRequest $request)
    {
        return $this->render();
    }
}
