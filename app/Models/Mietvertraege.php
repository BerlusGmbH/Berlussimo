<?php

namespace App\Models;

use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Mietvertraege extends Model
{
    use Searchable;

    public $timestamps = false;
    protected $table = 'MIETVERTRAG';
    protected $primaryKey = 'MIETVERTRAG_ID';
    protected $searchableFields = ['MIETVERTRAG_VON', 'MIETVERTRAG_BIS'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('aktuell', function (Builder $builder) {
            $builder->where('MIETVERTRAG_AKTUELL', '1');
        });
    }

    public function mieter()
    {
        return $this->belongsToMany('App\Models\Personen', 'PERSON_MIETVERTRAG', 'PERSON_MIETVERTRAG_MIETVERTRAG_ID', 'PERSON_MIETVERTRAG_PERSON_ID')->wherePivot('PERSON_MIETVERTRAG_AKTUELL', '=', '1');
    }

    public function einheit()
    {
        return $this->belongsTo('App\Models\Einheiten', 'EINHEIT_ID', 'EINHEIT_ID');
    }
}
