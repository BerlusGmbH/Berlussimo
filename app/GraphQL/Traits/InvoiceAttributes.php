<?php


namespace App\GraphQL\Traits;


use App\Models\Invoice;

trait InvoiceAttributes
{
    public function invoiceIsAssigned($attributes)
    {
        return is_numeric($attributes['advance_payment_invoice_id']);
    }

    public function advancePaymentInvoices($attributes, $id)
    {
        $invoice = Invoice::where('BELEG_NR', $id)->firstOrFail();
        $invoices = Invoice::where(function ($query) use ($attributes, $invoice) {
            if ($invoice->advance_payment_invoice_id) {
                $query->where('advance_payment_invoice_id', $invoice->advance_payment_invoice_id);
            }
            if (is_numeric($attributes['advance_payment_invoice_id'])) {
                $query->orWhere('advance_payment_invoice_id', $attributes['advance_payment_invoice_id']);
            } else {
                $query->where('BELEG_NR', '<>', $invoice->BELEG_NR);
            }
        });
        return $invoices;
    }

    public function isAdvancePaymentInvoice($attributes)
    {
        return in_array($attributes['invoiceType'], ['ADVANCE_PAYMENT_INVOICE', 'FINAL_ADVANCE_PAYMENT_INVOICE']);
    }

    public function isFinalInvoice($attributes)
    {
        return $attributes['invoiceType'] == 'FINAL_ADVANCE_PAYMENT_INVOICE';
    }

    protected function extractAttributes($args, $attributeNames, $defaults = [])
    {
        $attributes = [];
        foreach ($attributeNames as $name) {
            if (key_exists($name, $args)) {
                $attributes[$name] = $args[$name];
            }
        }
        foreach ($defaults as $key => $value) {
            if (!key_exists($key, $attributes)) {
                $attributes[$key] = $value;
            }
        }
        return $attributes;
    }

    protected function translateAttributes($attributes, $translations)
    {
        $attributesCopy = [];
        foreach ($translations as $new => $old) {
            if (key_exists($old, $attributes)) {
                $attributesCopy[$new] = $attributes[$old];
            }
        }
        return $attributesCopy;
    }

    protected function substituteCostForwardedValue(&$attributes)
    {
        if (isset($attributes['costForwarded'])) {
            $attributes['costForwarded'] = mb_strtolower($attributes['costForwarded'], 'UTF-8');
        }
    }

    protected function substituteInvoiceTypeValue(&$attributes)
    {
        if (isset($attributes['invoiceType'])) {
            switch ($attributes['invoiceType']) {
                case "INVOICE":
                    $attributes['invoiceType'] = "Rechnung";
                    break;
                case "REVERSAL_INVOICE":
                    $attributes['invoiceType'] = "Stornorechnung";
                    break;
                case "CREDIT_VOUCHER":
                    $attributes['invoiceType'] = "Gutschrift";
                    break;
                case "CASH_RECEIPT":
                    $attributes['invoiceType'] = "Kassenbeleg";
                    break;
                case "ACCOUNTING_RECEIPT":
                    $attributes['invoiceType'] = "Buchungsbeleg";
                    break;
                case "OFFER":
                    $attributes['invoiceType'] = "Angebot";
                    break;
                case "ADVANCE_PAYMENT_INVOICE":
                    $attributes['invoiceType'] = "Teilrechnung";
                    break;
                case "FINAL_ADVANCE_PAYMENT_INVOICE":
                    $attributes['invoiceType'] = "Schlussrechnung";
                    break;
            }
        }
    }

    protected function prefix($attribute)
    {
        $path = explode('.', $attribute);
        if (count($path) > 1) {
            $prefix = array_slice($path, 0, -1);
            $prefix = implode('.', $prefix);
            $prefix .= '.';
            return $prefix;
        }
        return '';
    }
}
