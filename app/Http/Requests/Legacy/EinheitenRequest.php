<?php

namespace App\Http\Requests\Legacy;


use App\Http\Requests\Request;
use Auth;

class EinheitenRequest extends Request
{
    public function authorize() {
        return check_user_mod(Auth::user()->id, 'einheit_raus');
    }

    public function rules() {
        return [];
    }
}