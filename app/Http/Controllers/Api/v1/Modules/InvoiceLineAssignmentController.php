<?php

namespace App\Http\Controllers\Api\v1\Modules;


use App\Http\Controllers\Controller;
use App\Http\Requests\Legacy\RechnungenRequest;
use App\Models\InvoiceLine;
use App\Models\InvoiceLineAssignment;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Arr;

class InvoiceLineAssignmentController extends Controller
{
    /**
     * @param RechnungenRequest $request
     * @return mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function store(RechnungenRequest $request)
    {
        $line = InvoiceLine::where('BELEG_NR', $request->input('BELEG_NR'))
            ->where('POSITION', $request->input('POSITION'))
            ->first();
        $amount = $line->MENGE;
        $assignedAmount = $line->assignedAmount();

        $this->validate($request, [
            'MENGE' => 'numeric|max:' . ($amount - $assignedAmount)
        ]);

        $attributes = $request->only([
            'BELEG_NR',
            'POSITION',
            'MENGE',
            'EINZEL_PREIS',
            'MWST_SATZ',
            'RABATT_SATZ',
            'SKONTO',
            'KOSTENTRAEGER_TYP',
            'KOSTENTRAEGER_ID',
            'VERWENDUNGS_JAHR',
            'WEITER_VERWENDEN',
            'KONTENRAHMEN_KONTO'
        ]);

        $id = InvoiceLineAssignment::max('KONTIERUNG_ID') + 1;
        Arr::set($attributes, 'KONTIERUNG_ID', $id);
        Arr::set($attributes, 'AKTUELL', '1');
        Arr::set($attributes, 'GESAMT_SUMME', $attributes['MENGE'] * $attributes['EINZEL_PREIS']);
        Arr::set($attributes, 'KONTIERUNGS_DATUM', Carbon::now());
        $assignment = InvoiceLineAssignment::forceCreate($attributes);
        return response()->json($assignment);
    }

    /**
     * @param RechnungenRequest $request
     * @param InvoiceLineAssignment $invoiceLineAssignment
     * @return mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function update(RechnungenRequest $request, InvoiceLineAssignment $invoiceLineAssignment)
    {
        $line = $invoiceLineAssignment->line();
        $amount = $line->MENGE;
        $assignedAmount = $line->assignedAmount();

        $this->validate($request, [
            'MENGE' => 'numeric|max:' . ($amount - $assignedAmount + $invoiceLineAssignment->MENGE)
        ]);

        $attributes = $request->only([
            'MENGE',
            'KOSTENTRAEGER_TYP',
            'KOSTENTRAEGER_ID',
            'VERWENDUNGS_JAHR',
            'WEITER_VERWENDEN',
            'KONTENRAHMEN_KONTO'
        ]);

        $invoiceLineAssignment = InvoiceLineAssignment::unguarded(function () use ($attributes, $invoiceLineAssignment) {
            Arr::set($attributes, 'GESAMT_SUMME', DB::raw('MENGE * EINZEL_PREIS'));
            return $invoiceLineAssignment->update($attributes);
        });
        return response()->json($invoiceLineAssignment);
    }

    /**
     * @param RechnungenRequest $request
     * @param InvoiceLineAssignment $invoiceLineAssignment
     * @return mixed
     * @throws \Exception
     * @throws \Throwable
     */
    public function destroy(RechnungenRequest $request, InvoiceLineAssignment $invoiceLineAssignment)
    {
        $invoiceLineAssignment->delete();
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
            'KOSTENTRAEGER_TYP',
            'KOSTENTRAEGER_ID',
            'VERWENDUNGS_JAHR',
            'WEITER_VERWENDEN',
            'KONTENRAHMEN_KONTO'
        ];
        $attributes = collect($request->only($attributeNames))->reject(function ($name, $key) use ($request) {
            return !$request->filled($key);
        });
        $create = collect($attributeNames)->reduce(function ($carry, $name) use ($request) {
            return $carry === false ? $carry : $request->filled($name);
        });
        $lines = $request->input('lines');
        return DB::transaction(function () use ($attributes, $create, $lines) {
            foreach ($lines as $lineId) {
                $line = InvoiceLine::find($lineId);
                if ($line) {
                    if ($line->assignments()->exists()) {
                        $line->assignments()->update($attributes->all());
                    } else if ($create) {
                        Arr::set($attributes, 'BELEG_NR', $line->BELEG_NR);
                        Arr::set($attributes, 'POSITION', $line->POSITION);
                        Arr::set($attributes, 'MENGE', $line->MENGE);
                        Arr::set($attributes, 'EINZEL_PREIS', $line->PREIS);
                        Arr::set($attributes, 'MWST_SATZ', $line->MWST_SATZ);
                        Arr::set($attributes, 'RABATT_SATZ', $line->RABATT_SATZ);
                        Arr::set($attributes, 'SKONTO', $line->SKONTO);
                        $id = InvoiceLineAssignment::max('KONTIERUNG_ID') + 1;
                        Arr::set($attributes, 'KONTIERUNG_ID', $id);
                        Arr::set($attributes, 'AKTUELL', '1');
                        Arr::set($attributes, 'GESAMT_SUMME', $attributes['MENGE'] * $attributes['EINZEL_PREIS']);
                        Arr::set($attributes, 'KONTIERUNGS_DATUM', Carbon::now());
                        InvoiceLineAssignment::forceCreate($attributes->all());
                    }
                }
            }
            return response()->json(['status' => 'ok']);
        });
    }
}