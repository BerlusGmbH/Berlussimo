<?php

namespace App\Models;

use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Haeuser extends Model
{
    use Searchable;

    public $timestamps = false;
    protected $table = 'HAUS';
    protected $primaryKey = 'HAUS_ID';
    protected $searchableFields = ['HAUS_STRASSE', 'HAUS_NUMMER', 'HAUS_PLZ', 'HAUS_STADT'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('aktuell', function (Builder $builder) {
            $builder->where('HAUS_AKTUELL', '1');
        });
    }

    public function objekt()
    {
        return $this->belongsTo('App\Models\Objekte', 'OBJEKT_ID', 'OBJEKT_ID');
    }
}
