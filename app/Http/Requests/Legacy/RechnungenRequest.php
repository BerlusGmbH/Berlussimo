<?php

namespace App\Http\Requests\Legacy;


use App\Http\Requests\Request;
use Auth;

class RechnungenRequest extends Request
{
    public function authorize() {
        return check_user_mod(Auth::user()->id, 'rechnungen');
    }

    public function rules() {
        return [];
    }
}