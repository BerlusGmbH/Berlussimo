<?php

namespace Tests\Unit;

use App\Models\Mietvertraege;
use App\Models\RentDefinition;
use Carbon\Carbon;
use Exception;
use mietkonto;
use Tests\TestCase;

class RentDefinitionTest extends TestCase
{
    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function testRentDefinition()
    {
        error_reporting(E_ERROR | E_WARNING | E_PARSE);
        $start = Carbon::today()->subMonths(24)->firstOfMonth();
        $end = $start->copy()->lastOfMonth();
        $rentalContracts = Mietvertraege::active('=', $end)->get();
        $exception = null;
        foreach ($rentalContracts as $rentalContract) {
            $basicRent = RentDefinition::sumDefinitions($rentalContract->basicRentDefinitions($start, $end), $start, $end);
            $basicRentDeduction = RentDefinition::sumDefinitions($rentalContract->basicRentDeductionDefinitions($start, $end), $start, $end);
            $heatingAdvanceCosts = RentDefinition::sumDefinitions($rentalContract->heatingExpenseAdvanceDefinitions($start, $end), $start, $end);
            $operatingAdvanceCosts = RentDefinition::sumDefinitions($rentalContract->operatingCostAdvanceDefinitions($start, $end), $start, $end);

            $outstanding = $basicRent
                + $basicRentDeduction
                + $heatingAdvanceCosts
                + $operatingAdvanceCosts;

            $buchung = new mietkonto();
            $outstandingPrevious = $buchung->summe_forderung_monatlich($rentalContract->MIETVERTRAG_ID, $end->format("m"), $end->format("Y"));
            $outstandingPrevious = floatval(explode('|', $outstandingPrevious)[0]);
            try {
                $this->assertEquals(
                    $outstandingPrevious,
                    $outstanding,
                    "Rental contract id: "
                    . $rentalContract->MIETVERTRAG_ID
                    . " unit: "
                    . $rentalContract->einheit->EINHEIT_KURZNAME
                    . " Date: "
                    . $end->toDateString()
                );
            } catch (Exception $e) {
                fwrite(STDERR, print_r($e->getMessage() . "\n", true));
                $exception = $e;
            }
        }
        if (!is_null($exception)) {
            throw $exception;
        }
    }
}
