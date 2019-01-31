<?php

namespace App\Models;

use App\Models\Traits\DefaultOrder;
use App\Models\Traits\ExternalKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Details extends Model
{
    use DefaultOrder;
    use ExternalKey;

    public $timestamps = false;
    protected $table = 'DETAIL';
    protected $primaryKey = 'DETAIL_DAT';
    protected $externalKey = 'DETAIL_ID';
    protected $defaultOrder = ['DETAIL_NAME' => 'asc', 'DETAIL_INHALT' => 'asc', 'DETAIL_BEMERKUNG' => 'asc'];
    protected $fillable = [
        'DETAIL_ID',
        'DETAIL_NAME',
        'DETAIL_INHALT',
        'DETAIL_BEMERKUNG',
        'DETAIL_AKTUELL',
        'DETAIL_ZUORDNUNG_TABELLE',
        'DETAIL_ZUORDNUNG_ID',
    ];

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
        $content = str_replace("\r\n", "<br>", $content);
        $content = str_replace("\r", "<br>", $content);
        $content = str_replace("\n", "<br>", $content);
        $content = trim($content, "<br>");
        return $content;
    }

    public function getDETAILINHALTAttribute($value)
    {
        return strip_tags($value);
    }

    public function from(): MorphTo
    {
        return $this->morphTo('details', 'DETAIL_ZUORDNUNG_TABELLE', 'DETAIL_ZUORDNUNG_ID', 'id');
    }

    public function detailable(): MorphTo
    {
        return $this->from();
    }

    public function detailablePerson(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'DETAIL_ZUORDNUNG_ID', 'id')->where('DETAIL_ZUORDNUNG_TABELLE', 'Person');
    }
}
