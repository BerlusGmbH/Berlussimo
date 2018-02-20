<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\v1\CallRequest;
use App\Models\Details;
use App\Services\PhoneLocator;
use Exception;
use GuzzleHttp\Client;

class CallController extends Controller
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
}
