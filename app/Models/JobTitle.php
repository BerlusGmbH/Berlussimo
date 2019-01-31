<?php

namespace App\Models;

use App\Models\Traits\DefaultOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function employer(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'employer_id', 'id');
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'jobs', 'employee_id', 'job_title_id', 'id', 'id');
    }
}
