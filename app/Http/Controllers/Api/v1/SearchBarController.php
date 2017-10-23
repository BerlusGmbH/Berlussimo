<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Bankkonten;
use App\Models\BaustellenExtern;
use App\Models\Einheiten;
use App\Models\Haeuser;
use App\Models\Kaufvertraege;
use App\Models\Mietvertraege;
use App\Models\Objekte;
use App\Models\Partner;
use App\Models\Person;
use App\Models\Wirtschaftseinheiten;
use Response;

class SearchBarController extends Controller
{
    public function search()
    {
        if (!request()->has('e')) {
            $classes = [
                'objekt',
                'haus',
                'einheit',
                'person',
                'partner',
                'bankkonto',
                'mietvertrag',
                'kaufvertrag',
                'baustelle',
                'wirtschaftseinheit'
            ];
            $response = [
                'objekt' => [],
                'haus' => [],
                'einheit' => [],
                'person' => [],
                'partner' => [],
                'bankkonto' => [],
                'mietvertrag' => [],
                'kaufvertrag' => [],
                'baustelle' => [],
                'wirtschaftseinheit' => []
            ];
        } else {
            $response = [];
            if (is_array(request()->input('e'))) {
                $classes = request()->input('e');
            } else {
                $classes = [request()->input('e')];
            }
        }
        if (!request()->has('q')) {
            return Response::json($response);
        }
        $tokens = explode(' ', request()->input('q'));

        foreach ($classes as $class) {
            switch ($class) {
                case 'objekt':
                    $response['objekt'] = Objekte::defaultOrder();
                    break;
                case 'haus':
                    $response['haus'] = Haeuser::defaultOrder();
                    break;
                case 'einheit':
                    $response['einheit'] = Einheiten::defaultOrder();
                    break;
                case 'person':
                    $response['person'] = Person::defaultOrder();
                    break;
                case 'partner':
                    $response['partner'] = Partner::defaultOrder();
                    break;
                case 'bankkonto':
                    $response['bankkonto'] = Bankkonten::defaultOrder();
                    break;
                case 'mietvertrag':
                    $response['mietvertrag'] = Mietvertraege::defaultOrder();
                    break;
                case 'kaufvertrag':
                    $response['kaufvertrag'] = Kaufvertraege::defaultOrder();
                    break;
                case 'baustelle':
                    $response['baustelle'] = BaustellenExtern::defaultOrder();
                    break;
                case 'wirtschaftseinheit':
                    $response['wirtschaftseinheit'] = Wirtschaftseinheiten::defaultOrder();
                    break;
            }
        }

        foreach ($classes as $class) {
            $response[$class] = $response[$class]->search($tokens)->get();
        }

        return Response::json($response);
    }
}
