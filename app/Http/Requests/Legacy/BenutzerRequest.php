<?php

namespace App\Http\Requests\Legacy;


use App\Http\Requests\Request;
use Auth;

class BenutzerRequest extends Request
{
    public function authorize() {
        return check_user_mod(Auth::user()->id, 'benutzer');
    }

    public function rules() {
        return [];
    }
}