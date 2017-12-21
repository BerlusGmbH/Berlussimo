<?php

namespace App\Http\Controllers\Api\v1\Modules;


use App\Http\Controllers\Controller;
use App\Http\Requests\Legacy\RechnungenRequest;
use App\Models\Invoice;
use App\Models\InvoiceItemUnit;

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
                },
                'assignments.costUnit'
            ]
        )->toArray();
        foreach ($invoiceArray['lines'] as $key => $item) {
            $assignments = $invoice->assignments->where('POSITION', $item['POSITION'])->values();
            $invoiceArray['lines'][$key]['assignments'] = $assignments ?: [];
        }

        unset($invoiceArray['assignments']);

        return $invoiceArray;
    }

    public function units(RechnungenRequest $request)
    {
        return InvoiceItemUnit::defaultOrder()->get();
    }
}