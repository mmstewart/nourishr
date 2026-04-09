<?php

namespace App\Http\Requests\Settings;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => ['required', 'max:255', 'blasp_check'],
            'dob' => [
                'required',
                'date',
                'after:1900-01-01',
                'before:' . now()->subYears(13)->format('Y-m-d'),
            ],
            'feet' => ['required', 'integer', 'min:1', 'max:8'],
            'inches' => ['required', 'integer', 'min:0', 'max:11'],
            'height_inches' => ['required', 'integer'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'gender' => ['required'],
            'country_code' => ['required'],
            'timezone' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'dob.required' => 'Please enter your date of birth.',
            'dob.after' => 'Please enter a valid birth date.',
            'dob.before' => 'You must be at least 13 years old to use Nourishr.',
        ];
    }
}
