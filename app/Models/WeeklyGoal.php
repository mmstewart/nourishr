<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyGoal extends Model
{
    use HasFactory;

    public $timestamps = false;

    /** Goals that create a calorie deficit (lose 0.5 to 2 lbs per week). */
    public const WEIGHT_LOSS_IDS = [1, 2, 3, 4];

    /**
     * Users under 18 can't choose weight-loss goals: calorie deficits aren't
     * appropriate without a doctor's guidance while still growing.
     */
    public function scopeAvailableFor(Builder $query, User $user): Builder
    {
        return $user->isMinor()
            ? $query->whereNotIn('id', self::WEIGHT_LOSS_IDS)
            : $query;
    }
}
