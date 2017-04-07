<?php

namespace App\Models;

use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Searchable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Mietvertraege extends Model
{
    use Searchable;
    use DefaultOrder;

    public $timestamps = false;
    protected $table = 'MIETVERTRAG';
    protected $primaryKey = 'MIETVERTRAG_ID';
    protected $searchableFields = ['MIETVERTRAG_VON', 'MIETVERTRAG_BIS'];
    protected $defaultOrder = ['MIETVERTRAG_VON' => 'desc', 'MIETVERTRAG_BIS' => 'desc'];

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

    public function isActive() {
        $today = Carbon::today();
        return $this->MIETVERTRAG_VON <= $today && ($this->MIETVERTRAG_BIS >= $today || $this->MIETVERTRAG_BIS == '0000-00-00');
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
                    $query->whereDate('MIETVERTRAG_VON', '<=', $date)->whereDate('MIETVERTRAG_BIS', '>=', $date);
                })->orWhere(function($query) use($date) {
                    $query->where('MIETVERTRAG_VON', '<=', $date)->whereDate('MIETVERTRAG_BIS', '=', '0000-00-00');
                });
            });
        } elseif ($comparator == '>') {
            $query->where(function($query) use ($date) {
                $query->whereDate('MIETVERTRAG_BIS', '>=', $date)->orWhereDate('MIETVERTRAG_BIS', '=', '0000-00-00');
            });
        } elseif ($comparator == '<') {
            $query->whereDate('MIETVERTRAG_VON', '<=', $date);
        }
    }

    public function scopeNotAktiv($query, $comparator = '=', $date = null) {
        if(is_null($date)) {
            $date = Carbon::today();
        }
        if($comparator == '=') {
            $query->where(function ($query) use ($date) {
                $query->whereDate('MIETVERTRAG_VON', '>', $date)->orWhereDate('MIETVERTRAG_BIS', '<', $date)->where('MIETVERTRAG_BIS', '<>', '0000-00-00');
            });
        } elseif ($comparator == '>') {
            $query->whereDate('MIETVERTRAG_BIS', '<', $date)->where('MIETVERTRAG_BIS', '<>', '0000-00-00');
        } elseif ($comparator == '<') {
            $query->whereDate('MIETVERTRAG_VON', '>', $date);
        }
    }

    public function getMieterNamenAttribute() {
        return $this->mieter->implode('full_name', '; ');
    }
}
