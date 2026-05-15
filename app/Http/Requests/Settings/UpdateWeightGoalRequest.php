<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWeightGoalRequest extends FormRequest
{
    public function rules()
    {
        return [
            'weekly_goal_id' => ['required', 'integer'],
            'activity_level_id' => ['required', 'integer'],
            'current_weight' => ['required', 'decimal:0,2', 'min:30', 'max:999'],
            'goal_weight' => ['required', 'decimal:0,2', 'min:30', 'max:999'],
        ];
    }
}
