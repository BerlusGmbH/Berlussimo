<?php

namespace App\Models;

use App\Models\Scopes\AktuellScope;
use App\Models\Traits\DefaultOrder;
use App\Models\Traits\ExternalKey;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Partner extends Model
{
    use Searchable;
    use DefaultOrder;
    use ExternalKey;

    public $timestamps = false;
    protected $table = 'PARTNER_LIEFERANT';
    protected $primaryKey = 'PARTNER_DAT';
    protected $externalKey = 'id';
    protected $searchableFields = ['PARTNER_NAME', 'STRASSE', 'NUMMER', 'PLZ', 'ORT'];
    protected $defaultOrder = ['PARTNER_NAME' => 'asc'];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AktuellScope());
    }

    public function jobsAsEmployer()
    {
        return $this->hasMany(Job::class, 'employer_id');
    }

    public function availableJobTitles(): HasMany
    {
        return $this->hasMany(JobTitle::class, 'employer_id', 'id');
    }

    public function arbeitnehmer(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'jobs', 'employer_id', 'employee_id', 'id');
    }

    public function bankaccounts(): BelongsToMany
    {
        return $this->belongsToMany(Bankkonten::class, 'GELD_KONTEN_ZUWEISUNG', 'KOSTENTRAEGER_ID', 'KONTO_ID', 'id', 'KONTO_ID')->wherePivot('KOSTENTRAEGER_TYP', 'Partner')->wherePivot('AKTUELL', '1');
    }

    public function details(): MorphMany
    {
        return $this->morphMany('App\Models\Details', 'details', 'DETAIL_ZUORDNUNG_TABELLE', 'DETAIL_ZUORDNUNG_ID', 'id');
    }

    public function emails(): MorphMany
    {
        return $this->details()->where('DETAIL_NAME', 'Email');
    }

    public function faxs(): MorphMany
    {
        return $this->details()->where('DETAIL_NAME', 'Fax');
    }

    public function phones(): MorphMany
    {
        return $this->details()->whereIn('DETAIL_NAME', ['Handy', 'Tel.', 'Telefon']);
    }

    public function rechtsvertreter(): MorphMany
    {
        return $this->details()->where('DETAIL_NAME', 'Rechtsvertreter');
    }

    public function handelsregister(): MorphMany
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
