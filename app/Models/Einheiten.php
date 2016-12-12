<?php

namespace App\Models;

use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Einheiten extends Model
{
    use Searchable;

    public $timestamps = false;
    protected $table = 'EINHEIT';
    protected $primaryKey = 'EINHEIT_DAT';
    protected $searchableFields = ['EINHEIT_KURZNAME', 'EINHEIT_LAGE'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('aktuell', function (Builder $builder) {
            $builder->where('EINHEIT_AKTUELL', '1');
        });
    }
}
