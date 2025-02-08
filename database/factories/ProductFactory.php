<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(10),
            'description' => $this->faker->sentence(50),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'stock_quantity' => $this->faker->numberBetween(10, 50),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
