<?php

namespace App\Http\Requests\Legacy;


use App\Libraries\Permission;
use Auth;
use Illuminate\Foundation\Http\FormRequest;

class ZeiterfassungRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::user()->can(Permission::PERMISSION_MODUL_ZEITERFASSUNG);
    }

    public function rules()
    {
        return [];
    }
}