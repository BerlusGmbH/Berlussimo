<?php

namespace App\Http\Requests\Legacy;


use App\Http\Requests\Request;
use Auth;

class UebersichtRequest extends Request
{
    public function authorize() {
        return check_user_mod(Auth::user()->id, 'uebersicht');
    }

    public function rules() {
        return [];
    }
}