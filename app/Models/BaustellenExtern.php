<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use App\Models\Traits\DefaultOrder;
use App\Models\Traits\ExternalKey;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BaustellenExtern extends Model
{
    use Searchable;
    use DefaultOrder;
    use ExternalKey;

    public $timestamps = false;
    protected $table = 'BAUSTELLEN_EXT';
    protected $primaryKey = 'DAT';
    protected $externalKey = 'id';
    protected $searchableFields = ['BEZ'];
    protected $defaultOrder = ['AKTIV' => 'asc', 'BEZ' => 'asc'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }

    public function contractee(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'id', 'id');
    }
}
