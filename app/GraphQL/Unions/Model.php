<?php

namespace App\GraphQL\Unions;


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

class Model
{
    public function resolveType($value)
    {
        $class = get_class($value);
        switch ($class) {
            case Objekte::class:
                return "Property";
            case Haeuser::class:
                return "House";
            case Einheiten::class:
                return "Unit";
            case Person::class:
                return "Person";
            case Partner::class:
                return "Partner";
            case Bankkonten::class:
                return "BankAccount";
            case Mietvertraege::class:
                return "RentalContract";
            case Kaufvertraege::class:
                return "PurchaseContract";
            case BaustellenExtern::class:
                return "ConstructionSite";
            case Wirtschaftseinheiten::class:
                return "AccountingEntity";
            case InvoiceItem::class:
                return "InvoiceItem";
            case Invoice::class:
                return "Invoice";
            case BankAccountStandardChart::class:
                return "BankAccountStandardChart";
            case BookingAccount::class:
                return "BookingAccount";
        }
        return class_basename($class);
    }
}