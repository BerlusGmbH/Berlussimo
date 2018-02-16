<?php

namespace App\Factories;

use App\Http\Responses\LegacyResponse;
use \Illuminate\Routing\ResponseFactory as BaseResponseFactory;

class ResponseFactory extends BaseResponseFactory
{
    public function legacy($include, $status = '200', $headers = []) {
        return new LegacyResponse($include, $status, $headers);
    }
}