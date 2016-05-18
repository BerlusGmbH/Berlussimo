<?php

namespace App\Http\Requests\Legacy;


use App\Http\Requests\Request;
use Auth;

class ZeiterfassungRequest extends Request
{
    public function authorize() {
        return check_user_mod(Auth::user()->id, 'zeiterfassung');
    }

    public function rules() {
        return [];
    }
}