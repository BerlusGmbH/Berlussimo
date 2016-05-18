<?php

namespace App\Http\Requests\Legacy;


use App\Http\Requests\Request;
use Auth;

class PersonalRequest extends Request
{
    public function authorize() {
        return check_user_mod(Auth::user()->id, 'personal');
    }

    public function rules() {
        return [];
    }
}