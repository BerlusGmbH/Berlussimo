<?php

namespace App\Models;

use App\Models\Contracts\Active as ActiveContract;
use App\Models\Scopes\AktuellScope;
use App\Models\Traits\Active;
use App\Models\Traits\DefaultOrder;
use Illuminate\Database\Eloquent\Model;

class Kaufvertraege extends Model implements ActiveContract
{
    use DefaultOrder;
    use Active;

    public $timestamps = false;
    protected $table = 'WEG_MITEIGENTUEMER';
    protected $primaryKey = 'ID';
    protected $defaultOrder = ['VON' => 'desc', 'BIS' => 'desc'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }

    public function eigentuemer()
    {
        return $this->belongsToMany(Person::class, 'WEG_EIGENTUEMER_PERSON', 'WEG_EIG_ID', 'PERSON_ID')->wherePivot('AKTUELL', '1');
    }

    public function einheit()
    {
        return $this->belongsTo('App\Models\Einheiten', 'EINHEIT_ID', 'EINHEIT_ID');
    }

    public function getStartDateFieldName()
    {
        return 'VON';
    }

    public function getEndDateFieldName()
    {
        return 'BIS';
    }
}
