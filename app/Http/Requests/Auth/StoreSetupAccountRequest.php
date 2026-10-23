<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Email;
use Illuminate\Validation\Rules\Password;

class StoreSetupAccountRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => ['required', 'max:255', 'blasp_check'],
            'email' => [
                'required',
                'lowercase',
                Email::default(),
                'max:255',
                Rule::unique(User::class, 'email'),
            ],
            'password' => ['required', Password::min(10)->numbers()->symbols(), 'confirmed'],
        ];
    }
}
