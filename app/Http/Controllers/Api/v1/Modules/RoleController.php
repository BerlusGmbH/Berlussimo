<?php

namespace App\Http\Controllers\Api\v1\Modules;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Persons\Jobs\StoreRequest;
use App\Http\Requests\Modules\Persons\Jobs\UpdateRequest;
use App\Models\Job;
use App\Models\Person;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        return response()->json(Role::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        $job = $request->only([
            'employee_id',
            'employer_id',
            'job_title_id',
            'join_date',
            'leave_date',
            'hours_per_week',
            'holidays',
            'hourly_rate'
        ]);
        return response()->json(Job::create($job));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateRequest $request
     * @param  \App\Models\Person $person
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRequest $request, Person $person, Job $job)
    {
        $job_request = $request->only([
            'job_title_id', 'join_date', 'leave_date', 'hours_per_week', 'holidays', 'hourly_rate'
        ]);

        return response()->json($job->update($job_request));
    }
}
