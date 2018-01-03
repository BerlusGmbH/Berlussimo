<?php

namespace App\Http\Controllers\Api\v1\Modules;


use App\Http\Controllers\Controller;
use App\Http\Requests\Legacy\RechnungenRequest;
use App\Models\Invoice;
use App\Models\InvoiceItemUnit;
use DB;
use Illuminate\Support\Arr;

class InvoiceController extends Controller
{
    public function show(RechnungenRequest $request, Invoice $invoice)
    {
        $invoiceArray = $invoice->load(
            [
                'bankAccount',
                'from',
                'to',
                'lines' => function ($query) {
                    $query->orderBy('POSITION');
                }
            ]
        )->toArray();
        foreach ($invoiceArray['lines'] as $key => $item) {
            $filteredAssignments = $invoice->assignments()->with('costUnit')->where('POSITION', $item['POSITION'])->get()->toArray();
            $invoiceArray['lines'][$key]['assignments'] = $filteredAssignments ?: [];
        }

        unset($invoiceArray['assignments']);

        return $invoiceArray;
    }

    /**
     * @param RechnungenRequest $request
     * @param Invoice $invoice
     * @return mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function update(RechnungenRequest $request, Invoice $invoice)
    {
        $attributes = $request->only([
            'RECHNUNGSNUMMER',
            'RECHNUNGSTYP',
            'RECHNUNGSDATUM',
            'EINGANGSDATUM',
            'FAELLIG_AM',
            'KURZBESCHREIBUNG'
        ]);

        if ($attributes['RECHNUNGSTYP'] === 'Buchungsbeleg') {
            Arr::set($attributes, 'EMPFAENGER_TYP', DB::raw('AUSSTELLER_TYP'));
            Arr::set($attributes, 'EMPFAENGER_ID', DB::raw('AUSSTELLER_ID'));
        }

        Invoice::unguarded(function () use ($attributes, $invoice) {
            $invoice->update($attributes);
        });
        return response()->json(['status' => 'ok']);
    }

    public function units(RechnungenRequest $request)
    {
        return InvoiceItemUnit::defaultOrder()->get();
    }

    public function types(RechnungenRequest $request)
    {
        return Invoice::getPossibleEnumValues('RECHNUNGSTYP');
    }
}