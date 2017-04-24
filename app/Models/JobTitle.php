<?php

namespace App\Models;

use App\Models\Traits\DefaultOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobTitle extends Model
{
    use SoftDeletes;
    use DefaultOrder;

    public $timestamps = true;
    protected $table = 'job_titles';
    protected $searchableFields = ['title'];
    protected $defaultOrder = ['title' => 'asc'];
    protected $guarded = [];

    public function employer()
    {
        return $this->hasOne(Partner::class, 'employer_id');
    }

    public function employee()
    {
        return $this->hasOne(Person::class, 'employee_id');
    }
}
