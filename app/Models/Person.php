<?php

namespace App\Models;

use App\Auditing\DateEncoder;
use App\Events\Models\PersonUpdated;
use App\Models\Traits\DefaultOrder;
use App\Models\Traits\Mergeable;
use App\Models\Traits\MergePersons;
use App\Models\Traits\Searchable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use InvalidArgumentException;
use Laravel\Passport\HasApiTokens;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\Permission\Traits\HasRoles;

class Person extends Authenticatable implements AuditableContract
{
    use Searchable, DefaultOrder, SoftDeletes, Auditable, HasRoles, Notifiable, MergePersons, Mergeable, HasApiTokens;

    public const MALE = "männlich";
    public const FEMALE = "weiblich";

    protected $table = 'persons';
    protected $searchableFields = ['name', 'first_name'];
    protected $defaultOrder = ['name' => 'asc', 'first_name' => 'asc', 'birthday' => 'asc'];
    protected $casts = ['birthday' => 'date:Y-m-d'];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $fillable = ['name', 'first_name', 'birthday', 'sex'];
    protected $appends = ['sex', 'full_name'];

    protected $attributeModifiers = [
        'birthday' => DateEncoder::class,
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('appendDetails', function (Builder $builder) {
            $builder->with(['sexDetail', 'hinweise']);
        });
    }

    public function mietvertraege(): BelongsToMany
    {
        return $this->belongsToMany(Mietvertraege::class,
            'PERSON_MIETVERTRAG',
            'PERSON_MIETVERTRAG_PERSON_ID',
            'PERSON_MIETVERTRAG_MIETVERTRAG_ID',
            'id',
            'id'
        )->using(RentalContractsToTenants::class)->where('PERSON_MIETVERTRAG_AKTUELL', '1');
    }

    public function kaufvertraege(): BelongsToMany
    {
        return $this->belongsToMany(Kaufvertraege::class,
            'WEG_EIGENTUEMER_PERSON',
            'PERSON_ID',
            'WEG_EIG_ID',
            'id',
            'id'
        )->wherePivot('AKTUELL', '1');
    }

    public function emails(): MorphMany
    {
        return $this->details()->where('DETAIL_NAME', 'Email');
    }

    public function details(): MorphMany
    {
        return $this->morphMany(
            Details::class,
            'details',
            'DETAIL_ZUORDNUNG_TABELLE',
            'DETAIL_ZUORDNUNG_ID'
        );
    }

    public function faxs(): MorphMany
    {
        return $this->details()->where('DETAIL_NAME', 'Fax');
    }

    public function phones(): MorphMany
    {
        return $this->details()->whereIn('DETAIL_NAME', ['Telefon', 'Handy']);
    }

    public function sexDetail(): MorphMany
    {
        return $this->details()->where('DETAIL_NAME', 'Geschlecht');
    }

    public function hinweise(): MorphMany
    {
        return $this->details()->where('DETAIL_NAME', 'Hinweis');
    }

    public function adressen(): MorphMany
    {
        return $this->details()->whereIn('DETAIL_NAME', ['Zustellanschrift', 'Verzugsanschrift', 'Anschrift']);
    }

    public function commonDetails(): MorphMany
    {
        return $this->details()->whereNotIn('DETAIL_NAME', ['Geschlecht', 'Hinweis', 'Email', 'Fax', 'Telefon', 'Handy', 'Zustellanschrift', 'Verzugsanschrift', 'Anschrift']);
    }

    public function credential(): HasOne
    {
        return $this->hasOne(Credential::class, 'id');
    }

    public function jobsAsEmployee(): HasMany
    {
        return $this->hasMany(Job::class, 'employee_id');
    }

    public function arbeitgeber(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'jobs', 'employee_id', 'employer_id', 'id', 'id');
    }

    public function hasHinweis()
    {
        return $this->hinweise->count() > 0;
    }

    /**
     * The channels the user receives notification broadcasts on.
     *
     * @return string
     */
    public function receivesBroadcastNotificationsOn()
    {
        return 'Notification.Person.' . $this->id;
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

    public function getSexAttribute()
    {
        return ($this->sexDetail->isNotEmpty() ? $this->sexDetail[0]['DETAIL_INHALT'] : null);
    }

    public function setSexAttribute($value)
    {
        if (!in_array($value, [Person::MALE, Person::FEMALE, null])) {
            throw new InvalidArgumentException('Value has to be one of "männlich" or "weiblich".');
        }

        if ($this->sexDetail->isNotEmpty()) {
            $this->sexDetail[0]->DETAIL_AKTUELL = '0';
            $this->sexDetail[0]->save();
        }

        if ($value) {
            if (is_null($this->getOriginal('id'))) {
                $this->save();
            }

            $this->details()->create([
                'DETAIL_NAME' => 'Geschlecht',
                'DETAIL_INHALT' => $value,
                'DETAIL_BEMERKUNG' => 'Stand: ' . Carbon::today()->toDateString()
            ]);
        }
    }

    public function getAddressNameAttribute()
    {
        $full_name = '';
        if ($this->sex == 'männlich')
            $full_name .= 'Herr ';
        if ($this->sex == 'weiblich')
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

    public function getEmailAttribute()
    {
        if (!$this->emails->isEmpty()) {
            return $this->emails[0]->DETAIL_INHALT;
        }
        return '';
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
     * @param string $value
     * @return void
     */
    public function setRememberToken($value)
    {
        if (!empty($this->getRememberTokenName())) {
            $credential = $this->credential;
            $credential->{$this->getRememberTokenName()} = $value;
        }
    }

    public function findForPassport($username)
    {
        return Person::whereHas('emails', function ($query) use ($username) {
            $query->where('DETAIL_INHALT', $username);
        })->first();
    }
}
