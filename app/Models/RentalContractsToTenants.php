<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RentalContractsToTenants extends Pivot
{
    public $timestamps = false;
    protected $table = 'PERSON_MIETVERTRAG';

    public static function boot()
    {
        parent::boot();

        static::addGlobalScope('aktuell', function (Builder $builder) {
            $builder->where('PERSON_MIETVERTRAG_AKTUELL', '1');
        });
    }
}