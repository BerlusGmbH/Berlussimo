<?php

namespace App\Http\Controllers\Api\v1\Modules;


use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\Modules\Invoice\Line\UpdateRequest;
use App\Http\Requests\Legacy\RechnungenRequest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
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
        $line = InvoiceLine::forceCreate($attributes);

        Invoice::unguarded(function () use ($line) {
            $line->invoice->update([
                'STATUS_VOLLSTAENDIG' => '1'
            ]);
        });

        $itemExists = InvoiceItem::where('ART_LIEFERANT', $request->input('ART_LIEFERANT'))
            ->where('ARTIKEL_NR', $request->input('ARTIKEL_NR'))
            ->where('LISTENPREIS', $request->input('PREIS'))
            ->where('MWST_SATZ', $request->input('MWST_SATZ'))
            ->where('RABATT_SATZ', $request->input('RABATT_SATZ'))
            ->where('SKONTO', $request->input('SKONTO'))
            ->where('EINHEIT', $request->input('EINHEIT'))
            ->where('BEZEICHNUNG', $request->input('BEZEICHNUNG'))
            ->exists();

        if (!$itemExists) {
            InvoiceItem::forceCreate([
                'KATALOG_ID' => InvoiceItem::max('KATALOG_ID') + 1,
                'ART_LIEFERANT' => $request->input('ART_LIEFERANT'),
                'ARTIKEL_NR' => $request->input('ARTIKEL_NR'),
                'LISTENPREIS' => $request->input('PREIS'),
                'MWST_SATZ' => $request->input('MWST_SATZ'),
                'RABATT_SATZ' => $request->input('RABATT_SATZ'),
                'SKONTO' => $request->input('SKONTO'),
                'EINHEIT' => $request->input('EINHEIT'),
                'BEZEICHNUNG' => $request->input('BEZEICHNUNG'),
                'AKTUELL' => '1'
            ]);
        }
        return response()->json($line);
    }

    /**
     * @param UpdateRequest $request
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
        InvoiceLine::unguarded(function () use ($attributes, $invoiceLine) {
            $invoiceLine->update($attributes);
        });
        InvoiceItem::unguarded(function () use ($request) {
            $item = InvoiceItem::where('ART_LIEFERANT', $request->input('ART_LIEFERANT'))
                ->where('ARTIKEL_NR', $request->input('ARTIKEL_NR'))
                ->where('AKTUELL', '1')
                ->orderBy('KATALOG_ID', 'DESC')
                ->first();
            if ($item) {
                $item->update([
                    'BEZEICHNUNG' => $request->input('BEZEICHNUNG'),
                    'EINHEIT' => $request->input('EINHEIT')
                ]);
            } else {
                InvoiceItem::forceCreate([
                    'KATALOG_ID' => InvoiceItem::max('KATALOG_ID') + 1,
                    'ART_LIEFERANT' => $request->input('ART_LIEFERANT'),
                    'ARTIKEL_NR' => $request->input('ARTIKEL_NR'),
                    'LISTENPREIS' => $request->input('PREIS'),
                    'MWST_SATZ' => $request->input('MWST_SATZ'),
                    'RABATT_SATZ' => $request->input('RABATT_SATZ'),
                    'SKONTO' => $request->input('SKONTO'),
                    'EINHEIT' => $request->input('EINHEIT'),
                    'BEZEICHNUNG' => $request->input('BEZEICHNUNG'),
                    'AKTUELL' => '1'
                ]);
            }
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
        InvoiceLineAssignment::where('BELEG_NR', $invoiceLine->BELEG_NR)
            ->where('POSITION', $invoiceLine->POSITION)
            ->delete();
        InvoiceLineAssignment::where('BELEG_NR', $invoiceLine->BELEG_NR)
            ->where('POSITION', '>', $invoiceLine->POSITION)
            ->update(['POSITION' => DB::raw('POSITION - 1')]);
        InvoiceLine::where('BELEG_NR', $invoiceLine->BELEG_NR)
            ->where('POSITION', '>', $invoiceLine->POSITION)
            ->update(['POSITION' => DB::raw('POSITION - 1')]);
        $invoice = $invoiceLine->invoice;
        $invoiceLine->delete();
        if (!$invoice->lines()->exists()) {
            Invoice::unguarded(function () use ($invoiceLine) {
                $invoiceLine->invoice->update([
                    'STATUS_VOLLSTAENDIG' => '0'
                ]);
            });
        }
        return response()->json(['status' => 'ok']);
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
        $invoiceId = InvoiceLine::findOrFail($lineIds[0])->BELEG_NR;
        InvoiceLine::unguarded(function () use ($attributes, $invoiceId) {
            InvoiceLineAssignment::where('BELEG_NR', $invoiceId)->update($attributes);
        });
        Arr::set($attributes, 'GESAMT_NETTO', DB::raw('PREIS * MENGE * ((100 - RABATT_SATZ)/100)'));
        InvoiceLine::unguarded(function () use ($lineIds, $attributes) {
            InvoiceLine::whereIn('RECHNUNGEN_POS_ID', $lineIds)->update($attributes);
        });
        Invoice::findOrFail($invoiceId)->updateSums();
        return response()->json(['status' => 'ok']);
    }
}