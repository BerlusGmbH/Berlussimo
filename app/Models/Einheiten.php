<?php

namespace App\Models;

use App\Models\Traits\DefaultOrder;
use App\Models\Traits\ExternalKey;
use App\Models\Traits\HasEnum;
use App\Models\Traits\Searchable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Einheiten extends Model
{
    use Searchable;
    use DefaultOrder;
    use HasEnum;
    use ExternalKey;

    public const LIVING_SPACE = 'Wohnraum';
    public const INDIVIDUALLY_OWNED_LIVING_SPACE = 'Wohneigentum';
    public const COMMERCIAL_SPACE = 'Gewerbe';
    public const PARKING_SPACE = 'Stellplatz';
    public const GARAGE = 'Garage';
    public const CELLAR = 'Keller';
    public const OPEN_SPACE = 'Freiflaeche';
    public const ADVERTISING_SPACE = 'Werbeflaeche';
    public const BUGGY_SPACE = 'Kinderwagenbox';
    public const ROOM = 'Zimmer (möbliert)';
    public const TYPES = [
        self::LIVING_SPACE,
        self::INDIVIDUALLY_OWNED_LIVING_SPACE,
        self::COMMERCIAL_SPACE,
        self::PARKING_SPACE,
        self::GARAGE,
        self::CELLAR,
        self::OPEN_SPACE,
        self::ADVERTISING_SPACE,
        self::BUGGY_SPACE,
        self::ROOM
    ];

    public $timestamps = false;
    protected $table = 'EINHEIT';
    protected $primaryKey = 'EINHEIT_DAT';
    protected $externalKey = 'id';
    protected $searchableFields = ['EINHEIT_KURZNAME', 'EINHEIT_LAGE'];
    protected $defaultOrder = ['EINHEIT_KURZNAME' => 'asc'];
    protected $appends = ['vermietet'];
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('aktuell', function (Builder $builder) {
            $builder->where('EINHEIT_AKTUELL', '1');
        });
        static::addGlobalScope('appendDetails', function (Builder $builder) {
            $builder->with('hinweise');
        });
    }

    public function haus()
    {
        return $this->belongsTo(Haeuser::class, 'HAUS_ID', 'id');
    }

    public function mietvertraege(): HasMany
    {
        return $this->hasMany(Mietvertraege::class,
            'EINHEIT_ID', 'id'
        );
    }

    public function kaufvertraege(): HasMany
    {
        return $this->hasMany(Kaufvertraege::class,
            'EINHEIT_ID', 'id'
        );
    }

    public function auftraege(): MorphMany
    {
        return $this->morphMany(Auftraege::class, 'kostentraeger', 'KOS_TYP', 'KOS_ID', 'id');
    }

    public function mieter($date = null) {
        if(is_null($date)) {
            $date = Carbon::today();
        }
        return Person::whereHas('mietvertraege', function ($query) use ($date){
            $query->where('EINHEIT_ID', $this->id)->active('=', $date);
        });
    }

    public function WEGEigentuemer($date = null)
    {
        if (is_null($date)) {
            $date = Carbon::today();
        }
        return Person::whereHas('kaufvertraege', function ($query) use ($date) {
            $query->where('EINHEIT_ID', $this->id)->active('=', $date);
        });
    }

    public function commonDetails(): MorphMany
    {
        return $this->details()->whereNotIn('DETAIL_NAME', ['Hinweis_zu_Einheit']);
    }

    public function details(): MorphMany
    {
        return $this->morphMany('App\Models\Details', 'details', 'DETAIL_ZUORDNUNG_TABELLE', 'DETAIL_ZUORDNUNG_ID', 'id');
    }

    public function hinweise(): MorphMany
    {
        return $this->details()->where('DETAIL_NAME', 'Hinweis_zu_Einheit');
    }

    public function hasHinweis()
    {
        return $this->hinweise->count() > 0;
    }

    public function getVermietetAttribute()
    {
        foreach($this->mietvertraege as $mietvertrag) {
            if($mietvertrag->isActive()) {
                return true;
            }
        }
        return false;
    }

    public function getHomeOwnersAttribute()
    {
        return $this->WEGEigentuemer()->get();
    }

    public function getTenantsAttribute()
    {
        return $this->mieter()->get();
    }

    public function getHomeOwnersEMailsAttribute()
    {
        $emails = Details::where('DETAIL_NAME', 'Email')->whereHas('detailablePerson.kaufvertraege.einheit', function ($query) {
            $query->where('id', $this->id);
        })->get();
        return $emails;
    }

    public function getTenantsEMailsAttribute()
    {
        $emails = Details::where('DETAIL_NAME', 'Email')->whereHas('detailablePerson.mietvertraege.einheit', function ($query) {
            $query->where('id', $this->id);
        })->get();
        return $emails;
    }
}
