<?php

namespace App\Http\Requests;

use App\Models\WeeklyGoal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWeightGoalRequest extends FormRequest
{
    public function rules()
    {
        return [
            'weekly_goal_id' => [
                'required',
                'integer',
                Rule::exists('weekly_goals', 'id'),
                // Users under 18 can't choose weight-loss goals.
                Rule::notIn($this->user()?->isMinor() ? WeeklyGoal::WEIGHT_LOSS_IDS : []),
            ],
            'activity_level_id' => ['required', 'integer', Rule::exists('activity_levels', 'id')],
            'current_weight' => ['required', 'decimal:0,1', 'min:30', 'max:999'],
            'goal_weight' => ['required', 'decimal:0,1', 'min:30', 'max:999'],
        ];
    }
}
