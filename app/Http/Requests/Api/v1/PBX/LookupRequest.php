<?php

namespace App\Http\Requests\Api\v1\PBX;


use Illuminate\Foundation\Http\FormRequest;

class LookupRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [];
    }
}