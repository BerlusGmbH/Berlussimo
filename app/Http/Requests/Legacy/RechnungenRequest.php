<?php

namespace App\Http\Requests\Legacy;


use App\Libraries\Permission;
use Auth;
use Illuminate\Foundation\Http\FormRequest;

class RechnungenRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::user()->can(Permission::PERMISSION_MODUL_RECHNUNG);
    }

    public function rules()
    {
        return [];
    }
}