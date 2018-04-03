<?php

namespace App\Http\Controllers\Api\v1\Modules;


use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\Modules\Invoice\UpdateRequest;
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
                },
                'advancePaymentInvoice',
                'advancePaymentInvoices' => function ($query) {
                    $query->orderBy('RECHNUNGSDATUM', 'asc');
                },
                'advancePaymentInvoices.from',
                'advancePaymentInvoices.to',
                'advancePaymentInvoices.advancePaymentInvoice',
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
     * @param UpdateRequest $request
     * @param Invoice $invoice
     * @return mixed
     */
    public function update(UpdateRequest $request, Invoice $invoice)
    {
        $attributes = $request->only([
            'RECHNUNGSNUMMER',
            'RECHNUNGSTYP',
            'RECHNUNGSDATUM',
            'EINGANGSDATUM',
            'FAELLIG_AM',
            'KURZBESCHREIBUNG',
            'advance_payment_invoice_id',
            'servicetime_from',
            'servicetime_to'
        ]);

        if ($attributes['RECHNUNGSTYP'] === 'Buchungsbeleg') {
            Arr::set($attributes, 'EMPFAENGER_TYP', DB::raw('AUSSTELLER_TYP'));
            Arr::set($attributes, 'EMPFAENGER_ID', DB::raw('AUSSTELLER_ID'));
        }

        $this->updateAdvancePaymentInvoices($attributes, $request);

        Invoice::unguarded(function () use ($attributes, $invoice) {
            $invoice->update($attributes);
        });
        return response()->json(['status' => 'ok']);
    }

    protected function updateAdvancePaymentInvoices(& $attributes, UpdateRequest $request)
    {
        if (!$request->has('advance_payment_invoice_id')) {
            $attributes['advance_payment_invoice_id'] = DB::raw('BELEG_NR');
        }

        $group_id = null;

        if ($request->invoiceIsAssigned() || $request->invoiceWasAssigned()) {
            $group_id = $request->advancePaymentInvoices()
                ->orderBy('RECHNUNGSDATUM')
                ->value('BELEG_NR');
        }
        if ($group_id) {
            $request->advancePaymentInvoices()
                ->update(['advance_payment_invoice_id' => $group_id]);
        }

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