<?php

namespace App\Http\Requests\Modules\Persons;


use App\Http\Requests\Legacy\PersonenRequest;

class UpdateRequest extends PersonenRequest
{
    public function rules()
    {
        return [
            'name' => 'required|max:255',
            'first_name' => 'nullable|max:255',
            'birthday' => 'nullable|date',
        ];
    }
}