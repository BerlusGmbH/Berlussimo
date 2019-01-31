<?php

namespace App\Models;

use App\Models\Contracts\Active as ActiveContract;
use App\Models\Scopes\AktuellScope;
use App\Models\Traits\Active;
use App\Models\Traits\DefaultOrder;
use App\Models\Traits\ExternalKey;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class Kaufvertraege extends Model implements ActiveContract
{
    use Searchable {
        Searchable::scopeSearch as scopeSearchFromTrait;
    }
    use DefaultOrder {
        DefaultOrder::scopeDefaultOrder as scopeDefaultOrderFromTrait;
    }
    use DefaultOrder;
    use Active;
    use ExternalKey;

    public $timestamps = false;
    protected $table = 'WEG_MITEIGENTUEMER';
    protected $primaryKey = 'DAT';
    protected $externalKey = 'id';
    protected $searchableFields = ['id', 'VON', 'BIS'];
    protected $defaultOrder = ['VON' => 'desc', 'BIS' => 'desc'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }

    public function eigentuemer()
    {
        return $this->belongsToMany(
            Person::class,
            'WEG_EIGENTUEMER_PERSON',
            'WEG_EIG_ID',
            'PERSON_ID',
            'id',
            'id'
        )->wherePivot('AKTUELL', '1');
    }

    public function einheit()
    {
        return $this->belongsTo(
            Einheiten::class,
            'EINHEIT_ID',
            'id'
        );
    }

    public function getStartDateFieldName()
    {
        return 'VON';
    }

    public function getEndDateFieldName()
    {
        return 'BIS';
    }

    public function scopeSearch($query, $tokens)
    {
        $query->with(['einheit', 'eigentuemer'])->orWhere(function ($query) use ($tokens) {
            $query->searchFromTrait($tokens);
        })->orWhereHas('einheit', function ($query) use ($tokens) {
            $query->search($tokens);
        })->orWhereHas('eigentuemer', function ($query) use ($tokens) {
            $query->search($tokens);
        });
        return $query;
    }

    public function scopeDefaultOrder($query)
    {
        $query->orderByRaw('CASE '
            . 'WHEN WEG_MITEIGENTUEMER.VON <= NOW() && (WEG_MITEIGENTUEMER.BIS >= NOW() || WEG_MITEIGENTUEMER.BIS = \'0000-00-00\') THEN 1 '
            . 'ELSE 2 '
            . 'END'
        )->defaultOrderFromTrait($query);
        return $query;
    }
}
