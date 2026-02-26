<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WeightEntryRequest extends FormRequest
{
    public function rules()
    {
        return [
            'date' => [
                'required',
                'date',
                Rule::unique('weight_entries')->ignore($this->route('weight_entry')),
            ],
            'weight' => ['required', 'decimal:0,1', 'min:30', 'max:999'],
        ];
    }
}
