<?php

namespace App\Models;

use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Personen extends Model
{
    use Searchable;

    public $timestamps = false;
    protected $table = 'PERSON';
    protected $primaryKey = 'PERSON_ID';
    protected $searchableFields = ['PERSON_VORNAME', 'PERSON_NACHNAME'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('aktuell', function (Builder $builder) {
            $builder->where('PERSON_AKTUELL', '1');
        });
    }

    public function mietvertraege()
    {
        return $this->belongsToMany('App\Models\Mietvertraege',
            'PERSON_MIETVERTRAG',
            'PERSON_MIETVERTRAG_PERSON_ID',
            'PERSON_MIETVERTRAG_MIETVERTRAG_ID'
        );
    }

    public function eigentuemer()
    {
        return $this->belongsToMany('App\Models\WEGEigentuemer',
            'WEG_EIGENTUEMER_PERSON',
            'PERSON_ID',
            'WEG_EIG_ID'
        );
    }
}
