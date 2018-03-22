<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\BankAccountStandardChart;
use App\Models\Bankkonten;
use App\Models\BaustellenExtern;
use App\Models\BookingAccount;
use App\Models\Einheiten;
use App\Models\Haeuser;
use App\Models\InvoiceItem;
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
                'wirtschaftseinheit',
                'artikel',
                'kontenrahmen',
                'buchungskonto'
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
                'wirtschaftseinheit' => [],
                'artikel' => [],
                'kontenrahmen' => [],
                'buchungskonto' => []
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
            $parts = explode(':', $class);
            switch ($parts[0]) {
                case 'objekt':
                    $response['objekt'] = Objekte::defaultOrder();
                    break;
                case 'haus':
                    $response['haus'] = Haeuser::defaultOrder()->with('objekt');
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
                case 'artikel':
                    $response['artikel'] = InvoiceItem::defaultOrder();
                    $response['artikel']->with('supplier');
                    if (isset($parts[1])) {
                        $response['artikel']->where('ART_LIEFERANT', $parts[1]);
                    }
                    break;
                case 'kontenrahmen':
                    $response['kontenrahmen'] = BankAccountStandardChart::defaultOrder();
                    break;
                case 'buchungskonto':
                    $response['buchungskonto'] = BookingAccount::defaultOrder();
                    if (isset($parts[1])) {
                        $response['buchungskonto']->where('KONTENRAHMEN_ID', $parts[1]);
                    }
                    break;
            }
            $response[$parts[0]] = $response[$parts[0]]->search($tokens)->get();
        }

        return Response::json($response);
    }
}
