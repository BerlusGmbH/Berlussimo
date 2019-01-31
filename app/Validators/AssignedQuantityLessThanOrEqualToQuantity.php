<?php


namespace App\Validators;


use App\Models\InvoiceLine;
use App\Models\InvoiceLineAssignment;
use Arr;
use Exception;

class AssignedQuantityLessThanOrEqualToQuantity
{
    public static function message()
    {
        return 'The assigned quantities are greater than the quantity. Increase the quantity or reduce the assigned quantities.';
    }

    public function validate($attribute, $value, $parameters, $validator)
    {
        try {
            $args = $validator->getData();

            $currentQuantity = 0;

            if (Arr::has($args, 'input.id')) {
                $invoiceLineAssignment = InvoiceLineAssignment::where('KONTIERUNG_ID', Arr::get($args, 'input.id'))
                    ->firstOrFail();
                $currentQuantity = $invoiceLineAssignment->MENGE;
                $invoiceLine = InvoiceLine::where('POSITION', $invoiceLineAssignment->POSITION)
                    ->where('BELEG_NR', $invoiceLineAssignment->BELEG_NR)
                    ->firstOrFail();
            }

            if (Arr::has($args, 'input.lineId')) {
                $invoiceLine = InvoiceLine::where('RECHNUNGEN_POS_ID', Arr::get($args, 'input.lineId'))
                    ->firstOrFail();
            }

            $assignedQuantity = $invoiceLine->assignedAmount();
            $newQuantity = $value;
            $quantity = $invoiceLine->MENGE;
            return $assignedQuantity - $currentQuantity + $newQuantity <= $quantity;
        } catch (Exception $e) {
            return false;
        }
    }
}
