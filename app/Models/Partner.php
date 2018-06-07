<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use Searchable;
    use DefaultOrder;

    public $timestamps = false;
    protected $table = 'PARTNER_LIEFERANT';
    protected $primaryKey = 'PARTNER_ID';
    protected $searchableFields = ['PARTNER_NAME', 'STRASSE', 'NUMMER', 'PLZ', 'ORT'];
    protected $defaultOrder = ['PARTNER_NAME' => 'asc'];
    protected $appends = ['type'];

    static public function getTypeAttribute()
    {
        return 'partner';
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }

    public function jobsAsEmployer()
    {
        return $this->hasMany(Job::class, 'employer_id');
    }

    public function availableJobTitles()
    {
        return $this->hasMany(JobTitle::class, 'employer_id');
    }

    public function arbeitnehmer()
    {
        return $this->belongsToMany(Person::class, 'jobs', 'employer_id', 'employee_id');
    }

    public function details()
    {
        return $this->morphMany('App\Models\Details', 'details', 'DETAIL_ZUORDNUNG_TABELLE', 'DETAIL_ZUORDNUNG_ID');
    }

    public function rechtsvertreter()
    {
        return $this->details()->where('DETAIL_NAME', 'Rechtsvertreter');
    }

    public function handelsregister()
    {
        return $this->details()->where('DETAIL_NAME', 'Handelsregister');
    }

    public function getNameOneLineAttribute()
    {
        $name = $this->PARTNER_NAME;
        $name = str_replace("\r\n", ' ', $name);
        $name = str_replace("\r", ' ', $name);
        $name = str_replace("\n", ' ', $name);
        return $name;
    }
}
