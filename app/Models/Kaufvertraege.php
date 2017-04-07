<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use App\Models\Traits\DefaultOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Kaufvertraege extends Model
{
    use DefaultOrder;

    public $timestamps = false;
    protected $table = 'WEG_MITEIGENTUEMER';
    protected $primaryKey = 'ID';
    protected $defaultOrder = ['VON' => 'desc', 'BIS' => 'desc'];

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

    public function scopeAktiv($query, $comparator = '=', $date = null) {
        if(is_null($date)) {
            $date = Carbon::today();
        }
        if($comparator == '=') {
            $query->where(function($query) use ($date) {
                $query->where(function($query) use ($date) {
                    $query->whereDate('VON', '<=', $date)->whereDate('BIS', '>=', $date);
                })->orWhere(function($query) use($date) {
                    $query->where('VON', '<=', $date)->whereDate('BIS', '=', '0000-00-00');
                });
            });
        } elseif ($comparator == '>') {
            $query->where(function($query) use ($date) {
                $query->whereDate('BIS', '>=', $date)->orWhereDate('BIS', '=', '0000-00-00');
            });
        } elseif ($comparator == '<') {
            $query->whereDate('VON', '<=', $date);
        }
    }

    public function scopeNotAktiv($query, $comparator = '=', $date = null) {
        if(is_null($date)) {
            $date = Carbon::today();
        }
        if($comparator == '=') {
            $query->where(function ($query) use ($date) {
                $query->whereDate('VON', '>', $date)->orWhereDate('BIS', '<', $date)->where('BIS', '<>', '0000-00-00');
            });
        } elseif ($comparator == '>') {
            $query->whereDate('BIS', '<', $date)->where('BIS', '<>', '0000-00-00');
        } elseif ($comparator == '<') {
            $query->whereDate('VON', '>', $date);
        }
    }

    public function isActive() {
        $today = Carbon::today();
        return $this->VON <= $today && ($this->BIS >= $today || $this->BIS == '0000-00-00');
    }
}
