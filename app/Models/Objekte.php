<?php

namespace App\Models;

use App\Models\Traits\CopyObject;
use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Searchable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Objekte extends Model
{
    use Searchable, DefaultOrder, CopyObject;

    public $timestamps = false;
    protected $table = 'OBJEKT';
    protected $primaryKey = 'OBJEKT_ID';
    protected $searchableFields = ['OBJEKT_KURZNAME'];
    protected $defaultOrder = ['OBJEKT_KURZNAME' => 'asc'];
    protected $appends = ['type'];

    static public function getTypeAttribute()
    {
        return 'pm_object';
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('aktuell', function (Builder $builder) {
            $builder->where('OBJEKT_AKTUELL', '1');
        });
    }

    public function haeuser() {
        return $this->hasMany('App\Models\Haeuser', 'OBJEKT_ID', 'OBJEKT_ID');
    }

    public function einheiten()
    {
        return $this->hasManyThrough(
            Einheiten::class, Haeuser::class,
            'OBJEKT_ID', 'HAUS_ID', 'OBJEKT_ID'
        )->whereHas('haus', function ($query){
            $query->where('HAUS_AKTUELL', '1');
        })->distinct();
    }

    public function mieter($date = null) {
        if(is_null($date)) {
            $date = Carbon::today();
        }
        return Person::whereHas('mietvertraege', function ($query) use ($date){
            $query->whereHas('einheit.haus.objekt', function ($query) {
                $query->where('OBJEKT_ID', $this->OBJEKT_ID);
            })->active('=', $date);
        });
    }

    public function WEGEigentuemer($date = null)
    {
        if (is_null($date)) {
            $date = Carbon::today();
        }
        return Person::whereHas('kaufvertraege', function ($query) use ($date) {
            $query->whereHas('einheit.haus.objekt', function ($query) {
                $query->where('OBJEKT_ID', $this->OBJEKT_ID);
            })->active('=', $date);
        });
    }

    public function auftraege() {
        return $this->morphMany(Auftraege::class, 'kostentraeger', 'KOS_TYP', 'KOS_ID');
    }

    public function commonDetails() {
        return $this->details()->whereNotIn('DETAIL_NAME', ['Hinweis_zum_Objekt']);
    }

    public function details()
    {
        return $this->morphMany('App\Models\Details', 'details', 'DETAIL_ZUORDNUNG_TABELLE', 'DETAIL_ZUORDNUNG_ID');
    }

    public function hinweise() {
        return $this->details()->where('DETAIL_NAME', 'Hinweis_zum_Objekt');
    }

    public function hasHinweis() {
        return $this->hinweise->count() > 0;
    }

    public function eigentuemer() {
        return $this->belongsTo(Partner::class, 'EIGENTUEMER_PARTNER', 'PARTNER_ID');
    }

    public function bankkonten() {
        return $this->belongsToMany(Objekte::class, 'GELD_KONTEN_ZUWEISUNG', 'KOSTENTRAEGER_ID', 'KOSTENTRAEGER_ID')->wherePivot('KOSTENTRAEGER_TYP', 'OBJEKT')->wherePivot('AKTUELL', '1');
    }

    public function getWohnflaecheAttribute()
    {
        $flaeche = Einheiten::whereHas('haus.objekt', function ($query) {
            $query->where('OBJEKT_ID', $this->OBJEKT_ID);
        })->whereIn('TYP', ['Wohnraum', 'Wohneigentum'])->sum('EINHEIT_QM');
        return isset($flaeche) ? $flaeche : 0;
    }

    public function getGewerbeflaecheAttribute()
    {
        $flaeche = Einheiten::whereHas('haus.objekt', function ($query) {
            $query->where('OBJEKT_ID', $this->OBJEKT_ID);
        })->where('TYP', 'Gewerbe')->sum('EINHEIT_QM');
        return isset($flaeche) ? $flaeche : 0;
    }
}
