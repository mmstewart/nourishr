<?php

namespace Database\Factories;

use App\Models\CoachUsage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CoachUsage>
 */
class CoachUsageFactory extends Factory
{
    protected $model = CoachUsage::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider' => 'gemini',
            'model' => 'gemini-test-model',
            'input_tokens' => fake()->numberBetween(50, 500),
            'output_tokens' => fake()->numberBetween(20, 300),
        ];
    }
}
