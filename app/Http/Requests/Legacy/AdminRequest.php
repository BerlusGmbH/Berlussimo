<?php

namespace App\Http\Requests\Legacy;


use App\Http\Requests\Request;
use Auth;

class AdminRequest extends Request
{
    public function authorize() {
        return check_user_mod(Auth::user()->id, 'admin_panel');
    }

    public function rules() {
        return [];
    }
}