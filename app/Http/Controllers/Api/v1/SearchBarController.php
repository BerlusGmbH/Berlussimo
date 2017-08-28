<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Bankkonten;
use App\Models\Einheiten;
use App\Models\Haeuser;
use App\Models\Objekte;
use App\Models\Partner;
use App\Models\Person;
use Response;

class SearchBarController extends Controller
{
    public function search()
    {
        if (!request()->has('e')) {
            $classes = ['objekt', 'haus', 'einheit', 'person', 'partner', 'bankkonto'];
            $response = [
                'objekt' => [],
                'haus' => [],
                'einheit' => [],
                'person' => [],
                'partner' => [],
                'bankkonto' => []];
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
            }
        }

        foreach ($classes as $class) {
            foreach ($tokens as $token) {
                $response[$class] = $response[$class]->search($token);
            }
            $response[$class] = $response[$class]->get();
        }

        return Response::json($response);
    }
}
