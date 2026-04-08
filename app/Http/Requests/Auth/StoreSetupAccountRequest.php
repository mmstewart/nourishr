<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class StoreSetupAccountRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => ['required', 'max:255', 'blasp_check'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique(User::class, 'email'),
            ],
            'password' => ['required', 'confirmed', Password::min(10)->numbers()->symbols()],
            'password_confirmation' => ['required'],
        ];
    }
}
