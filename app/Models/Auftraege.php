<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use App\Models\Traits\DefaultOrder;
use App\Models\Traits\ExternalKey;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Auftraege extends Model
{
    use Searchable;
    use DefaultOrder;
    use ExternalKey;

    public $timestamps = false;
    protected $table = 'TODO_LISTE';
    protected $primaryKey = 'T_DAT';
    protected $externalKey = 'T_ID';
    protected $searchableFields = ['TEXT', 'ERSTELLT', 'T_ID'];
    protected $defaultOrder = ['ERSTELLT' => 'desc'];
    protected $guarded = ['T_DAT'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }

    public function von(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'VERFASSER_ID');
    }

    public function an(): MorphTo
    {
        return $this->morphTo('an', 'BENUTZER_TYP', 'BENUTZER_ID', 'id');
    }

    public function kostentraeger(): MorphTo
    {
        return $this->morphTo('kostentraeger', 'KOS_TYP', 'KOS_ID', 'id');
    }
}

