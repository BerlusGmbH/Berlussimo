<?php

namespace App\Http\Requests\Legacy;


use App\Http\Requests\Request;
use Auth;

class DbBackupRequest extends Request
{
    public function authorize() {
        return check_user_mod(Auth::user()->id, 'dbbackup');
    }

    public function rules() {
        return [];
    }
}