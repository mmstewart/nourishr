<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    public function rules()
    {
        return [
            'current_password' => ['required', 'current_password'],
            'new_password' => [
                'required',
                'confirmed',
                'different:current_password',
                Password::min(10)->numbers()->symbols()
            ],
        ];
    }
}
