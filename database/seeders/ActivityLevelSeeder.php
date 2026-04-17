<?php

namespace Database\Seeders;

use App\Models\ActivityLevel;
use Illuminate\Database\Seeder;

class ActivityLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['id' => 1, 'name' => 'Sedentary', 'multiplier' => 1.2],
            ['id' => 2, 'name' => 'Lightly Active', 'multiplier' => 1.375],
            ['id' => 3, 'name' => 'Active', 'multiplier' => 1.55],
            ['id' => 4, 'name' => 'Very Active', 'multiplier' => 1.725],
            ['id' => 5, 'name' => 'Extra Active', 'multiplier' => 1.9],
        ];

        foreach ($levels as $level) {
            ActivityLevel::updateOrCreate(['id' => $level['id']], $level);
        }
    }
}
