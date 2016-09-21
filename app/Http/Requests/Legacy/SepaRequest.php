<?php

namespace App\Http\Requests\Legacy;


use App\Http\Requests\Request;
use Auth;

class SepaRequest extends Request
{
    public function authorize() {
        return check_user_mod(Auth::user()->id, 'sepa');
    }

    public function rules() {
        return [];
    }
}