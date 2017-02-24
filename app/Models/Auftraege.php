<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class Auftraege extends Model
{
    use Searchable;
    use DefaultOrder;

    public $timestamps = false;
    protected $table = 'TODO_LISTE';
    protected $primaryKey = 'T_ID';
    protected $searchableFields = ['TEXT'];
    protected $defaultOrder = ['ERLEDIGT' => 'asc', 'AKUT' => 'asc', 'ERSTELLT' => 'desc'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }

    public function von()
    {
        return $this->belongsTo('App\Models\User', 'VERFASSER_ID');
    }

    public function an()
    {
        return $this->morphTo('an', 'BENUTZER_TYP', 'BENUTZER_ID');
    }

    public function kostentraeger()
    {
        return $this->morphTo('kostentraeger', 'KOS_TYP', 'KOS_ID');
    }
}
