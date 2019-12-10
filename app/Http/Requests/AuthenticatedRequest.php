<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class AuthenticatedRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() ? true : false;
    }

    public function rules()
    {
        return [];
    }
}