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

    public function lookup(LookupRequest $request, $cid)
    {
        $details = Details::where('DETAIL_INHALT', 'LIKE', '%' . $cid . '%')->get();
        foreach ($details as $detail) {
            $owner = $detail->from;
            if ($owner) {
                switch (get_class($owner)) {
                    case Person::class:
                        return response(
                            mb_convert_encoding($owner->full_name, 'latin1')
                        )->header('Content-Type', 'text/plain; charset=ISO-8859-1');
                    case Partner::class:
                        return response(
                            mb_convert_encoding($owner->PARTNER_NAME, 'latin1')
                        )->header('Content-Type', 'text/plain; charset=ISO-8859-1');
                }
            }
        }
        return response('');
    }
}
