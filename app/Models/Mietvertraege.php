<?php

namespace App\Models;

use App\Models\Contracts\Active as ActiveContract;
use App\Models\Traits\Active;
use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Mietvertraege extends Model implements ActiveContract
{
    use Searchable {
        Searchable::scopeSearch as scopeSearchFromTrait;
    }
    use DefaultOrder;
    use Active;

    public $timestamps = false;
    protected $table = 'MIETVERTRAG';
    protected $primaryKey = 'MIETVERTRAG_ID';
    protected $searchableFields = ['MIETVERTRAG_ID', 'MIETVERTRAG_VON', 'MIETVERTRAG_BIS'];
    protected $defaultOrder = ['MIETVERTRAG_VON' => 'desc', 'MIETVERTRAG_BIS' => 'desc'];
    protected $appends = ['type'];

    static public function getTypeAttribute()
    {
        return 'rental_contract';
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('aktuell', function (Builder $builder) {
            $builder->where('MIETVERTRAG_AKTUELL', '1');
        });
    }

    public function mieter()
    {
        return $this->belongsToMany(Person::class, 'PERSON_MIETVERTRAG', 'PERSON_MIETVERTRAG_MIETVERTRAG_ID', 'PERSON_MIETVERTRAG_PERSON_ID')->wherePivot('PERSON_MIETVERTRAG_AKTUELL', '1');
    }

    public function einheit()
    {
        return $this->belongsTo('App\Models\Einheiten', 'EINHEIT_ID', 'EINHEIT_ID');
    }

    public function getMieterNamenAttribute()
    {
        return $this->mieter->implode('full_name', '; ');
    }

    public function getStartDateFieldName()
    {
        return 'MIETVERTRAG_VON';
    }

    public function getEndDateFieldName()
    {
        return 'MIETVERTRAG_BIS';
    }

    public function scopeSearch($query, $tokens)
    {
        $query->with(['einheit', 'mieter'])->orWhere(function ($query) use ($tokens) {
            $query->searchFromTrait($tokens);
        })->orWhereHas('einheit', function ($query) use ($tokens) {
            $query->search($tokens);
        })->orWhereHas('mieter', function ($query) use ($tokens) {
            $query->search($tokens);
        });
        return $query;
    }
}
