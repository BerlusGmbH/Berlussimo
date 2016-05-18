<?php

namespace App\Http\Requests\Legacy;


use App\Http\Requests\Request;
use Auth;

class GeldkontenRequest extends Request
{
    public function authorize() {
        return check_user_mod(Auth::user()->id, 'geldkonten');
    }

    public function rules() {
        return [];
    }
}