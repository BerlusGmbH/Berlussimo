<?php

namespace App\Http\Requests\Modules\Person;


use App\Http\Requests\Legacy\PersonenRequest;
use App\Models\Person;
use Illuminate\Validation\ValidationException;

class StorePersonRequest extends PersonenRequest
{
    public function rules()
    {
        return [
            'name' => 'required|max:255',
            'first_name' => 'nullable|max:255',
            'sex' => 'nullable|in:männlich,weiblich',
            'birthday' => 'nullable|date',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|phone:AUTO,DE',
            'accept_dublicates' => 'nullable|in:on'
        ];
    }

    public function withValidator($validator)
    {
        if ($validator->passes()) {
            $validator->after(function ($validator) {
                if (key_exists('accept_dublicates', $validator->valid())) {
                    return;
                }
                $persons = Person::where('name', request()->input('name'))
                    ->where('first_name', request()->input('first_name'))
                    ->get();
                if (!$persons->isEmpty()) {
                    session()->flash('dublicates', $persons);
                    throw new ValidationException($validator);
                }
            });
        }
    }
}