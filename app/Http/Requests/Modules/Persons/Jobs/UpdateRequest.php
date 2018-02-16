<?php

namespace App\Http\Requests\Modules\Persons\Jobs;


use App\Http\Requests\Legacy\PersonenRequest;

class UpdateRequest extends PersonenRequest
{
    public function rules()
    {
        return [
            'job_title_id' => 'required|exists:job_titles,id',
            'join_date' => 'required|date',
            'leave_date' => 'nullable|date|after:join_date',
            'hours_per_week' => 'nullable|numeric',
            'holidays' => 'required|numeric',
            'hourly_rate' => 'nullable|numeric'
        ];
    }
}