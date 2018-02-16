<?php

namespace App\Http\Requests\Legacy;


use App\Http\Requests\Request;
use Auth;

class ToDoRequest extends Request
{
    public function authorize() {
        return check_user_mod(Auth::user()->id, 'todo');
    }

    public function rules() {
        return [];
    }
}