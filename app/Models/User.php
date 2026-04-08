<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'country_code',
        'dob',
        'email',
        'email_verified_at',
        'gender',
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

    protected function casts()
    {
        return [
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

    public function hasCompletedSetup()
    {
        return collect([
            $this->dob,
            $this->height_inches,
            $this->country_code,
            $this->timezone,
            $this->gender,
        ])->every(fn($value) => !empty($value));
    }
}
