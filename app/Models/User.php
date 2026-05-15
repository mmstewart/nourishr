<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'country_code',
        'dob',
        'email',
        'email_verified_at',
        'gender',
        'google_id',
        'height_inches',
        'name',
        'password',
        'timezone',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $appends = [
        'has_password',
        'has_weight_goal',
    ];

    public $rememberTokenName = false;

    protected function casts()
    {
        return [
            'dob' => 'date:Y-m-d',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function nutritionEntries()
    {
        return $this->hasMany(NutritionEntry::class);
    }

    public function weightEntries()
    {
        return $this->hasMany(WeightEntry::class);
    }

    public function weightGoal()
    {
        return $this->hasOne(WeightGoal::class);
    }

    public function getAgeAttribute()
    {
        return $this->dob?->age;
    }

    public function getHasPasswordAttribute()
    {
        return !is_null($this->password);
    }

    public function getHasWeightGoalAttribute()
    {
        return $this->weightGoal()->exists();
    }

    public function hasCompletedProfile()
    {
        return collect([
            $this->dob,
            $this->height_inches,
            $this->country_code,
            $this->timezone,
            $this->gender,
        ])->every(fn($value) => !empty($value));
    }

    public function hasCompletedSetup()
    {
        return $this->hasCompletedProfile() && $this->weightGoal()->exists();
    }
}
