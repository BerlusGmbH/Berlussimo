<?php

namespace App\Http\Requests\Legacy;


use App\Http\Requests\Request;
use Auth;

class LagerRequest extends Request
{
    public function authorize() {
        return check_user_mod(Auth::user()->id, 'lager');
    }

    public function rules() {
        return [];
    }
}