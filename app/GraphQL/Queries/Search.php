<?php

namespace App\GraphQL\Queries;

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
use Arr;
use Auth;
use Exception;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Search
{
    /**
     * Return a value for the field.
     *
     * @param null $rootValue Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param mixed[] $args The arguments that were passed into the field.
     * @param \Nuwave\Lighthouse\Support\Contracts\GraphQLContext $context Arbitrary data that is shared between all fields of a single query.
     * @param \GraphQL\Type\Definition\ResolveInfo $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return mixed
     * @throws Exception
     */
    public function resolve($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        if (is_null($args['query'])) {
            return [];
        }
        if (is_null($args['entities']) || empty($args['entities'])) {
            $classes = [
                'Property',
                'House',
                'Unit',
                'Person',
                'Partner',
                'BankAccount',
                'RentalContract',
                'PurchaseContract',
                'ConstructionSite',
                'AccountingEntity',
                'InvoiceItem',
                'BookingAccountStandardChart',
                'BookingAccount',
                'Invoice'
            ];
        } else {
            $classes = $args['entities'];
        }

        $tokens = explode(' ', $args['query']);
        $response = collect();

        foreach ($classes as $class) {
            $request = null;
            switch ($class) {
                case 'Property':
                    if (Auth::user()->can(Permission::PERMISSION_MODUL_OBJEKT)) {
                        $request = Objekte::defaultOrder();
                    }
                    break;
                case 'House':
                    if (Auth::user()->can(Permission::PERMISSION_MODUL_HAUS)) {
                        $request = Haeuser::defaultOrder()->with('objekt');
                    }
                    break;
                case 'Unit':
                    if (Auth::user()->can(Permission::PERMISSION_MODUL_EINHEIT)) {
                        $request = Einheiten::query();
                        if (key_exists('unitForRent', $args) && $args['unitForRent']) {
                            $request->where(function ($query) {
                                $query->whereDoesntHave('mietvertraege', function ($query) {
                                    $query->active();
                                })->orWhereHas('mietvertraege', function ($query) {
                                    $query->active()
                                        ->where('MIETVERTRAG_BIS', '<>', '0000-00-00');
                                });
                            })->where(function ($query) {
                                $query->whereHas('details', function ($query) {
                                    $query->where('DETAIL_NAME', 'Fertigstellung in Prozent')
                                        ->where('DETAIL_INHALT', '>', 99);
                                });
                            });
                        }
                        $request->defaultOrder();
                    }
                    break;
                case 'Person':
                    if (Auth::user()->can(Permission::PERMISSION_MODUL_PERSON)) {
                        $request = Person::defaultOrder();
                    }
                    break;
                case 'Partner':
                    $request = Partner::defaultOrder();
                    break;
                case 'BankAccount':
                    if (Auth::user()->can(Permission::PERMISSION_MODUL_BANKKONTO)) {
                        $request = Bankkonten::defaultOrder();
                    }
                    break;
                case 'RentalContract':
                    if (Auth::user()->can(Permission::PERMISSION_MODUL_MIETVERTRAG)) {
                        $request = Mietvertraege::defaultOrder();
                    }
                    break;
                case 'PurchaseContract':
                    if (Auth::user()->hasAnyPermission([
                        Permission::PERMISSION_MODUL_WEG,
                        Permission::PERMISSION_MODUL_AUFTRAEGE
                    ])) {
                        $request = Kaufvertraege::defaultOrder();
                    }
                    break;
                case 'ConstructionSite':
                    if (Auth::user()->hasAnyPermission([
                        Permission::PERMISSION_MODUL_STATISTIK,
                        Permission::PERMISSION_MODUL_AUFTRAEGE
                    ])) {
                        $request = BaustellenExtern::defaultOrder();
                    }
                    break;
                case 'AccountingEntity':
                    if (Auth::user()->hasAnyPermission([
                        Permission::PERMISSION_MODUL_BETRIEBSKOSTEN,
                        Permission::PERMISSION_MODUL_AUFTRAEGE
                    ])) {
                        $request = Wirtschaftseinheiten::defaultOrder();
                    }
                    break;
                case 'InvoiceItem':
                    if (Auth::user()->can(Permission::PERMISSION_MODUL_RECHNUNG)) {
                        $request = InvoiceItem::defaultOrder();
                        $request->with('supplier');
                        if (isset($args['invoiceItemsFrom'])) {
                            $request->where('ART_LIEFERANT', $args['invoiceItemsFrom']);
                        }
                    }
                    break;
                case 'BankAccountStandardChart':
                    if (Auth::user()->can(Permission::PERMISSION_MODUL_KONTENRAHMEN)) {
                        $request = BankAccountStandardChart::defaultOrder();
                    }
                    break;
                case 'BookingAccount':
                    if (Auth::user()->can(Permission::PERMISSION_MODUL_BUCHEN)) {
                        $request = BookingAccount::defaultOrder();
                        if (isset($args['bookingAccountIn'])) {
                            $request->where('KONTENRAHMEN_ID', $args['bookingAccountIn']);
                        }
                    }
                    break;
                case 'Invoice':
                    if (Auth::user()->can(Permission::PERMISSION_MODUL_RECHNUNG)) {
                        if (Arr::has($args, 'advancePayment')) {
                            $request = Invoice::orderBy('RECHNUNGSDATUM', 'asc')
                                ->whereColumn('BELEG_NR', 'advance_payment_invoice_id')
                                ->where('RECHNUNGSTYP', 'Teilrechnung')
                                ->where('AUSSTELLER_TYP', 'Partner')
                                ->where('AUSSTELLER_ID', Arr::get($args, 'advancePayment.issuer'))
                                ->where('EMPFAENGER_TYP', 'Partner')
                                ->where('EMPFAENGER_ID', Arr::get($args, 'advancePayment.recipient'))
                                ->whereDate('RECHNUNGSDATUM', '<=', Arr::get($args, 'advancePayment.before'))
                                ->has('finalAdvancePaymentInvoice', 0);
                        }
                    }
                    break;
                case 'Employer':
                    if (Auth::user()->can(Permission::PERMISSION_MODUL_PARTNER)) {
                        $request = Partner::defaultOrder()->has('arbeitnehmer');
                    }
                    break;
            }
            if ($request !== null) {
                $response = $response->concat($request->search($tokens)->get());
            }
        }

        return $response->all();
    }
}
