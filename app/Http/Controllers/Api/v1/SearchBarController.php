<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Objekte;
use App\Models\Haeuser;
use App\Models\Einheiten;
use App\Models\Partner;
use App\Models\Personen;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Response;

class SearchBarController extends Controller
{
    public function search() {
        $response = ['objekte' => [], 'haeuser' => [], 'einheiten' => [], 'partner' => [], 'personen' => []];
        if (!request()->has('q')) {
            return Response::json($response);
        }
        $query = request()->input('q');
        $response['objekte'] = Objekte::search($query);
        $response['haeuser'] = Haeuser::search($query);
        $response['einheiten'] = Einheiten::search($query);
        $response['partner'] = Partner::search($query);
        $response['personen'] = Personen::search($query);
        return Response::json($response);
    }
}
