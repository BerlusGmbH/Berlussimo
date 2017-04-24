<?php

namespace App\Http\Requests\Modules\Persons\Credentials;


use App\Http\Requests\Legacy\PersonenRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateRequest extends PersonenRequest
{
    public function rules()
    {
        return [
            'password' => 'max:255',
            'roles.*' => [Rule::in(Role::all()->pluck('name')->all())]
        ];
    }
}