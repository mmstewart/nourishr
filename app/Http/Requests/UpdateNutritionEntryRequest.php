<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNutritionEntryRequest extends FormRequest
{
    public function rules()
    {
        return [
            'date' => ['required', 'date', 'before_or_equal:today'],
            'meal_type' => ['required', 'in:breakfast,lunch,dinner,snack'],
            'calories' => ['required', 'integer', 'min:0'],
            'protein' => ['required', 'decimal:0,2', 'min:0'],
            'carbohydrates' => ['required', 'decimal:0,2', 'min:0'],
            'fats' => ['required', 'decimal:0,2', 'min:0'],
            'serving_size' => ['nullable', 'max:255'],
            'quantity' => ['required', 'decimal:0,2', 'min:0.25'],
        ];
    }
}
