<?php

namespace App\Http\Controllers\Api\v1\Modules;


use App\Http\Controllers\Controller;
use App\Http\Requests\Legacy\GeldkontenRequest;
use App\Models\Bankkonten;

class BankAccountController extends Controller
{

    public function select(GeldkontenRequest $request, Bankkonten $bankaccount)
    {
        session()->put('geldkonto_id', $bankaccount->KONTO_ID);
        $objekt = $bankaccount->objekte()->first();
        if ($objekt) {
            session()->put('objekt_id', $objekt->OBJEKT_ID);
            return [
                'object' => $objekt,
                'status' => 'ok'
            ];
        }
        return ['status' => 'ok'];
    }

    public function unselect(GeldkontenRequest $request)
    {
        session()->forget('geldkonto_id');
        return ['status' => 'ok'];
    }
}