<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoachUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'input_tokens',
        'model',
        'output_tokens',
        'provider',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Usage since midnight in the user's own timezone, so the daily cap
     * resets at their midnight rather than the server's.
     */
    public function scopeTodayFor(Builder $query, User $user): Builder
    {
        $startOfDay = now($user->timezone ?: config('app.timezone'))->startOfDay()->utc();

        return $query->where('user_id', $user->id)->where('created_at', '>=', $startOfDay);
    }
}
