<?php

namespace App\Models;

use App\Models\Traits\DefaultOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Details extends Model
{
    use DefaultOrder;

    public $timestamps = false;
    protected $table = 'DETAIL';
    protected $primaryKey = 'DETAIL_ID';
    protected $defaultOrder = ['DETAIL_NAME' => 'asc', 'DETAIL_INHALT' => 'asc', 'DETAIL_BEMERKUNG' => 'asc'];


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
