<?php

namespace App\Models;

use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Objekte extends Model
{
    use Searchable;
    use DefaultOrder;

    public $timestamps = false;
    protected $table = 'OBJEKT';
    protected $primaryKey = 'OBJEKT_ID';
    protected $searchableFields = ['OBJEKT_KURZNAME'];
    protected $defaultOrder = ['OBJEKT_KURZNAME' => 'asc'];

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
            })->whereDate('MIETVERTRAG_VON', '<=', $date)->where(function ($query) use($date) {
                $query->whereDate('MIETVERTRAG_BIS', '>=', $date)->orWhereDate('MIETVERTRAG_BIS', '=', '0000-00-00');
            });
        });
    }

    public function auftraege() {
        return $this->morphMany(Auftraege::class, 'kostentraeger', 'KOS_TYP', 'KOS_ID');
    }

    public function details()
    {
        return $this->morphMany('App\Models\Details', 'details', 'DETAIL_ZUORDNUNG_TABELLE', 'DETAIL_ZUORDNUNG_ID');
    }

    public function commonDetails() {
        return $this->details()->whereNotIn('DETAIL_NAME', ['Hinweis_zum_Objekt']);
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
}
