<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NutritionEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'calories',
        'carbohydrates',
        'date',
        'fats',
        'food_item',
        'meal_type',
        'protein',
        'serving_size',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
