<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWeightRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'weekly_goal_id' => ['required', 'integer'],
            'activity_level_id' => ['required', 'integer'],
            'current_weight' => ['required', 'decimal:0,1', 'min:30', 'max:999'],
            'goal_weight' => ['required', 'decimal:0,1', 'min:30', 'max:999'],
        ];
    }
}
