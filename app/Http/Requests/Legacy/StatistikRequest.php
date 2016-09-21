<?php

namespace App\Http\Requests\Legacy;


use App\Http\Requests\Request;
use Auth;

class StatistikRequest extends Request
{
    public function authorize() {
        return check_user_mod(Auth::user()->id, 'statistik');
    }

    public function rules() {
        return [];
    }
}