<?php

namespace App\Http\Controllers\Api\v1\Modules;

use App;
use App\Http\Controllers\Controller;
use App\Models\Einheiten;
use App\Models\Objekte;
use App\Models\RentDefinition;
use Carbon\Carbon;
use Illuminate\Http\Request;
use XLSXWriter;

class ReportController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function revenue(Request $request, Objekte $object)
    {
        $file = tempnam(sys_get_temp_dir(), 'revenue_report_');
        $start = Carbon::today()->firstOfYear();
        $end = $start->copy()->lastOfYear();

        if ($request->filled('date')) {
            $start = Carbon::parse($request->input('date'));
            $end = Carbon::parse($request->input('date'));
        }
        if ($request->filled('period')) {
            $period = $request->input('period');
            switch ($period) {
                case 'year':
                    $start->firstOfYear();
                    $end->lastOfYear();
                    break;
                case 'quarter':
                    $start->firstOfQuarter();
                    $end->lastOfQuarter();
                    break;
                case 'month':
                    $start->firstOfMonth();
                    $end->lastOfMonth();
                    break;
            }
        }

        $header = [
            'Apartment No.' => 'string',
            'Space' => '0.00',
            'Occupant Name' => 'string',
            'Contract Start' => 'date',
            'Contract End' => 'date',
            'Days Occupied' => 'integer',
            'Basic Rent' => 'euro',
            'Operating Costs' => 'euro',
            'Heating Costs' => 'euro',
            'Actual Payments' => 'euro'
        ];

        $sheet = 'Revenue';

        $writer = new XLSXWriter();

        $writer->writeSheetHeader($sheet, $header);

        $units = Einheiten::whereHas('haus.objekt', function ($query) use ($object) {
            $query->where('OBJEKT_ID', $object->OBJEKT_ID);
        })->with(['haus.objekt', 'mietvertraege.mieter'])->defaultOrder()->get();
        $rows = 1;

        foreach ($units as $unit) {
            $rentalContracts = $unit->mietvertraege()
                ->where(function ($query) use ($start, $end) {
                    $query->where(function ($query) use ($start, $end) {
                        $query->active('>=', $start)
                            ->active('<=', $end);
                    })->orWhere(function ($query) use ($start, $end) {
                        $query->whereHas('postings', function ($query) use ($start, $end) {
                            $query->where('KONTENRAHMEN_KONTO', 80001)
                                ->where('KOSTENTRAEGER_TYP', 'Mietvertrag')
                                ->whereDate('DATUM', '>=', $start)
                                ->whereDate('DATUM', '<=', $end);
                        });
                    });
                })->defaultOrder()->get();
            if ($rentalContracts->isEmpty()) {
                $writer->writeSheetRow($sheet, [$unit->EINHEIT_KURZNAME, $unit->EINHEIT_QM, '', '', '', 0, 0, 0, 0, 0]);
                $rows++;
            } else {
                foreach ($rentalContracts as $index => $rentalContract) {
                    $basicRent = RentDefinition::sumDefinitions($rentalContract->basicRentDefinitions($start, $end), $start, $end);
                    $heatingCosts = RentDefinition::sumDefinitions($rentalContract->heatingExpenseDefinitions($start, $end), $start, $end);
                    $operatingCosts = RentDefinition::sumDefinitions($rentalContract->operatingCostDefinitions($start, $end), $start, $end);
                    $postings = $rentalContract->postings($start, $end)->where('KONTENRAHMEN_KONTO', 80001)->sum('BETRAG');
                    $occupantName = $rentalContract->mieter->implode('full_name', '; ');
                    $to = $rentalContract->MIETVERTRAG_BIS === '0000-00-00' ? '' : $rentalContract->MIETVERTRAG_BIS;
                    $row = ['', '', $occupantName, $rentalContract->MIETVERTRAG_VON, $to, $rentalContract->overlaps($start, $end), $basicRent, $operatingCosts, $heatingCosts, $postings];
                    if ($index === 0) {
                        $row[0] = $unit->EINHEIT_KURZNAME;
                        $row[1] = $unit->EINHEIT_QM;
                    }
                    $writer->writeSheetRow($sheet, $row);
                    $rows++;
                }
            }
        }
        $row = ['Total', "=SUM(B2:B$rows)", '', '', '', '', "=SUM(G2:G$rows)", "=SUM(H2:H$rows)", "=SUM(I2:I$rows)", "=SUM(J2:J$rows)"];
        $writer->writeSheetRow($sheet, $row);
        $writer->writeSheetRow($sheet, ['', '', '', '', '', '', '', '', '', '']);
        $writer->writeSheetRow($sheet, ['', '', 'Query Period:', $start->toDateString(), $end->toDateString(), '', '', '', '', '']);

        $writer->writeToFile($file);

        $filename = 'revenue_report_'
            . $object->OBJEKT_KURZNAME . '_'
            . $start->toDateString() . '_'
            . $end->toDateString() . '_created_at_'
            . Carbon::today()->toDateString();

        return response()->file($file, ['Content-Disposition' => 'inline; filename="' . $filename . '.xlsx'])->deleteFileAfterSend(true);
    }
}
