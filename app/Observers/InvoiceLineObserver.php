<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\InvoiceLine;

class InvoiceLineObserver
{
    public function created(InvoiceLine $invoiceLine)
    {
        $invoice = Invoice::where('BELEG_NR', $invoiceLine->BELEG_NR)->firstOrFail();
        if ($invoice) {
            $invoice->updateSums();
        }
    }

    public function updated(InvoiceLine $invoiceLine)
    {
        $invoice = Invoice::where('BELEG_NR', $invoiceLine->BELEG_NR)->firstOrFail();
        if ($invoice) {
            $invoice->updateSums();
        }
    }

    public function deleted(InvoiceLine $invoiceLine)
    {
        $invoice = Invoice::where('BELEG_NR', $invoiceLine->BELEG_NR)->firstOrFail();
        if ($invoice) {
            $invoice->updateSums();
        }
    }
}
