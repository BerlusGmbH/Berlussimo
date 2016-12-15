<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use Searchable;

    public $timestamps = false;
    protected $table = 'PARTNER_LIEFERANT';
    protected $primaryKey = 'PARTNER_ID';
    protected $searchableFields = ['PARTNER_NAME', 'STRASSE', 'NUMMER', 'PLZ', 'ORT'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }
}
