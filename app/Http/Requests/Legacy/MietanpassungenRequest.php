<?php

namespace App\Http\Requests\Legacy;


use App\Http\Requests\Request;
use Auth;

class MietanpassungenRequest extends Request
{
    public function authorize() {
        return check_user_mod(Auth::user()->id, 'mietanpassung');
    }

    public function rules() {
        return [];
    }
}