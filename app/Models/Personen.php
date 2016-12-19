<?php

namespace App\Models;

use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Personen extends Model
{
    use Searchable;

    public $timestamps = false;
    protected $table = 'PERSON';
    protected $primaryKey = 'PERSON_ID';
    protected $searchableFields = ['PERSON_VORNAME', 'PERSON_NACHNAME'];
    protected $dates = ['PERSON_GEBURTSTAG'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('aktuell', function (Builder $builder) {
            $builder->where('PERSON_AKTUELL', '1');
        });
    }

    public function mietvertraege()
    {
        return $this->belongsToMany('App\Models\Mietvertraege',
            'PERSON_MIETVERTRAG',
            'PERSON_MIETVERTRAG_PERSON_ID',
            'PERSON_MIETVERTRAG_MIETVERTRAG_ID'
        )->wherePivot('PERSON_MIETVERTRAG_AKTUELL', '=', '1');
    }

    public function kaufvertraege()
    {
        return $this->belongsToMany('App\Models\Kaufvertraege',
            'WEG_EIGENTUEMER_PERSON',
            'PERSON_ID',
            'WEG_EIG_ID'
        )->wherePivot('AKTUELL', '=', '1');
    }

    public function details()
    {
        return $this->morphMany('App\Models\Details', 'details', 'DETAIL_ZUORDNUNG_TABELLE', 'DETAIL_ZUORDNUNG_ID');
    }

    public function emails() {
        return $this->details()->where('DETAIL_NAME', 'Email');
    }

    public function faxs() {
        return $this->details()->where('DETAIL_NAME', 'Fax');
    }

    public function phones() {
        return $this->details()->whereIn('DETAIL_NAME', ['Telefon','Handy']);
    }

    public function sex() {
        return $this->details()->where('DETAIL_NAME', 'Geschlecht');
    }

    public function anschriften() {
        return $this->details()->whereIn('DETAIL_NAME', ['Zustellanschrift', 'Verzugsanschrift', 'Anschrift']);
    }

    public function commonDetails() {
        return $this->details()->whereNotIn('DETAIL_NAME', ['Geschlecht', 'Email', 'Fax', 'Telefon', 'Handy', 'Zustellanschrift', 'Verzugsanschrift', 'Anschrift']);
    }
}
