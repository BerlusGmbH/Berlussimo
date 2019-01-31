<?php


namespace App\Validators;


use App\GraphQL\Traits\InvoiceAttributes;
use App\Models\Invoice;

class HasMultipleFinalInvoices
{
    use InvoiceAttributes;

    public static function message()
    {
        return 'This invoice already has a final invoice.';
    }

    public function validate($attribute, $value, $parameters, $validator)
    {
        $args = $validator->getData();
        $prefix = $this->prefix($attribute);
        $id = $args[$prefix . 'id'];
        $invoice = Invoice::where('BELEG_NR', $id)->firstOrFail();
        $attributes = $this->extractAttributes($args, [
            $prefix . 'invoiceNumber',
            $prefix . 'invoiceType',
            $prefix . 'invoiceDate',
            $prefix . 'dateOfReceipt',
            $prefix . 'dueDate',
            $prefix . 'description',
            $prefix . 'issuerInvoiceNumber',
            $prefix . 'recipientInvoiceNumber',
            $prefix . 'firstAdvancePaymentInvoiceId',
            $prefix . 'serviceTimeStart',
            $prefix . 'serviceTimeEnd',
            $prefix . 'costForwarded'
        ], [
            $prefix . 'invoiceNumber' => $invoice->RECHNUNGSNUMMER,
            $prefix . 'invoiceType' => $invoice->RECHNUNGSTYP,
            $prefix . 'invoiceDate' => $invoice->RECHNUNGSDATUM,
            $prefix . 'dateOfReceipt' => $invoice->EINGANGSDATUM,
            $prefix . 'dueDate' => $invoice->FAELLIG_AM,
            $prefix . 'description' => $invoice->KURZBESCHREIBUNG,
            $prefix . 'issuerInvoiceNumber' => $invoice->AUSTELLER_AUSGANGS_RNR,
            $prefix . 'recipientInvoiceNumber' => $invoice->EMPFAENGER_EINGANGS_RNR,
            $prefix . 'firstAdvancePaymentInvoiceId' => $invoice->advance_payment_invoice_id,
            $prefix . 'serviceTimeStart' => $invoice->servicetime_from,
            $prefix . 'serviceTimeEnd' => $invoice->servicetime_to,
            $prefix . 'costForwarded' => $invoice->forwarded
        ]);
        if (!$this->isAdvancePaymentInvoice($attributes)) {
            return false;
        } elseif ($this->invoiceIsAssigned($attributes)) {
            $finalInvoices = $this->advancePaymentInvoices($attributes, $id)->where('RECHNUNGSTYP', 'Schlussrechnung')->get();
            if ($finalInvoices->count() > 1) {
                if ($finalInvoices->count() == 2
                    && !$this->isFinalInvoice($attributes)
                    && !$finalInvoices
                        ->where('BELEG_NR', $id)
                        ->isEmpty()
                ) {
                    return false;
                }
                return true;
            }
            if ($finalInvoices->count() == 1
                && $finalInvoices->first()->BELEG_NR != $id
                && $this->isFinalInvoice($attributes)
            ) {
                return true;
            }
        }
        return false;
    }
}
