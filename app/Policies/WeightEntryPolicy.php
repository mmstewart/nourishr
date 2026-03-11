<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WeightEntry;

class WeightEntryPolicy
{
    public function viewAny(User $user)
    {
        //
    }

    public function view(User $user, WeightEntry $weightEntry)
    {
        //
    }

    public function create(User $user)
    {
        //
    }

    public function update(User $user, WeightEntry $weightEntry)
    {
        return $user->id === $weightEntry->user_id;
    }

    public function delete(User $user, WeightEntry $weightEntry)
    {
        //
    }

    public function restore(User $user, WeightEntry $weightEntry)
    {
        //
    }

    public function forceDelete(User $user, WeightEntry $weightEntry)
    {
        //
    }
}
