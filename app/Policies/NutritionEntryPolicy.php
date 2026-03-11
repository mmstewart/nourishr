<?php

namespace App\Policies;

use App\Models\NutritionEntry;
use App\Models\User;

class NutritionEntryPolicy
{
    public function viewAny(User $user)
    {
        //
    }

    public function view(User $user, NutritionEntry $nutritionEntry)
    {
        return $user->id === $nutritionEntry->user_id;
    }

    public function create(User $user)
    {
        //
    }

    public function update(User $user, NutritionEntry $nutritionEntry)
    {
        return $user->id === $nutritionEntry->user_id;
    }

    public function delete(User $user, NutritionEntry $nutritionEntry)
    {
        //
    }

    public function restore(User $user, NutritionEntry $nutritionEntry)
    {
        //
    }

    public function forceDelete(User $user, NutritionEntry $nutritionEntry)
    {
        //
    }
}
