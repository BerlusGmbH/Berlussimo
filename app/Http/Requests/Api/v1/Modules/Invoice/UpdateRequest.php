<?php

namespace App\Http\Requests\Api\v1\Modules\Invoice;


use App\Http\Requests\Legacy\RechnungenRequest;
use App\Models\Invoice;
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
            'advance_payment_invoice_id' => 'numeric|nullable',
            'servicetime_from' => 'date_format:Y-m-d|nullable',
            'servicetime_to' => 'date_format:Y-m-d|nullable'
        ];

        return $rules;
    }

    public function withValidator($validator)
    {
        if (!$this->isAdvancePaymentInvoice()) {
            $this->getInputSource()->set('advance_payment_invoice_id', null);
        }
        if (!$this->has('servicetime_from')) {
            $this->getInputSource()->set('servicetime_to', null);
        }
        $validator->after(function ($validator) {
            if ($this->hasMultipleFinalInvoices()) {
                $validator->errors()->add('multiple_final_invoices', 'Es sind mehrere Schlussrechnungen vorhanden.');
            }
        });
    }

    public function isAdvancePaymentInvoice()
    {
        return in_array($this->input('RECHNUNGSTYP'), ['Teilrechnung', 'Schlussrechnung']);
    }

    /**
     * @return bool
     */
    protected function hasMultipleFinalInvoices()
    {
        $invoice = request()->route()->parameter('invoice');
        if (!$this->isAdvancePaymentInvoice()) {
            return false;
        } elseif ($this->invoiceIsAssigned() || $this->invoiceWasAssigned()) {
            $finalInvoices = $this->advancePaymentInvoices()->where('RECHNUNGSTYP', 'Schlussrechnung')->get();
            if ($finalInvoices->count() > 1) {
                if ($finalInvoices->count() == 2
                    && !$this->isFinalInvoice()
                    && !$finalInvoices
                        ->where('BELEG_NR', $invoice->BELEG_NR)
                        ->isEmpty()
                ) {
                    return false;
                }
                return true;
            }
            if ($finalInvoices->count() == 1
                && $finalInvoices->first()->BELEG_NR != $invoice->BELEG_NR
                && $this->isFinalInvoice()
            ) {
                return true;
            }
        }
        return false;
    }

    public function invoiceIsAssigned()
    {
        return is_numeric($this->input('advance_payment_invoice_id'));
    }

    public function invoiceWasAssigned()
    {
        $invoice = request()->route()->parameter('invoice');
        return $invoice->advance_payment_invoice_id;
    }

    public function advancePaymentInvoices()
    {
        $invoice = request()->route()->parameter('invoice');
        $invoices = Invoice::where(function ($query) use ($invoice) {
            if ($invoice->advance_payment_invoice_id) {
                $query->where('advance_payment_invoice_id', $invoice->advance_payment_invoice_id);
            }
            if (is_numeric($this->input('advance_payment_invoice_id'))) {
                $query->orWhere('advance_payment_invoice_id', $this->input('advance_payment_invoice_id'));
            } else {
                $query->where('BELEG_NR', '<>', $invoice->BELEG_NR);
            }
        });
        return $invoices;
    }

    public function isFinalInvoice()
    {
        return $this->input('RECHNUNGSTYP') == 'Schlussrechnung';
    }
}