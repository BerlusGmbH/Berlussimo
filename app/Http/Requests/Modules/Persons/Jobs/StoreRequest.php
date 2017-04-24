<?php

namespace App\Http\Requests\Modules\Persons\Jobs;


use App\Http\Requests\Legacy\PersonenRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends PersonenRequest
{
    public function rules()
    {
        return [
            'employer' => ['required', Rule::exists('PARTNER_LIEFERANT', 'PARTNER_NAME', function ($query) {
                $query->orWhere('PARTNER_AKTUELL', '1');
            })],
            'title' => 'required|exists:job_titles,title',
            'join_date' => 'required|date',
            'leave_date' => 'nullable|date|after:join_date',
            'hours_per_week' => 'nullable|numeric',
            'holidays' => 'required|numeric',
            'hourly_rate' => 'nullable|numeric'
        ];
    }
}