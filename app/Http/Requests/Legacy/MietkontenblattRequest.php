<?php

namespace App\Http\Requests\Legacy;


use App\Http\Requests\Request;
use Auth;

class MietkontenblattRequest extends Request
{
    public function authorize() {
        return check_user_mod(Auth::user()->id, 'mietkonten_blatt');
    }

    public function rules() {
        return [];
    }
}