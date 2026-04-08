<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class StoreSetupProfileRequest extends FormRequest
{
    public function rules()
    {
        return [
            // todo
            'dob' => [
                'required',
                'date',
                'before:' . now()->subYears(13)->format('Y-m-d'),
                'after:1900-01-01',
            ],
            'feet' => ['required', 'integer', 'min:1', 'max:8'],
            'inches' => ['required', 'integer', 'min:0', 'max:11'],
            'height_inches' => ['required', 'integer'],
            'gender' => ['required'],
            'country_code' => ['required'],
            'timezone' => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'dob.before' => 'You must be at least 13 years old to use Nourishr.',
            'dob.after' => 'Please enter a valid birth date.',
        ];
    }
}
