<?php

namespace App\Http\Requests\Legacy;


use App\Http\Requests\Request;
use Auth;

class KatalogRequest extends Request
{
    public function authorize() {
        return check_user_mod(Auth::user()->id, 'katalog');
    }

    public function rules() {
        return [];
    }
}