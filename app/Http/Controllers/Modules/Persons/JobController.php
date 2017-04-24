<?php

namespace App\Http\Controllers\Modules\Persons;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Persons\Jobs\StoreRequest;
use App\Http\Requests\Modules\Persons\Jobs\UpdateRequest;
use App\Models\Job;
use App\Models\JobTitle;
use App\Models\Partner;
use App\Models\Person;

class JobController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreRequest $request
     * @param  Person $person
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request, Person $person)
    {
        $job = $request->only([
            'join_date', 'leave_date', 'hours_per_week', 'holidays', 'hourly_rate'
        ]);
        $employer = Partner::where('PARTNER_NAME', $request->input('employer'))->first(['PARTNER_ID']);
        $job_title = JobTitle::where('title', $request->input('title'))->first(['id']);
        $job['employer_id'] = $employer->PARTNER_ID;
        $job['job_title_id'] = $job_title->id;
        $person->jobsAsEmployee()->create($job);
        return redirect()->back();
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
            'join_date', 'leave_date', 'hours_per_week', 'holidays', 'hourly_rate'
        ]);
        $job_title = JobTitle::where('title', $request->input('title'))->first(['id']);
        $job_request['job_title_id'] = $job_title->id;
        $job->update($job_request);
        return redirect()->back();
    }
}
