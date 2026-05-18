<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNutritionEntryRequest extends FormRequest
{
    public function rules()
    {
        return [
            'date' => ['required', 'date', 'before_or_equal:today'],
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.fdc_id' => ['nullable', 'integer'],
            'entries.*.meal_type' => ['required', 'in:breakfast,lunch,dinner,snack'],
            'entries.*.food_item' => ['required', 'max:255'],
            'entries.*.calories' => ['required', 'integer', 'min:0'],
            'entries.*.protein' => ['required', 'decimal:0,2', 'min:0'],
            'entries.*.carbohydrates' => ['required', 'decimal:0,2', 'min:0'],
            'entries.*.fats' => ['required', 'decimal:0,2', 'min:0'],
            'entries.*.serving_size' => ['nullable', 'max:255'],
            'entries.*.quantity' => ['required', 'decimal:0,2', 'min:0.25'],
        ];
    }
}
