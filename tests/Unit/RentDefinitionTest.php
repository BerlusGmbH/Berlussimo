<?php

namespace Tests\Unit;

use App\Models\Mietvertraege;
use App\Models\RentDefinition;
use Carbon\Carbon;
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
        $start = Carbon::today()->subMonths(18)->firstOfMonth();
        $end = $start->copy()->lastOfMonth();
        $rentalContracts = Mietvertraege::active('=', $end)->get();
        foreach ($rentalContracts as $rentalContract) {
            $basicRent = RentDefinition::sumDefinitions($rentalContract->basicRentDefinitions($start, $end), $start, $end);
            $heatingCosts = RentDefinition::sumDefinitions($rentalContract->heatingExpenseAdvanceDefinitions($start, $end), $start, $end);
            $operatingCosts = RentDefinition::sumDefinitions($rentalContract->operatingCostAdvanceDefinitions($start, $end), $start, $end);

            $outstanding = $basicRent + $heatingCosts + $operatingCosts;

            $buchung = new mietkonto();
            $outstandingPrevious = $buchung->summe_forderung_monatlich($rentalContract->MIETVERTRAG_ID, $end->format("m"), $end->format("Y"));
            $outstandingPrevious = floatval(explode('|', $outstandingPrevious)[0]);
            $this->assertEquals($outstandingPrevious, $outstanding, "Rental contract id: " . $rentalContract->MIETVERTRAG_ID . " Date: " . $end->toDateString());
        }
    }
}
