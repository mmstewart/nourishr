<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeightGoal extends Model
{
    use HasFactory;

    public $fillable = [
        'activity_level_id',
        'current_weight',
        'goal_weight',
        'start_weight',
        'start_date',
        'weekly_goal_id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
