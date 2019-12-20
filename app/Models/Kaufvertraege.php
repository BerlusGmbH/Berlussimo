<?php

namespace App\Models;

use App\Models\Contracts\Active as ActiveContract;
use App\Models\Scopes\AktuellScope;
use App\Models\Traits\Active;
use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Searchable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Kaufvertraege extends Model implements ActiveContract
{
    use Searchable {
        Searchable::scopeSearch as scopeSearchFromTrait;
    }
    use DefaultOrder;
    use Active;

    public $timestamps = false;
    protected $table = 'WEG_MITEIGENTUEMER';
    protected $primaryKey = 'ID';
    protected $searchableFields = ['ID', 'VON', 'BIS'];
    protected $defaultOrder = ['VON' => 'desc', 'BIS' => 'desc'];
    protected $appends = ['type'];

    static public function getTypeAttribute()
    {
        return 'purchase_contract';
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }

    public function eigentuemer()
    {
        return $this->belongsToMany(Person::class, 'WEG_EIGENTUEMER_PERSON', 'WEG_EIG_ID', 'PERSON_ID')->wherePivot('AKTUELL', '1');
    }

    public function einheit()
    {
        return $this->belongsTo('App\Models\Einheiten', 'EINHEIT_ID', 'EINHEIT_ID');
    }

    public function SEPAMandates()
    {
        return $this->morphMany(SEPAMandate::class, 'SEPAMAndates', 'M_KOS_TYP', 'M_KOS_ID');
    }

    public function hoaFeeDefinitions($from = null, $to = null)
    {
        if (is_string($from)) {
            $from = Carbon::parse($from);
        }
        if (is_string($to)) {
            $to = Carbon::parse($to);
        }
        $hoaFeeDefinitions = $this->hasMany(HOAFeeDefinition::class, 'KOS_ID', 'EINHEIT_ID')
            ->where('KOS_TYP', 'Einheit');
        if ($from) {
            $hoaFeeDefinitions->whereDate('ANFANG', '<=', $to);
        }
        if ($to) {
            $hoaFeeDefinitions->where(function ($query) use ($from) {
                $query->whereDate('ENDE', '>=', $from)
                    ->orWhere('ENDE', '0000-00-00');
            });
        }
        return $hoaFeeDefinitions;
    }

    public function getStartDateFieldName()
    {
        return 'VON';
    }

    public function getEndDateFieldName()
    {
        return 'BIS';
    }

    public function scopeSearch($query, $tokens)
    {
        $query->with(['einheit', 'eigentuemer'])->orWhere(function ($query) use ($tokens) {
            $query->searchFromTrait($tokens);
        })->orWhereHas('einheit', function ($query) use ($tokens) {
            $query->search($tokens);
        })->orWhereHas('eigentuemer', function ($query) use ($tokens) {
            $query->search($tokens);
        });
        return $query;
    }
}
