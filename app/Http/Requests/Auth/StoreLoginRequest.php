<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Email;

class StoreLoginRequest extends FormRequest
{
    public function rules()
    {
        return [
            'email' => ['required', 'lowercase', Email::default(), 'max:255'],
            'password' => ['required'],
        ];
    }
}
