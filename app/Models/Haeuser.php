<?php

namespace App\Models;

use App\Models\Traits\DefaultOrder;
use App\Models\Traits\ExternalKey;
use App\Models\Traits\Searchable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Haeuser extends Model
{
    use Searchable;
    use DefaultOrder;
    use ExternalKey;

    public $timestamps = false;
    protected $table = 'HAUS';
    protected $primaryKey = 'HAUS_DAT';
    protected $externalKey = 'id';
    protected $searchableFields = ['HAUS_STRASSE', 'HAUS_NUMMER', 'HAUS_PLZ', 'HAUS_STADT'];
    protected $defaultOrder = ['HAUS_STRASSE' => 'asc',
        'CAST(HAUS_NUMMER AS UNSIGNED)' => 'asc',
        'LENGTH(HAUS_NUMMER)' => 'asc',
        'HAUS_NUMMER' => 'asc'
    ];
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('aktuell', function (Builder $builder) {
            $builder->where('HAUS_AKTUELL', '1');
        });
        static::addGlobalScope('appendDetails', function (Builder $builder) {
            $builder->with('hinweise');
        });
    }

    public function objekt()
    {
        return $this->belongsTo(Objekte::class, 'OBJEKT_ID', 'id');
    }

    public function einheiten() {
        return $this->hasMany(Einheiten::class, 'HAUS_ID', 'id');
    }

    public function auftraege() {
        return $this->morphMany(Auftraege::class, 'kostentraeger', 'KOS_TYP', 'KOS_ID', 'id');
    }

    public function commonDetails() {
        return $this->details()->whereNotIn('DETAIL_NAME', ['Hinweis_zum_Haus']);
    }

    public function details()
    {
        return $this->morphMany('App\Models\Details', 'details', 'DETAIL_ZUORDNUNG_TABELLE', 'DETAIL_ZUORDNUNG_ID', 'id');
    }

    public function hinweise() {
        return $this->details()->where('DETAIL_NAME', 'Hinweis_zum_Haus');
    }

    public function hasHinweis() {
        return $this->hinweise->count() > 0;
    }

    public function mieter($date = null) {
        if(is_null($date)) {
            $date = Carbon::today();
        }
        return Person::whereHas('mietvertraege', function ($query) use ($date){
            $query->whereHas('einheit.haus', function ($query) {
                $query->where('id', $this->id);
            })->active('=', $date);
        });
    }

    public function WEGEigentuemer($date = null)
    {
        if (is_null($date)) {
            $date = Carbon::today();
        }
        return Person::whereHas('kaufvertraege', function ($query) use ($date) {
            $query->whereHas('einheit.haus', function ($query) {
                $query->where('id', $this->id);
            })->active('=', $date);
        });
    }

    public function getWohnflaecheAttribute()
    {
        $flaeche = Einheiten::whereHas('haus', function ($query) {
            $query->where('id', $this->id);
        })->whereIn('TYP', ['Wohnraum', 'Wohneigentum'])->sum('EINHEIT_QM');
        return isset($flaeche) ? $flaeche : 0;
    }

    public function getGewerbeflaecheAttribute()
    {
        $flaeche = Einheiten::whereHas('haus', function ($query) {
            $query->where('id', $this->id);
        })->where('TYP', 'Gewerbe')->sum('EINHEIT_QM');
        return isset($flaeche) ? $flaeche : 0;
    }

    public function getHomeOwnersEMailsAttribute()
    {
        $emails = Details::where('DETAIL_NAME', 'Email')->whereHas('detailablePerson.kaufvertraege.einheit.haus', function ($query) {
            $query->where('id', $this->id);
        })->get();
        return $emails;
    }

    public function getTenantsEMailsAttribute()
    {
        $emails = Details::where('DETAIL_NAME', 'Email')->whereHas('detailablePerson.mietvertraege.einheit.haus', function ($query) {
            $query->where('id', $this->id);
        })->get();
        return $emails;
    }

    public function getHomeOwnersAttribute()
    {
        return $this->WEGEigentuemer()->get();
    }

    public function getTenantsAttribute()
    {
        return $this->mieter()->get();
    }
}
