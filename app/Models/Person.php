<?php

namespace App\Models;

use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\Permission\Traits\HasRoles;

class Person extends Authenticatable implements AuditableContract
{
    use Searchable;
    use DefaultOrder;
    use SoftDeletes;
    use Auditable;
    use HasRoles;

    protected $table = 'persons';
    protected $searchableFields = ['name', 'first_name'];
    protected $defaultOrder = ['name' => 'asc', 'first_name' => 'asc', 'birthday' => 'asc'];
    protected $dates = ['birthday', 'created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['name', 'first_name', 'birthday'];

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

    public function emails()
    {
        return $this->details()->where('DETAIL_NAME', 'Email');
    }

    public function details()
    {
        return $this->morphMany('App\Models\Details', 'details', 'DETAIL_ZUORDNUNG_TABELLE', 'DETAIL_ZUORDNUNG_ID');
    }

    public function faxs()
    {
        return $this->details()->where('DETAIL_NAME', 'Fax');
    }

    public function phones()
    {
        return $this->details()->whereIn('DETAIL_NAME', ['Telefon', 'Handy']);
    }

    public function sex()
    {
        return $this->details()->where('DETAIL_NAME', 'Geschlecht');
    }

    public function hinweise()
    {
        return $this->details()->where('DETAIL_NAME', 'Hinweis');
    }

    public function adressen()
    {
        return $this->details()->whereIn('DETAIL_NAME', ['Zustellanschrift', 'Verzugsanschrift', 'Anschrift']);
    }

    public function commonDetails()
    {
        return $this->details()->whereNotIn('DETAIL_NAME', ['Geschlecht', 'Hinweis', 'Email', 'Fax', 'Telefon', 'Handy', 'Zustellanschrift', 'Verzugsanschrift', 'Anschrift']);
    }

    public function credential()
    {
        return $this->hasOne(Credential::class, 'id');
    }

    public function jobsAsEmployee()
    {
        return $this->hasMany(Job::class, 'employee_id');
    }

    public function arbeitgeber()
    {
        return $this->belongsToMany(Partner::class, 'jobs', 'employee_id', 'employer_id');
    }

    public function hasHinweis()
    {
        return $this->hinweise->count() > 0;
    }

    public function getFullNameAttribute()
    {
        $full_name = '';
        if (!empty($this->name))
            $full_name .= $this->name;
        if (!empty($this->name) && !empty($this->first_name))
            $full_name .= ', ';
        if (!empty($this->first_name))
            $full_name .= $this->first_name;
        return $full_name;
    }

    public function getAddressNameAttribute()
    {
        $full_name = '';
        if ($this->sex[0]['DETAIL_INHALT'] == 'männlich')
            $full_name .= 'Herr ';
        if ($this->sex[0]['DETAIL_INHALT'] == 'weiblich')
            $full_name .= 'Frau ';
        if (!empty($this->first_name))
            $full_name .= $this->first_name;
        if (!empty($this->name) && !empty($this->first_name))
            $full_name .= ' ';
        if (!empty($this->name))
            $full_name .= $this->name;
        return $full_name;
    }

    public function getPrettyNameAttribute()
    {
        $full_name = '';
        if (!empty($this->first_name))
            $full_name .= $this->first_name;
        if (!empty($this->name) && !empty($this->first_name))
            $full_name .= ' ';
        if (!empty($this->name))
            $full_name .= $this->name;
        return $full_name;
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        if ($this->hasCredential()) {
            return $this->credential->password;
        }
        return null;
    }

    public function hasCredential()
    {
        return !is_null($this->credential);
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken()
    {
        if (!empty($this->getRememberTokenName()) && $this->hasCredential()) {
            return $this->credential->{$this->getRememberTokenName()};
        }
        return null;
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string $value
     * @return void
     */
    public function setRememberToken($value)
    {
        if (!empty($this->getRememberTokenName())) {
            $credential = $this->credential;
            $credential->{$this->getRememberTokenName()} = $value;
        }
    }
}
