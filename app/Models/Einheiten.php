<?php

namespace App\Models;

use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Searchable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Einheiten extends Model
{
    use Searchable;
    use DefaultOrder;

    public $timestamps = false;
    protected $table = 'EINHEIT';
    protected $primaryKey = 'EINHEIT_ID';
    protected $searchableFields = ['EINHEIT_KURZNAME', 'EINHEIT_LAGE'];
    protected $defaultOrder = ['EINHEIT_KURZNAME' => 'asc'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('aktuell', function (Builder $builder) {
            $builder->where('EINHEIT_AKTUELL', '1');
        });
    }

    public function haus()
    {
        return $this->belongsTo('App\Models\Haeuser', 'HAUS_ID', 'HAUS_ID');
    }

    public function mietvertraege()
    {
        return $this->hasMany('App\Models\Mietvertraege',
            'EINHEIT_ID', 'EINHEIT_ID'
        );
    }

    public function kaufvertraege()
    {
        return $this->hasMany('App\Models\Kaufvertraege',
            'EINHEIT_ID', 'EINHEIT_ID'
        );
    }

    public function auftraege() {
        return $this->morphMany(Auftraege::class, 'kostentraeger', 'KOS_TYP', 'KOS_ID');
    }

    public function mieter($date = null) {
        if(is_null($date)) {
            $date = Carbon::today();
        }
        return Personen::whereHas('mietvertraege', function ($query) use ($date){
            $query->where('EINHEIT_ID', $this->EINHEIT_ID)->active('=', $date);
        });
    }

    public function WEGEigentuemer($date = null)
    {
        if (is_null($date)) {
            $date = Carbon::today();
        }
        return Personen::whereHas('kaufvertraege', function ($query) use ($date) {
            $query->where('EINHEIT_ID', $this->EINHEIT_ID)->active('=', $date);
        });
    }

    public function commonDetails() {
        return $this->details()->whereNotIn('DETAIL_NAME', ['Hinweis_zu_Einheit']);
    }

    public function details()
    {
        return $this->morphMany('App\Models\Details', 'details', 'DETAIL_ZUORDNUNG_TABELLE', 'DETAIL_ZUORDNUNG_ID');
    }

    public function hinweise() {
        return $this->details()->where('DETAIL_NAME', 'Hinweis_zu_Einheit');
    }

    public function hasHinweis() {
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
}
