<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Libraries\Permission;
use App\Models\BankAccountStandardChart;
use App\Models\Bankkonten;
use App\Models\BaustellenExtern;
use App\Models\BookingAccount;
use App\Models\Einheiten;
use App\Models\Haeuser;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Kaufvertraege;
use App\Models\Mietvertraege;
use App\Models\Objekte;
use App\Models\Partner;
use App\Models\Person;
use App\Models\Wirtschaftseinheiten;
use Auth;
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
                'buchungskonto',
                'teilrechnung'
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
                'buchungskonto' => [],
                'teilrechnung' => []
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
                    if (Auth::user()->can(Permission::PERMISSION_MODUL_OBJEKT)) {
                        $response['objekt'] = Objekte::defaultOrder();
                    }
                    break;
                case 'haus':
                    if (Auth::user()->can(Permission::PERMISSION_MODUL_HAUS)) {
                        $response['haus'] = Haeuser::defaultOrder()->with('objekt');
                    }
                    break;
                case 'einheit':
                    if (Auth::user()->can(Permission::PERMISSION_MODUL_EINHEIT)) {
                        $response['einheit'] = Einheiten::defaultOrder();
                    }
                    break;
                case 'person':
                    if (Auth::user()->can(Permission::PERMISSION_MODUL_PERSON)) {
                        $response['person'] = Person::defaultOrder();
                    }
                    break;
                case 'partner':
                    $response['partner'] = Partner::defaultOrder();
                    break;
                case 'bankkonto':
                    if (Auth::user()->can(Permission::PERMISSION_MODUL_BANKKONTO)) {
                        $response['bankkonto'] = Bankkonten::defaultOrder();
                    }
                    break;
                case 'mietvertrag':
                    if (Auth::user()->can(Permission::PERMISSION_MODUL_MIETVERTRAG)) {
                        $response['mietvertrag'] = Mietvertraege::defaultOrder();
                    }
                    break;
                case 'kaufvertrag':
                    if (Auth::user()->hasAnyPermission([
                        Permission::PERMISSION_MODUL_WEG,
                        Permission::PERMISSION_MODUL_AUFTRAEGE
                    ])) {
                        $response['kaufvertrag'] = Kaufvertraege::defaultOrder();
                    }
                    break;
                case 'baustelle':
                    if (Auth::user()->hasAnyPermission([
                        Permission::PERMISSION_MODUL_STATISTIK,
                        Permission::PERMISSION_MODUL_AUFTRAEGE
                    ])) {
                        $response['baustelle'] = BaustellenExtern::defaultOrder();
                    }
                    break;
                case 'wirtschaftseinheit':
                    if (Auth::user()->hasAnyPermission([
                        Permission::PERMISSION_MODUL_BETRIEBSKOSTEN,
                        Permission::PERMISSION_MODUL_AUFTRAEGE
                    ])) {
                        $response['wirtschaftseinheit'] = Wirtschaftseinheiten::defaultOrder();
                    }
                    break;
                case 'artikel':
                    if (Auth::user()->can(Permission::PERMISSION_MODUL_RECHNUNG)) {
                        $response['artikel'] = InvoiceItem::defaultOrder();
                        $response['artikel']->with('supplier');
                        if (isset($parts[1])) {
                            $response['artikel']->where('ART_LIEFERANT', $parts[1]);
                        }
                    }
                    break;
                case 'kontenrahmen':
                    if (Auth::user()->can(Permission::PERMISSION_MODUL_KONTENRAHMEN)) {
                        $response['kontenrahmen'] = BankAccountStandardChart::defaultOrder();
                    }
                    break;
                case 'buchungskonto':
                    if (Auth::user()->can(Permission::PERMISSION_MODUL_BUCHEN)) {
                        $response['buchungskonto'] = BookingAccount::defaultOrder();
                        if (isset($parts[1])) {
                            $response['buchungskonto']->where('KONTENRAHMEN_ID', $parts[1]);
                        }
                    }
                    break;
                case 'teilrechnung':
                    if (Auth::user()->can(Permission::PERMISSION_MODUL_RECHNUNG)) {
                        $response['teilrechnung'] = Invoice::orderBy('RECHNUNGSDATUM', 'asc')
                            ->whereColumn('BELEG_NR', 'advance_payment_invoice_id')
                            ->where('RECHNUNGSTYP', 'Teilrechnung');
                        if (isset($parts[1])) {
                            $response['teilrechnung']->where('AUSSTELLER_TYP', 'Partner')
                                ->where('AUSSTELLER_ID', $parts[1]);
                        }
                        if (isset($parts[2])) {
                            $response['teilrechnung']->where('EMPFAENGER_TYP', 'Partner')
                                ->where('EMPFAENGER_ID', $parts[2]);
                        }
                        if (isset($parts[3])) {
                            $response['teilrechnung']
                                ->whereDate('RECHNUNGSDATUM', '<=', $parts[3])
                                ->has('finalAdvancePaymentInvoice', 0);
                        }
                    }
                    break;
            }
            if (array_key_exists($parts[0], $response)) {
                $response[$parts[0]] = $response[$parts[0]]->search($tokens)->get();
            }
        }

        return Response::json($response);
    }
}
