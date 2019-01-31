<?php


namespace App\Validators;


use App\GraphQL\Traits\InvoiceAttributes;
use App\Models\Invoice;
use Arr;
use Exception;

class EmptyIffNotAdvancePayment
{
    use InvoiceAttributes;

    public static function message()
    {
        return 'Value has to be empty if and only if invoiceType is not one of ADVANCE_PAYMENT_INVOICE or FINAL_ADVANCE_PAYMENT_INVOICE.';
    }

    public function validate($attribute, $value, $parameters, $validator)
    {
        try {
            $args = $validator->getData();
            $prefix = $this->prefix($attribute);
            $invoice = Invoice::where('BELEG_NR', $args['id'])->firstOrFail();
            $attributes = $this->extractAttributes($args, [
                $prefix . 'id',
                $prefix . 'invoiceType',
                $prefix . 'firstAdvancePaymentInvoiceId'
            ], [
                $prefix . 'invoiceType' => $invoice->RECHNUNGSTYP,
                $prefix . 'firstAdvancePaymentInvoiceId' => $invoice->advance_payment_invoice_id
            ]);

            $this->substituteInvoiceTypeValue($attributes);

            if (
                !empty(Arr::get($attributes, $prefix . 'firstAdvancePaymentInvoiceId'))
                && in_array(Arr::get($attributes, $prefix . 'invoiceType'), ['Teilrechnung', 'Schlussrechnung'])
            ) {
                return true;
            }

            if (
                empty(Arr::get($attributes, $prefix . 'firstAdvancePaymentInvoiceId'))
                && !in_array(Arr::get($attributes, $prefix . 'invoiceType'), ['Teilrechnung', 'Schlussrechnung'])
            ) {
                return true;
            }

            return false;
        } catch (Exception $e) {
            return false;
        }
    }
}
