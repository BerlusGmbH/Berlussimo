<?php

namespace App\Models;

use App\Models\Contracts\Active as ActiveContract;
use App\Models\Traits\Active;
use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Searchable;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Mietvertraege extends Model implements ActiveContract
{
    use Searchable {
        Searchable::scopeSearch as scopeSearchFromTrait;
    }
    use DefaultOrder;
    use Active;

    public $timestamps = false;
    protected $table = 'MIETVERTRAG';
    protected $primaryKey = 'MIETVERTRAG_ID';
    protected $searchableFields = ['MIETVERTRAG_ID', 'MIETVERTRAG_VON', 'MIETVERTRAG_BIS'];
    protected $defaultOrder = ['MIETVERTRAG_VON' => 'desc', 'MIETVERTRAG_BIS' => 'desc'];
    protected $appends = ['type'];

    static public function getTypeAttribute()
    {
        return 'rental_contract';
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('aktuell', function (Builder $builder) {
            $builder->where('MIETVERTRAG_AKTUELL', '1');
        });
    }

    public function einheit()
    {
        return $this->belongsTo(Einheiten::class, 'EINHEIT_ID', 'EINHEIT_ID');
    }

    public function mieter()
    {
        return $this->belongsToMany(
            Person::class,
            'PERSON_MIETVERTRAG',
            'PERSON_MIETVERTRAG_MIETVERTRAG_ID',
            'PERSON_MIETVERTRAG_PERSON_ID'
        )->wherePivot('PERSON_MIETVERTRAG_AKTUELL', '1');
    }

    public function SEPAMandates()
    {
        return $this->morphMany(SEPAMandate::class, 'SEPAMAndates', 'M_KOS_TYP', 'M_KOS_ID');
    }

    public function scopeSearch($query, $tokens)
    {
        $query->with(['einheit', 'mieter'])->orWhere(function ($query) use ($tokens) {
            $query->searchFromTrait($tokens);
        })->orWhereHas('einheit', function ($query) use ($tokens) {
            $query->search($tokens);
        })->orWhereHas('mieter', function ($query) use ($tokens) {
            $query->search($tokens);
        });
        return $query;
    }

    public function basicRentDefinitions($from = null, $to = null)
    {
        return $this->rentDefinitions($from, $to)
            ->where(function ($query) {
                $query->where('KOSTENKATEGORIE', '=', 'Miete kalt')
                    ->orWhere('KOSTENKATEGORIE', '=', 'MHG')
                    ->orWhere('KOSTENKATEGORIE', '=', 'MOD')
                    ->orWhere('KOSTENKATEGORIE', '=', 'Stellplatzmiete')
                    ->orWhere('KOSTENKATEGORIE', '=', 'Garagenmiete')
                    ->orWhere('KOSTENKATEGORIE', '=', 'Untermieter Zuschlag');
            });
    }

    public function rentDefinitions($from = null, $to = null)
    {
        if (is_string($from)) {
            $from = Carbon::parse($from);
        }
        if (is_string($to)) {
            $to = Carbon::parse($to);
        }
        $rentDefinitions = $this->morphMany(
            RentDefinition::class,
            'rentDefinitions',
            'KOSTENTRAEGER_TYP',
            'KOSTENTRAEGER_ID'
        );
        if ($from) {
            $rentDefinitions->whereDate('ANFANG', '<=', $to);
        }
        if ($to) {
            $rentDefinitions->where(function ($query) use ($from) {
                $query->whereDate('ENDE', '>=', $from)
                    ->orWhere('ENDE', '0000-00-00');
            });
        }
        return $rentDefinitions;
    }

    public function basicRentDeductionDefinitions($from = null, $to = null)
    {
        return $this->rentDefinitions($from, $to)
            ->where(function ($query) {
                $query->where('KOSTENKATEGORIE', '=', 'Mietminderung');
            });
    }

    public function heatingExpenseAdvanceDefinitions($from = null, $to = null)
    {
        return $this->rentDefinitions($from, $to)
            ->where(function ($query) {
                $query->where('KOSTENKATEGORIE', '=', 'Heizkosten Vorauszahlung');
            });
    }

    public function heatingExpenseSettlementDefinitions($from = null, $to = null)
    {
        return $this->rentDefinitions($from, $to)
            ->where(function ($query) {
                $query->where('KOSTENKATEGORIE', 'LIKE', 'Heizkostenabrechnung%');
            });
    }

    public function operatingCostAdvanceDefinitions($from = null, $to = null)
    {
        return $this->rentDefinitions($from, $to)
            ->where(function ($query) {
                $query->where('KOSTENKATEGORIE', '=', 'Kabel TV')
                    ->orWhere('KOSTENKATEGORIE', '=', 'Nebenkosten Vorauszahlung');
            });
    }

    public function operatingCostSettlementDefinitions($from = null, $to = null)
    {
        return $this->rentDefinitions($from, $to)
            ->where(function ($query) {
                $query->where('KOSTENKATEGORIE', 'LIKE', 'Betriebskostenabrechnung%')
                    ->orWhere('KOSTENKATEGORIE', 'LIKE', 'Kaltwasserabrechnung%')
                    ->orWhere('KOSTENKATEGORIE', 'LIKE', 'Kabel TV %')
                    ->orWhere('KOSTENKATEGORIE', 'LIKE', 'Thermenwartung%');
            });
    }

    public function openingBalanceDefinitions($from = null, $to = null)
    {
        return $this->rentDefinitions($from, $to)
            ->where('KOSTENKATEGORIE', '=', 'Saldo Vortrag Vorverwaltung');
    }

    public function postings($from = null, $to = null)
    {
        if (is_string($from)) {
            $from = Carbon::parse($from);
        }
        if (is_string($to)) {
            $to = Carbon::parse($to);
        }
        $postings = $this->morphMany(Posting::class, 'postings', 'KOSTENTRAEGER_TYP', 'KOSTENTRAEGER_ID');
        if ($from) {
            $postings->whereDate('DATUM', '>=', $from);
        }
        if ($to) {
            $postings->whereDate('DATUM', '<=', $to);
        }
        return $postings;
    }

    public function getMieterNamenAttribute()
    {
        return $this->mieter->implode('full_name', '; ');
    }

    public function getStartDateFieldName()
    {
        return 'MIETVERTRAG_VON';
    }

    public function getEndDateFieldName()
    {
        return 'MIETVERTRAG_BIS';
    }

    public function getSalutationAttribute()
    {
        $salutation = "Sehr geehrte Damen und Herren,";
        $tenants = $this->mieter;
        $tenantsWithoutGenderInformation = $tenants->filter(function ($value) {
            return $value->sex === null;
        });
        if ($tenants->isEmpty() || !$tenantsWithoutGenderInformation->isEmpty()) {
            return $salutation;
        }
        $tenants = $tenants->sortByDesc('sex')->values();
        $firstKey = $tenants->keys()->first();
        $lastKey = $tenants->keys()->last();
        foreach ($tenants as $key => $tenant) {
            if ($key === $firstKey) {
                $salutation = $tenant->salutation . "\n";
            } else {
                $salutationWithSmallFirstLetter = $tenant->salutation;
                if (count($salutationWithSmallFirstLetter) > 0) {
                    $salutationWithSmallFirstLetter[0] = 's';
                }
                $salutation .= $salutationWithSmallFirstLetter . "\n";
            }
            if ($key === $lastKey)
                $salutation = substr($salutation, 0, -1);
        }
        return $salutation;
    }

    public function getPostalAddressAttribute()
    {
        $ids = $this->mieter->pluck('id')->values()->toArray();
        $query = Details::where('DETAIL_NAME', 'Zustellanschrift')->whereHas('fromPerson', function ($query) use ($ids) {
            $query->whereIn('id', $ids);
        });
        $postalAdresses = $query->get();
        if (!$postalAdresses->isEmpty()) {
            return trim($postalAdresses->first()->DETAIL_INHALT);
        }
        try {
            $postalAddress = "";
            $tenants = $this->mieter->sortByDesc('sex')->values();
            foreach ($tenants as $tenant) {
                $postalAddress .= $tenant->addressName($this->mieter->count() === 1) . "\n";
            }
            return $postalAddress . $this->einheit->haus->postalAddress("\n");
        } catch (Exception $e) {
            return null;
        }
    }
}
