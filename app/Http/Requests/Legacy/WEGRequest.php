<?php

namespace App\Http\Requests\Legacy;


use App\Http\Requests\Request;
use Auth;

class WEGRequest extends Request
{
    public function authorize() {
        return check_user_mod(Auth::user()->id, 'weg');
    }

    public function rules() {
        return [];
    }
}