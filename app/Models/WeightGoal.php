<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeightGoal extends Model
{
    use HasFactory;

    protected $appends = [
        'bmr',
        'carb_grams',
        'fat_grams',
        'maintenance_calories',
        'protein_grams',
        'target_calories',
    ];

    public $fillable = [
        'activity_level_id',
        'current_weight',
        'goal_weight',
        'start_weight',
        'start_date',
        'weekly_goal_id',
        'user_id',
    ];

    public function activityLevel()
    {
        return $this->belongsTo(ActivityLevel::class);
    }

    public function weeklyGoal()
    {
        return $this->belongsTo(WeeklyGoal::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getBMRAttribute()
    {
        // Mifflin-St Jeor Equation
        $gender = $this->user->gender === 'male' ? 5 : -161;

        $heightInCM = $this->user->height_inches * 2.54;

        $weightInKG = $this->current_weight / 2.20462;

        return round((10 * $weightInKG) + (6.25 * $heightInCM) - (5 * $this->user->age) + $gender);
    }

    public function getCarbGramsAttribute()
    {
        // MFP Default: 50% of target calories (USDA (United States Department of Agriculture) guidelines)
        return round(($this->target_calories * 0.50) / 4);
    }

    public function getDailyAdjustmentAttribute()
    {
        return match ($this->weekly_goal_id) {
            1 => -250, // Lose 0.5 lbs
            2 => -500,  // Lose 1 lb
            3 => -750,  // Lose 1.5 lbs
            4 => -1000, // Lose 2 lbs
            5 => 0,     // Maintain
            6 => 250,   // Gain 0.5 lb
            7 => 500,   // Gain 1 lb
            default => 0,
        };
    }

    public function getFatGramsAttribute()
    {
        // MFP Default: 30% of target calories (USDA (United States Department of Agriculture) guidelines)
        return round(($this->target_calories * 0.30) / 9);
    }

    public function getMaintenanceCaloriesAttribute()
    {
        return round($this->bmr * $this->activityLevel->multiplier);
    }

    public function getProteinGramsAttribute()
    {
        // MFP Default: 20% of target calories (USDA (United States Department of Agriculture) guidelines)
        return round(($this->target_calories * 0.20) / 4);
    }

    public function getTargetCaloriesAttribute()
    {
        return $this->maintenance_calories + $this->daily_adjustment;
    }
}
