<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AskCoachRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'min:3', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'question.required' => 'Ask the coach a question first.',
            'question.max' => 'Keep your question under 500 characters.',
        ];
    }
}
