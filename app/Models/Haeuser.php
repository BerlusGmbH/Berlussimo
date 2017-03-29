<?php

namespace App\Models;

use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Haeuser extends Model
{
    use Searchable;
    use DefaultOrder;

    public $timestamps = false;
    protected $table = 'HAUS';
    protected $primaryKey = 'HAUS_ID';
    protected $searchableFields = ['HAUS_STRASSE', 'HAUS_NUMMER', 'HAUS_PLZ', 'HAUS_STADT'];
    protected $defaultOrder = ['HAUS_STRASSE' => 'asc', 'HAUS_NUMMER' => 'asc'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('aktuell', function (Builder $builder) {
            $builder->where('HAUS_AKTUELL', '1');
        });
    }

    public function objekt()
    {
        return $this->belongsTo('App\Models\Objekte', 'OBJEKT_ID', 'OBJEKT_ID');
    }

    public function einheiten() {
        return $this->hasMany('App\Models\Einheiten', 'HAUS_ID', 'HAUS_ID');
    }

    public function auftraege() {
        return $this->morphMany(Auftraege::class, 'kostentraeger', 'KOS_TYP', 'KOS_ID');
    }

    public function details()
    {
        return $this->morphMany('App\Models\Details', 'details', 'DETAIL_ZUORDNUNG_TABELLE', 'DETAIL_ZUORDNUNG_ID');
    }

    public function commonDetails() {
        return $this->details()->whereNotIn('DETAIL_NAME', ['Hinweis_zum_Haus']);
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
                    $query->where('HAUS_ID', $this->HAUS_ID);
            })->whereDate('MIETVERTRAG_VON', '<=', $date)->where(function ($query) use($date) {
                $query->whereDate('MIETVERTRAG_BIS', '>=', $date)->orWhereDate('MIETVERTRAG_BIS', '=', '0000-00-00');
            });
        });
    }
}
