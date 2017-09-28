<?php

namespace App\Http\Requests\Modules\Persons\Jobs;


use App\Http\Requests\Legacy\PersonenRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends PersonenRequest
{
    public function rules()
    {
        return [
            'employer_id' => ['required', Rule::exists('PARTNER_LIEFERANT', 'PARTNER_ID', function ($query) {
                $query->where('PARTNER_AKTUELL', '1');
            })],
            'employee_id' => 'required|exists:persons,id',
            'job_title_id' => 'required|exists:job_titles,id',
            'join_date' => 'required|date',
            'leave_date' => 'nullable|date|after:join_date',
            'hours_per_week' => 'nullable|numeric',
            'holidays' => 'required|numeric',
            'hourly_rate' => 'nullable|numeric'
        ];
    }
}