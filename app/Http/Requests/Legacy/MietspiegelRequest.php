<?php

namespace App\Http\Requests\Legacy;


use App\Http\Requests\Request;
use Auth;

class MietspiegelRequest extends Request
{
    public function authorize() {
        return check_user_mod(Auth::user()->id, 'mietspiegel');
    }

    public function rules() {
        return [];
    }
}