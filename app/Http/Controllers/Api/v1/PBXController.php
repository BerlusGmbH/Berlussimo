<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\PBX\CallRequest;
use App\Http\Requests\Api\v1\PBX\LookupRequest;
use App\Models\Details;
use App\Models\Partner;
use App\Models\Person;
use App\Services\PhoneLocator;
use Exception;
use GuzzleHttp\Client;

class PBXController extends Controller
{
    public function call(CallRequest $request, Details $detail, PhoneLocator $locator)
    {
        if ($locator->workplaceHasPhone()) {
            $client = new Client();
            try {
                $response = $client->get($locator->url($detail->DETAIL_INHALT));
                return response('', $response->getStatusCode(), $response->getHeaders());
            } catch (Exception $e) {
                return response($e->getMessage(), 500);
            }
        } else {
            return response()->setStatusCode(424)->json(['status' => 'error', 'message' => 'No Phone is known for your workplace.']);
        }
    }

    public function lookup(LookupRequest $request)
    {
        $cid = $request->input('cid');
        if (strncmp($cid, '00', 2) === 0) {
            $cid = mb_substr($cid, 4);
        }
        if (strncmp($cid, '+', 1) === 0) {
            $cid = mb_substr($cid, 3);
        }
        $cid = ltrim($cid, '0');
        $valid_chars = '([-|(|)| |/|]*)';
        $regexp = '.*' . $valid_chars;
        $digits = str_split($cid);
        foreach ($digits as $digit) {
            $regexp .= $digit . $valid_chars;
        }
        $regexp .= '.*';
        $details = Details::where('DETAIL_INHALT', 'REGEXP', $regexp)
            ->whereIn('DETAIL_ZUORDNUNG_TABELLE', ['Partner', 'Person'])
            ->get();
        foreach ($details as $detail) {
            if ($detail->from) {
                $cnam = '';
                switch (get_class($detail->from)) {
                    case Person::class:
                        $cnam = $detail->from->full_name;
                        $rentalContracts = $detail->from->mietvertraege()
                            ->join('EINHEIT', 'EINHEIT.id', '=', 'MIETVERTRAG.EINHEIT_ID')
                            ->orderByRaw('CASE '
                                . 'WHEN MIETVERTRAG.MIETVERTRAG_VON <= NOW() && (MIETVERTRAG.MIETVERTRAG_BIS >= NOW() || MIETVERTRAG.MIETVERTRAG_BIS = \'0000-00-00\') THEN 1 '
                                . 'ELSE 2 '
                                . 'END'
                            )->orderByRaw('CASE EINHEIT.TYP '
                                . 'WHEN "Gewerbe" THEN 1 '
                                . 'WHEN "Wohnraum" THEN 2 '
                                . 'WHEN "Wohneigentum" THEN 3 '
                                . 'WHEN "Zimmer (möbliert)" THEN 4 '
                                . 'WHEN "Garage" THEN 5 '
                                . 'WHEN "Stellplatz" THEN 6 '
                                . 'WHEN "Keller" THEN 7 '
                                . 'ELSE 8 '
                                . 'END'
                            )->defaultOrder()
                            ->get();
                        if (!$rentalContracts->isEmpty()) {
                            $cnam .= ' (';
                            $cnam .= $rentalContracts->first()->einheit->EINHEIT_KURZNAME;
                            if ($rentalContracts->count() > 1) {
                                $cnam .= ' +' . ($rentalContracts->count() - 1);
                            }
                            $cnam .= ')';
                        }
                        break;
                    case Partner::class:
                        $cnam = trim($detail->from->name_one_line);
                        break;
                }
                if ($detail->DETAIL_BEMERKUNG) {
                    $cnam .= ' [' . $detail->DETAIL_BEMERKUNG . ']';
                }
                return response(
                    $cnam
                );
            }
        }
        return response('');
    }
}
