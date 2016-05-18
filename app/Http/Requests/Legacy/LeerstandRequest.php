<?php

namespace App\Http\Requests\Legacy;


use App\Http\Requests\Request;
use Auth;

class LeerstandRequest extends Request
{
    public function authorize() {
        return check_user_mod(Auth::user()->id, 'leerstand');
    }

    public function rules() {
        return [];
    }
}