<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\DefaultOrder;

class Kaufvertraege extends Model
{
    use DefaultOrder;

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
        return $this->belongsToMany('App\Models\Personen', 'WEG_EIGENTUEMER_PERSON', 'WEG_EIG_ID', 'PERSON_ID');
    }

    public function einheit()
    {
        return $this->belongsTo('App\Models\Einheiten', 'EINHEIT_ID', 'EINHEIT_ID');
    }
}
