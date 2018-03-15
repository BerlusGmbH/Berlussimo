<?php

namespace App\Http\Requests\Api\v1\Modules\Invoice;


use App\Http\Requests\Legacy\RechnungenRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends RechnungenRequest
{
    public function rules()
    {
        $rules = [
            'BELEG_NR' => 'required|numeric',
            'RECHNUNGSNUMMER' => 'required',
            'AUSTELLER_AUSGANGS_RNR' => 'required|numeric',
            'EMPFAENGER_EINGANGS_RNR' => 'required|numeric',
            'RECHNUNGSTYP' => [
                'required',
                Rule::in([
                    'Rechnung',
                    'Buchungsbeleg',
                    'Gutschrift',
                    'Stornorechnung',
                    'Angebot',
                    'Teilrechnung',
                    'Schlussrechnung'
                ])
            ],
            'RECHNUNGSDATUM' => 'required|date_format:Y-m-d',
            'EINGANGSDATUM' => 'required|date_format:Y-m-d',
            'FAELLIG_AM' => 'required|date_format:Y-m-d',
            'advance_payment_invoice_id' => 'numeric|nullable'
        ];

        return $rules;
    }
}