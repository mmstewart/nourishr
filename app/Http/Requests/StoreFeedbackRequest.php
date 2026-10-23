<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Email;

class StoreFeedbackRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => ['required', 'max:255', 'blasp_check'],
            'email' => ['required', 'lowercase', Email::default(), 'max:255'],
            'message' => ['required', 'min:10'],
        ];
    }
}
