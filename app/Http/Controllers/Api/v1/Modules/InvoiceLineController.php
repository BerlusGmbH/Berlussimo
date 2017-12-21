<?php

namespace App\Http\Controllers\Api\v1\Modules;


use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\Modules\Invoice\Line\UpdateRequest;
use App\Http\Requests\Legacy\RechnungenRequest;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\InvoiceLineAssignment;
use DB;
use Illuminate\Support\Arr;

class InvoiceLineController extends Controller
{
    /**
     * @param RechnungenRequest $request
     * @return mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function store(RechnungenRequest $request)
    {
        $attributes = $request->only([
            'U_BELEG_NR',
            'BELEG_NR',
            'ART_LIEFERANT',
            'ARTIKEL_NR',
            'MENGE',
            'PREIS',
            'MWST_SATZ',
            'RABATT_SATZ',
            'SKONTO',
            'GESAMT_NETTO'
        ]);
        $id = InvoiceLine::max('RECHNUNGEN_POS_ID') + 1;
        $pos = InvoiceLine::where('BELEG_NR', $attributes['BELEG_NR'])->max('POSITION') + 1;
        Arr::set($attributes, 'RECHNUNGEN_POS_ID', $id);
        Arr::set($attributes, 'AKTUELL', '1');
        Arr::set($attributes, 'POSITION', $pos);
        return DB::transaction(function () use ($attributes) {
            $line = InvoiceLine::forceCreate($attributes);
            return response()->json($line);
        });
    }

    /**
     * @param RechnungenRequest $request
     * @param InvoiceLine $invoiceLine
     * @return mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function update(UpdateRequest $request, InvoiceLine $invoiceLine)
    {
        $assignedAmount = $invoiceLine->assignedAmount();
        $this->validate($request, [
            'MENGE' => 'numeric|min:' . $assignedAmount
        ]);

        $attributes = $request->only([
            'ART_LIEFERANT',
            'ARTIKEL_NR',
            'MENGE',
            'PREIS',
            'MWST_SATZ',
            'RABATT_SATZ',
            'SKONTO',
            'GESAMT_NETTO'
        ]);
        return DB::transaction(function () use ($attributes, $invoiceLine) {
            InvoiceLine::unguarded(function () use ($attributes, $invoiceLine) {
                $invoiceLine->update($attributes);
            });
            InvoiceLineAssignment::unguarded(function () use ($attributes, $invoiceLine) {
                Arr::forget($attributes, 'GESAMT_NETTO');
                Arr::set($attributes, 'EINZEL_PREIS', Arr::get($attributes, 'PREIS'));
                Arr::forget($attributes, 'PREIS');
                Arr::forget($attributes, 'ART_LIEFERANT');
                Arr::forget($attributes, 'ARTIKEL_NR');
                Arr::forget($attributes, 'MENGE');
                Arr::set($attributes, 'GESAMT_SUMME', DB::raw('MENGE * EINZEL_PREIS'));
                $invoiceLine->assignments()->update($attributes);
            });
            return response()->json(['status' => 'ok']);
        });
    }

    /**
     * @param RechnungenRequest $request
     * @param InvoiceLine $invoiceLine
     * @return mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function destroy(RechnungenRequest $request, InvoiceLine $invoiceLine)
    {
        return DB::transaction(function () use ($invoiceLine) {
            InvoiceLineAssignment::where('BELEG_NR', $invoiceLine->BELEG_NR)
                ->where('POSITION', $invoiceLine->POSITION)
                ->delete();
            InvoiceLineAssignment::where('BELEG_NR', $invoiceLine->BELEG_NR)
                ->where('POSITION', '>', $invoiceLine->POSITION)
                ->update(['POSITION' => DB::raw('POSITION - 1')]);
            InvoiceLine::where('BELEG_NR', $invoiceLine->BELEG_NR)
                ->where('POSITION', '>', $invoiceLine->POSITION)
                ->update(['POSITION' => DB::raw('POSITION - 1')]);
            $invoiceLine->delete();
            return response()->json(['status' => 'ok']);
        });
    }

    /**
     * @param RechnungenRequest $request
     * @return mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function updateBatch(RechnungenRequest $request)
    {
        $attributeNames = [
            'RABATT_SATZ',
            'SKONTO'
        ];
        $attributes = collect($request->only($attributeNames))->reject(function ($name, $key) use ($request) {
            return !$request->has($key);
        })->all();
        $lineIds = $request->input('lines');
        $invoice = InvoiceLine::findOrFail($lineIds[0])->BELEG_NR;
        return DB::transaction(function () use ($attributes, $lineIds, $invoice) {
            InvoiceLine::unguarded(function () use ($attributes, $invoice) {
                InvoiceLineAssignment::where('BELEG_NR', $invoice)->update($attributes);
            });
            Arr::set($attributes, 'GESAMT_NETTO', DB::raw('PREIS * MENGE * ((100 - RABATT_SATZ)/100)'));
            InvoiceLine::unguarded(function () use ($lineIds, $attributes) {
                InvoiceLine::whereIn('RECHNUNGEN_POS_ID', $lineIds)->update($attributes);
            });
            Invoice::updateSums($invoice);
            return response()->json(['status' => 'ok']);
        });
    }
}