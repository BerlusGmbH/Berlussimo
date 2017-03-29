<?php

namespace App\Models;

use App\Models\Traits\Searchable;
use App\Models\Traits\DefaultOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Person extends Model implements AuditableContract
{
    use Searchable;
    use DefaultOrder;
    use SoftDeletes;
    use Auditable;

    protected $table = 'persons';
    protected $searchableFields = ['name', 'first_name'];
    protected $defaultOrder = ['name' => 'asc', 'first_name' => 'asc', 'birthday' => 'asc'];
    protected $dates = ['birthday', 'created_at', 'updated_at', 'deleted_at'];

    public function mietvertraege()
    {
        return $this->belongsToMany('App\Models\Mietvertraege',
            'PERSON_MIETVERTRAG',
            'PERSON_MIETVERTRAG_PERSON_ID',
            'PERSON_MIETVERTRAG_MIETVERTRAG_ID'
        )->wherePivot('PERSON_MIETVERTRAG_AKTUELL', '1');
    }

    public function kaufvertraege()
    {
        return $this->belongsToMany('App\Models\Kaufvertraege',
            'WEG_EIGENTUEMER_PERSON',
            'PERSON_ID',
            'WEG_EIG_ID'
        )->wherePivot('AKTUELL', '1');
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

    public function hinweise() {
        return $this->details()->where('DETAIL_NAME', 'Hinweis');
    }

    public function adressen() {
        return $this->details()->whereIn('DETAIL_NAME', ['Zustellanschrift', 'Verzugsanschrift', 'Anschrift']);
    }

    public function commonDetails() {
        return $this->details()->whereNotIn('DETAIL_NAME', ['Geschlecht', 'Hinweis', 'Email', 'Fax', 'Telefon', 'Handy', 'Zustellanschrift', 'Verzugsanschrift', 'Anschrift']);
    }

    public function hasHinweis() {
        return $this->hinweise->count() > 0;
    }

    public function getFullNameAttribute() {
        $full_name = '';
        if(!empty($this->name))
            $full_name .= trim($this->name);
        if(!empty($this->name) && !empty($this->name))
            $full_name .= ', ';
        if(!empty($this->first_name))
            $full_name .= trim($this->first_name);
        return $full_name;
    }
}
