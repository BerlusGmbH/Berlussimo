<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use App\Models\Traits\DefaultOrder;
use App\Models\Traits\ExternalKey;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Wirtschaftseinheiten extends Model
{
    use Searchable;
    use DefaultOrder;
    use ExternalKey;

    public $timestamps = false;
    protected $table = 'WIRT_EINHEITEN';
    protected $primaryKey = 'W_DAT';
    protected $externalKey = 'id';
    protected $searchableFields = ['W_NAME'];
    protected $defaultOrder = ['W_NAME' => 'asc'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }

    public function einheiten(): BelongsToMany
    {
        return $this->belongsToMany(
            Einheiten::class,
            'WIRT_EIN_TAB',
            'W_ID',
            'EINHEIT_ID',
            'id',
            'id'
        )->wherePivot('AKTUELL', '1');
    }
}
