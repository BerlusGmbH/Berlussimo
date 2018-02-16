<?php

namespace App\Http\Requests\Legacy;


use App\Http\Requests\Request;
use Auth;

class UrlaubRequest extends Request
{
    public function authorize() {
        return check_user_mod(Auth::user()->id, 'urlaub');
    }

    public function rules() {
        return [];
    }
}