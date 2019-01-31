<?php

namespace App\Models;

use App\Models\Traits\CopyObject;
use App\Models\Traits\DefaultOrder;
use App\Models\Traits\ExternalKey;
use App\Models\Traits\Searchable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Objekte extends Model
{
    use Searchable, DefaultOrder, CopyObject, ExternalKey;

    public $timestamps = false;
    protected $table = 'OBJEKT';
    protected $primaryKey = 'OBJEKT_DAT';
    protected $externalKey = 'id';
    protected $searchableFields = ['OBJEKT_KURZNAME'];
    protected $defaultOrder = ['OBJEKT_KURZNAME' => 'asc'];
    protected $fillable = ['OBJEKT_KURZNAME', 'EIGENTUEMER_PARTNER'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('aktuell', function (Builder $builder) {
            $builder->where('OBJEKT_AKTUELL', '1');
        });
        static::addGlobalScope('appendDetails', function (Builder $builder) {
            $builder->with('hinweise');
        });
    }

    public function haeuser()
    {
        return $this->hasMany(Haeuser::class, 'OBJEKT_ID', 'id');
    }

    public function einheiten()
    {
        return $this->hasManyThrough(
            Einheiten::class, Haeuser::class,
            'OBJEKT_ID', 'HAUS_ID', 'id', 'id'
        )->whereHas('haus', function ($query) {
            $query->where('HAUS_AKTUELL', '1');
        })->distinct();
    }

    public function mieter($date = null)
    {
        if (is_null($date)) {
            $date = Carbon::today();
        }
        return Person::whereHas('mietvertraege', function ($query) use ($date) {
            $query->whereHas('einheit.haus.objekt', function ($query) {
                $query->where('id', $this->id);
            })->active('=', $date);
        });
    }

    public function WEGEigentuemer($date = null)
    {
        if (is_null($date)) {
            $date = Carbon::today();
        }
        return Person::whereHas('kaufvertraege', function ($query) use ($date) {
            $query->whereHas('einheit.haus.objekt', function ($query) {
                $query->where('id', $this->id);
            })->active('=', $date);
        });
    }

    public function auftraege()
    {
        return $this->morphMany(Auftraege::class, 'kostentraeger', 'KOS_TYP', 'KOS_ID', 'id');
    }

    public function commonDetails()
    {
        return $this->details()->whereNotIn('DETAIL_NAME', ['Hinweis_zum_Objekt']);
    }

    public function details()
    {
        return $this->morphMany('App\Models\Details', 'details', 'DETAIL_ZUORDNUNG_TABELLE', 'DETAIL_ZUORDNUNG_ID', 'id');
    }

    public function hinweise()
    {
        return $this->details()->where('DETAIL_NAME', 'Hinweis_zum_Objekt');
    }

    public function hasHinweis()
    {
        return $this->hinweise->count() > 0;
    }

    public function eigentuemer()
    {
        return $this->belongsTo(Partner::class, 'EIGENTUEMER_PARTNER', 'id');
    }

    public function bankkonten()
    {
        return $this->belongsToMany(Bankkonten::class, 'GELD_KONTEN_ZUWEISUNG', 'KOSTENTRAEGER_ID', 'KONTO_ID', 'id', 'KONTO_ID')->wherePivot('KOSTENTRAEGER_TYP', 'Objekt')->wherePivot('AKTUELL', '1');
    }

    public function getWohnflaecheAttribute()
    {
        $flaeche = Einheiten::whereHas('haus.objekt', function ($query) {
            $query->where('id', $this->id);
        })->whereIn('TYP', ['Wohnraum', 'Wohneigentum'])->sum('EINHEIT_QM');
        return isset($flaeche) ? $flaeche : 0;
    }

    public function getGewerbeflaecheAttribute()
    {
        $flaeche = Einheiten::whereHas('haus.objekt', function ($query) {
            $query->where('id', $this->id);
        })->where('TYP', 'Gewerbe')->sum('EINHEIT_QM');
        return isset($flaeche) ? $flaeche : 0;
    }

    public function getHomeOwnersEMailsAttribute()
    {
        $emails = Details::where('DETAIL_NAME', 'Email')->whereHas('detailablePerson.kaufvertraege.einheit.haus.objekt', function ($query) {
            $query->where('id', $this->id);
        })->get();
        return $emails;
    }

    public function getTenantsEMailsAttribute()
    {
        $emails = Details::where('DETAIL_NAME', 'Email')->whereHas('detailablePerson.mietvertraege.einheit.haus.objekt', function ($query) {
            $query->where('id', $this->id);
        })->get();
        return $emails;
    }

    public function getHomeOwnersAttribute()
    {
        return $this->WEGEigentuemer()->defaultOrder()->get();
    }

    public function getTenantsAttribute()
    {
        return $this->mieter()->defaultOrder()->get();
    }
}
