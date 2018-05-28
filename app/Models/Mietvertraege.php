<?php

namespace App\Models;

use App\Models\Contracts\Active as ActiveContract;
use App\Models\Traits\Active;
use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Searchable;
use Carbon\Carbon;
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

    public function mieter()
    {
        return $this->belongsToMany(Person::class, 'PERSON_MIETVERTRAG', 'PERSON_MIETVERTRAG_MIETVERTRAG_ID', 'PERSON_MIETVERTRAG_PERSON_ID')->wherePivot('PERSON_MIETVERTRAG_AKTUELL', '1');
    }

    public function einheit()
    {
        return $this->belongsTo('App\Models\Einheiten', 'EINHEIT_ID', 'EINHEIT_ID');
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
                    ->orWhere('KOSTENKATEGORIE', '=', 'Mietminderung')
                    ->orWhere('KOSTENKATEGORIE', '=', 'MOD')
                    ->orWhere('KOSTENKATEGORIE', '=', 'Stellplatzmiete')
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
        $rentDefinitions = $this->morphMany(RentDefinition::class, 'rentDefinitions', 'KOSTENTRAEGER_TYP', 'KOSTENTRAEGER_ID');
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

    public function heatingExpenseDefinitions($from = null, $to = null)
    {
        return $this->rentDefinitions($from, $to)
            ->where(function ($query) {
                $query->where('KOSTENKATEGORIE', 'LIKE', 'Heizkostenabrechnung%')
                    ->orWhere('KOSTENKATEGORIE', '=', 'Heizkosten Vorauszahlung');
            });
    }

    public function operatingCostDefinitions($from = null, $to = null)
    {
        return $this->rentDefinitions($from, $to)
            ->where(function ($query) {
                $query->where('KOSTENKATEGORIE', 'LIKE', 'Betriebskostenabrechnung%')
                    ->orWhere('KOSTENKATEGORIE', 'LIKE', 'Kabel TV%')
                    ->orWhere('KOSTENKATEGORIE', 'LIKE', 'Kaltwasserabrechnung%')
                    ->orWhere('KOSTENKATEGORIE', '=', 'Nebenkosten Vorauszahlung')
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
        $rentDefinitions = $this->morphMany(Posting::class, 'postings', 'KOSTENTRAEGER_TYP', 'KOSTENTRAEGER_ID');
        if ($from) {
            $rentDefinitions->whereDate('DATUM', '>=', $from);
        }
        if ($to) {
            $rentDefinitions->whereDate('DATUM', '<=', $to);
        }
        return $rentDefinitions;
    }
}
