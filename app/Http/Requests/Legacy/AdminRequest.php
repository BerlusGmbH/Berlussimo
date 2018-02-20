<?php

namespace App\Http\Requests\Legacy;


use App\Libraries\Permission;
use Auth;
use Illuminate\Foundation\Http\FormRequest;

class AdminRequest extends FormRequest
{
    public function authorize() {
        return Auth::user()->can(Permission::PERMISSION_MODUL_DETAIL);
    }

    public function rules() {
        return [];
    }
}