<?php

namespace Database\Seeders;

use App\Models\WeeklyGoal;
use Illuminate\Database\Seeder;

class WeeklyGoalSeeder extends Seeder
{
    public function run(): void
    {
        $goals = [
            [
                'id' => 1,
                'name' => 'Lose 0.5 lbs per week',
            ],
            [
                'id' => 2,
                'name' => 'Lose 1 lb per week',
            ],
            [
                'id' => 3,
                'name' => 'Lose 1.5 lbs per week',
            ],
            [
                'id' => 4,
                'name' => 'Lose 2 lbs per week',
            ],
            [
                'id' => 5,
                'name' => 'Maintain weight',
            ],
            [
                'id' => 6,
                'name' => 'Gain 0.5 lbs per week',
            ],
            [
                'id' => 7,
                'name' => 'Lose 1 lb per week',
            ],
        ];

        foreach ($goals as $goal) {
            WeeklyGoal::updateOrCreate(['id' => $goal['id']], $goal);
        }
    }
}
