<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use App\Libraries\BelongsToMorph;

class Auftraege extends Model
{
    use Searchable;
    use DefaultOrder;

    public $timestamps = false;
    protected $table = 'TODO_LISTE';
    protected $primaryKey = 'T_ID';
    protected $searchableFields = ['TEXT', 'ERSTELLT'];
    protected $defaultOrder = ['ERSTELLT' => 'desc'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }

    public function von()
    {
        return $this->belongsTo(User::class, 'VERFASSER_ID');
    }

    public function an()
    {
        return $this->morphTo('an', 'BENUTZER_TYP', 'BENUTZER_ID');
    }

    public function anUser()
    {
        return BelongsToMorph::build($this, User::class, 'an', 'BENUTZER_TYP', 'BENUTZER_ID');
    }

    public function anPartner()
    {
        return BelongsToMorph::build($this, Partner::class, 'an', 'BENUTZER_TYP', 'BENUTZER_ID');
    }

    public function kostentraeger()
    {
        return $this->morphTo('kostentraeger', 'KOS_TYP', 'KOS_ID');
    }

    public function kostentraegerBaustellenExtern()
    {
        return BelongsToMorph::build($this, BaustellenExtern::class, 'kostentraegerBaustelle', 'KOS_TYP', 'KOS_ID');
    }

    public function kostentraegerUser()
    {
        return BelongsToMorph::build($this, User::class, 'kostentraegerMitarbeiter', 'KOS_TYP', 'KOS_ID');
    }

    public function kostentraegerPartner()
    {
        return BelongsToMorph::build($this, Partner::class, 'kostentraegerPartner', 'KOS_TYP', 'KOS_ID');
    }

    public function kostentraegerKaufvertraege()
    {
        return BelongsToMorph::build($this, Kaufvertraege::class, 'kostentraegerKaufvertrag', 'KOS_TYP', 'KOS_ID');
    }

    public function kostentraegerMietvertraege()
    {
        return BelongsToMorph::build($this, Mietvertraege::class, 'kostentraegerMietvertrag', 'KOS_TYP', 'KOS_ID');
    }

    public function kostentraegerWirtschaftseinheiten()
    {
        return BelongsToMorph::build($this, Wirtschaftseinheiten::class, 'kostentraegerWirtschaftseinheit', 'KOS_TYP', 'KOS_ID');
    }

    public function kostentraegerObjekte()
    {
        return BelongsToMorph::build($this, Objekte::class, 'kostentraegerObjekt', 'KOS_TYP', 'KOS_ID');
    }

    public function kostentraegerHaeuser()
    {
        return BelongsToMorph::build($this, Haeuser::class, 'kostentraegerHaus', 'KOS_TYP', 'KOS_ID');
    }

    public function kostentraegerEinheiten()
    {
        return BelongsToMorph::build($this, Einheiten::class, 'kostentraegerEinheit', 'KOS_TYP', 'KOS_ID');
    }
}

