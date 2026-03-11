<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NutritionEntryRequest extends FormRequest
{
    public function rules()
    {
        return [
            'date' => ['required', 'date'],
            'food_item' => [
                'required',
                'max:255',
                Rule::unique('nutrition_entries')->where(function ($query) {
                    return $query->where('date', $this->date);
                }),
            ],
            'calories' => ['required', 'integer', 'min:0'],
            'protein' => ['required', 'integer', 'min:0'],
            'carbohydrates' => ['required', 'integer', 'min:0'],
            'fat' => ['required', 'integer', 'min:0'],
            'serving_size' => ['required', 'integer', 'min:0'],
        ];
    }
}
