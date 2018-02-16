<?php

namespace App\Http\Requests\Legacy;


use App\Http\Requests\Request;
use Auth;

class BuchenRequest extends Request
{
    public function authorize() {
        return check_user_mod(Auth::user()->id, 'buchen');
    }

    public function rules() {
        return [];
    }
}