<?php

namespace Database\Factories;


use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['COMPLETED']),
            'name' => $this->faker->sentence(10),
            'description' => $this->faker->sentence(300),
            'total_price' => $this->faker->randomFloat(2, 10, 500),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
