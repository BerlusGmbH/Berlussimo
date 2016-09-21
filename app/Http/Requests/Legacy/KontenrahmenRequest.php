<?php

namespace App\Http\Requests\Legacy;


use App\Http\Requests\Request;
use Auth;

class KontenrahmenRequest extends Request
{
    public function authorize() {
        return check_user_mod(Auth::user()->id, 'kontenrahmen');
    }

    public function rules() {
        return [];
    }
}