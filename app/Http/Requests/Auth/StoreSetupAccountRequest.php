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
            'age' => ['required', 'integer', 'min:13', 'max:120'],
            'feet' => ['required', 'integer', 'min:1', 'max:8'],
            'inches' => ['required', 'integer', 'min:0', 'max:11'],
            'height_inches' => ['required', 'integer'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'gender' => ['required'],
            'country_code' => ['required', 'string'],
            'timezone' => ['required', 'string'],
            'password' => ['required', Password::min(10)->numbers()->symbols()],
            'password_confirmation' => ['required', 'same:password'],
        ];
    }
}
