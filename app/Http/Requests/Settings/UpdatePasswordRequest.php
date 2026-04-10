<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    public function rules()
    {
        $hasPassword = !is_null($this->user()->password);

        return [
            'current_password' => [
                $hasPassword ? 'required' : 'nullable',
                $hasPassword ? 'current_password' : ''
            ],
            'new_password' => [
                'required',
                Password::min(10)->numbers()->symbols(),
                $hasPassword ? 'different:current_password' : '',
                'confirmed',
            ],
        ];
    }
}
