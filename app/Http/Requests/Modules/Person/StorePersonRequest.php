<?php

namespace App\Http\Requests\Modules\Person;


use App\Http\Requests\Legacy\PersonenRequest;

class StorePersonRequest extends PersonenRequest
{
    public function rules()
    {
        return [
            'name' => 'required|alpha|max:255',
            'first_name' => 'max:255',
            'sex' => 'filled',
            'birthday' => 'date',
            'email' => 'email|max:255',
            'phone' => 'phone:AUTO,DE'
        ];
    }

}