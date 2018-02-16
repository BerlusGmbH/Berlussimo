<?php

namespace App\Http\Requests\Api\v1\Modules\Invoice\Line;


use App\Http\Requests\Legacy\RechnungenRequest;

class UpdateRequest extends RechnungenRequest
{
    public function rules()
    {
        return [
            'ART_LIEFERANT' => 'required',
            'ARTIKEL_NR' => 'required|string',
            'MENGE' => 'required|numeric',
            'PREIS' => 'required|numeric',
            'MWST_SATZ' => 'required|numeric|max:100',
            'RABATT_SATZ' => 'required|numeric|max:100',
            'SKONTO' => 'required|numeric|max:100',
            'GESAMT_NETTO' => 'required|numeric'
        ];
    }
}