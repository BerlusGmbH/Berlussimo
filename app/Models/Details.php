<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Details extends Model
{
    //use Searchable;

    public $timestamps = false;
    protected $table = 'DETAIL';
    protected $primaryKey = 'DETAIL_ID';

    //protected $searchableFields = ['HAUS_STRASSE', 'HAUS_NUMMER', 'HAUS_PLZ', 'HAUS_STADT'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('aktuell', function (Builder $builder) {
            $builder->where('DETAIL_AKTUELL', '1');
        });
    }

    public function getDetailInhaltWithBrAttribute()
    {
        $content = $this->DETAIL_INHALT;
        $content = str_replace('\r\n', '<br>', $content);
        $content = str_replace('\r', '<br>', $content);
        $content = str_replace('\n', '<br>', $content);
        return $content;
    }

    public function from()
    {
        return $this->morphTo('details', 'DETAIL_ZUORDNUNG_TABELLE', 'DETAIL_ZUORDNUNG_ID');
    }
}
