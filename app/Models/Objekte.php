<?php

namespace App\Models;

use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Objekte extends Model
{
    use Searchable;

    public $timestamps = false;
    protected $table = 'OBJEKT';
    protected $primaryKey = 'OBJEKT_ID';
    protected $searchableFields = ['OBJEKT_KURZNAME'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('aktuell', function (Builder $builder) {
            $builder->where('OBJEKT_AKTUELL', '1');
        });
    }
}
