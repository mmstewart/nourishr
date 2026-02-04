<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'max:255', 'blasp_check'],
            'age' => ['required', 'integer', 'min:13', 'max:120'],
            'feet' => ['required', 'integer', 'min:1', 'max:8'],
            'inches' => ['required', 'integer', 'min:0', 'max:11'],
            'height_inches' => ['required', 'integer'],
            // todo, work on email validation rules: email:rfc,dns,spoof
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'gender' => ['required'],
            'country_code' => ['required', 'string'],
            'timezone' => ['required', 'string'],
            'password' => ['required', Password::min(10)->numbers()->symbols()],
            'password_confirmation' => ['required', 'same:password'],
        ];
    }
}
