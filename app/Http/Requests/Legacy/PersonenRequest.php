<?php

namespace App\Http\Requests\Legacy;


use App\Http\Requests\Request;
use Auth;

class PersonenRequest extends Request
{
    public function authorize() {
        return check_user_mod(Auth::user()->id, 'person');
    }

    public function rules() {
        return [];
    }
}