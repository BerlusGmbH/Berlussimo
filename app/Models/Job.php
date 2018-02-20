<?php

namespace App\Models;

use App\Models\Contracts\Active as ActiveContract;
use App\Models\Traits\Active;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model implements ActiveContract
{
    use SoftDeletes;
    use Active;

    public $timestamps = true;
    protected $table = 'jobs';
    protected $guarded = [];
    protected $appends = ['type'];

    static public function getTypeAttribute()
    {
        return 'job';
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('employer_and_title', function (Builder $builder) {
            $builder->with(['employer', 'title']);
        });
    }

    public function employer()
    {
        return $this->hasOne(Partner::class, 'PARTNER_ID', 'employer_id');
    }

    public function employee()
    {
        return $this->hasOne(Person::class, 'id', 'employee_id');
    }

    public function title()
    {
        return $this->belongsTo(JobTitle::class, 'job_title_id');
    }

    public function getStartDateFieldName()
    {
        return 'join_date';
    }

    public function getEndDateFieldName()
    {
        return 'leave_date';
    }
}
