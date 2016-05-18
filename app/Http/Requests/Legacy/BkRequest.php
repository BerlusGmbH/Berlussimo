<?php

namespace App\Http\Requests\Legacy;


use App\Http\Requests\Request;
use Auth;

class BkRequest extends Request
{
    public function authorize() {
        return check_user_mod(Auth::user()->id, 'bk');
    }

    public function rules() {
        return [];
    }
}