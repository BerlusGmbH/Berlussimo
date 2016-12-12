<?php

namespace App\Models;

use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Personen extends Model
{
    use Searchable;

    public $timestamps = false;
    protected $table = 'PERSON';
    protected $primaryKey = 'PERSON_DAT';
    protected $searchableFields = ['PERSON_VORNAME', 'PERSON_NACHNAME'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('aktuell', function (Builder $builder) {
            $builder->where('PERSON_AKTUELL', '1');
        });
    }
}
