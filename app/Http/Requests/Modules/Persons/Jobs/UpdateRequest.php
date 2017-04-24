<?php

namespace App\Http\Requests\Modules\Persons\Jobs;


use App\Http\Requests\Legacy\PersonenRequest;
use Route;

class UpdateRequest extends PersonenRequest
{
    public function rules()
    {
        return [
            'title' => 'required|exists:job_titles,title',
            'join_date' => 'required|date',
            'leave_date' => 'nullable|date|after:join_date',
            'hours_per_week' => 'nullable|numeric',
            'holidays' => 'required|numeric',
            'hourly_rate' => 'nullable|numeric'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!empty($validator->failed())) {
                session()->flash('job_edit_' . Route::current()->parameter('job')->id, null);
            }
        });
    }
}