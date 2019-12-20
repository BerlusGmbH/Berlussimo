<?php

namespace App\Models;

use App\Libraries\BelongsToMorph;
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
    protected $fillable = [
        'DETAIL_ID',
        'DETAIL_NAME',
        'DETAIL_INHALT',
        'DETAIL_BEMERKUNG',
        'DETAIL_AKTUELL',
        'DETAIL_ZUORDNUNG_TABELLE',
        'DETAIL_ZUORDNUNG_ID',
    ];
    protected $appends = ['type'];

    static public function getTypeAttribute()
    {
        return 'detail';
    }

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

    public function getDetailInhaltOneLineAttribute()
    {
        $content = $this->DETAIL_INHALT;
        $content = str_replace("\r\n", "", $content);
        $content = str_replace("\r", "", $content);
        $content = str_replace("\n", "", $content);
        $content = trim($content, "");
        return $content;
    }

    public function getDetailBemerkungOneLineAttribute()
    {
        $content = $this->DETAIL_BEMERKUNG;
        $content = str_replace("\r\n", "", $content);
        $content = str_replace("\r", "", $content);
        $content = str_replace("\n", "", $content);
        $content = trim($content, "");
        return $content;
    }

    public function getDETAILINHALTAttribute($value)
    {
        return strip_tags($value);
    }

    public function from()
    {
        return $this->morphTo('details', 'DETAIL_ZUORDNUNG_TABELLE', 'DETAIL_ZUORDNUNG_ID');
    }

    public function fromPerson()
    {
        return BelongsToMorph::build($this, Person::class, 'from', 'DETAIL_ZUORDNUNG_TABELLE', 'DETAIL_ZUORDNUNG_ID');
    }
}
