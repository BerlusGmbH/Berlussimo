<?php

namespace App\GraphQL\Queries;

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
use Illuminate\Database\Eloquent\Relations\Relation;

class Assignments
{
    /* Limit result to assignments matching $value
    *
    * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $builder
    * @param  mixed  $value
    * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
    */
    public function search($builder, $value)
    {
        if (!is_null($value)) {
            $builder->search($value);
        }
        return $builder;
    }

    /* Limit result to assignments matching $value
    *
    * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $builder
    * @param  mixed  $value
    * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
    */
    public function assignedTo($builder, $values)
    {
        if (!is_array($values)) {
            return $builder;
        }
        $builder->where(function ($query) use ($values) {
            foreach ($values as $value) {
                $type = '';
                switch ($value['type']) {
                    case 'Partner':
                        $type = 'Partner';
                        break;
                    case 'Person':
                        $type = 'Person';
                        break;
                }
                if (!empty($type)) {
                    $query->orWhere(function ($query) use ($value, $type) {
                        $query->where('BENUTZER_TYP', $type)
                            ->where('BENUTZER_ID', $value['id']);
                    });
                }
            }
        });
        return $builder;
    }

    /* Limit result to assignments matching $value
    *
    * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $builder
    * @param  mixed  $value
    * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
    */
    public function author($builder, $values)
    {
        if (!is_array($values)) {
            return $builder;
        }
        $builder->where(function ($query) use ($values) {
            foreach ($values as $value) {
                if ($value['type'] === 'Person') {
                    $query->orWhere(function ($query) use ($value) {
                        $query->where('VERFASSER_ID', $value['id']);
                    });
                }
            }
        });
        return $builder;
    }

    /* Limit result to assignments matching $value
    *
    * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $builder
    * @param  mixed  $value
    * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
    */
    public function costBearer($builder, $values)
    {
        if (!is_array($values)) {
            return $builder;
        }
        $builder->where(function ($query) use ($values) {
            foreach ($values as $value) {
                $type = array_search($this->typeToClass($value['type']), Relation::morphMap());
                if ($type !== false) {
                    $query->orWhere(function ($query) use ($value, $type) {
                        $query->where('KOS_TYP', $type)
                            ->where('KOS_ID', $value['id']);
                    });
                }
            }
        });
        return $builder;
    }

    /* Limit result to assignments matching $value
    *
    * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $builder
    * @param  mixed  $value
    * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
    */
    public function highPriority($builder, $value)
    {
        $builder->where('AKUT', $value ? 'JA' : 'NEIN');
        return $builder;
    }

    /* Limit result to assignments matching $value
    *
    * @param  \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder  $builder
    * @param  mixed  $value
    * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
    */
    public function done($builder, $value)
    {
        $builder->where('ERLEDIGT', $value ? '1' : '0');
        return $builder;
    }

    private function typeToClass($type)
    {
        switch ($type) {
            case "Property":
                return Objekte::class;
            case "House":
                return Haeuser::class;
            case "Unit":
                return Einheiten::class;
            case "Person":
                return Person::class;
            case "Partner":
                return Partner::class;
            case "BankAccount":
                return Bankkonten::class;
            case "RentalContract":
                return Mietvertraege::class;
            case "PurchaseContract":
                return Kaufvertraege::class;
            case "ConstructionSite":
                return BaustellenExtern::class;
            case "AccountingEntity":
                return Wirtschaftseinheiten::class;
            case "InvoiceItem":
                return InvoiceItem::class;
            case "Invoice":
                return Invoice::class;
            case "BankAccountStandardChart":
                return BankAccountStandardChart::class;
            case "BookingAccount":
                return BookingAccount::class;
        }
        return $type;
    }
}
