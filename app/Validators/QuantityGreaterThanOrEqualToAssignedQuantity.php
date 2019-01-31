<?php


namespace App\Validators;


use App\Models\InvoiceLine;
use Exception;

class QuantityGreaterThanOrEqualToAssignedQuantity
{
    public static function message()
    {
        return 'The quantity is less than the assigned quantities. Increase the quantity or reduce the assigned quantities.';
    }

    public function validate($attribute, $value, $parameters, $validator)
    {
        try {
            $args = $validator->getData();
            $invoiceLine = InvoiceLine::where('RECHNUNGEN_POS_ID', $args['id'])->firstOrFail();
            $assignedAmount = $invoiceLine->assignedAmount();
            return $assignedAmount <= $value;
        } catch (Exception $e) {
            return false;
        }
    }
}
