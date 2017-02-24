<?php

namespace App\Models;

use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
        return Personen::whereHas('mietvertraege.einheit', function ($query) {
            $query->where('EINHEIT_ID', $this->EINHEIT_ID);
        })->whereHas('mietvertraege', function ($query) use ($date){
            $query->whereDate('MIETVERTRAG_VON', '<=', $date)->where(function ($query) use($date) {
                $query->whereDate('MIETVERTRAG_BIS', '>=', $date)->orWhereDate('MIETVERTRAG_BIS', '=', '0000-00-00');
            });
        });
    }

    public function details()
    {
        return $this->morphMany('App\Models\Details', 'details', 'DETAIL_ZUORDNUNG_TABELLE', 'DETAIL_ZUORDNUNG_ID');
    }

    public function commonDetails() {
        return $this->details()->whereNotIn('DETAIL_NAME', ['']);
    }
}
