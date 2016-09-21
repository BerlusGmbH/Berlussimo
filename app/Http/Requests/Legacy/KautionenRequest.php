<?php

namespace App\Http\Requests\Legacy;


use App\Http\Requests\Request;
use Auth;

class KautionenRequest extends Request
{
    public function authorize() {
        return check_user_mod(Auth::user()->id, 'kautionen');
    }

    public function rules() {
        return [];
    }
}