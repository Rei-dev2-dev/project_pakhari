<?php

namespace Database\Factories;

use App\Models\Tank;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tank>
 */
class TankFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $length = fake()->randomFloat(2, 100, 300);
        $width = fake()->randomFloat(2, 60, 120);
        $height = fake()->randomFloat(2, 50, 100);
        $diameter = fake()->randomFloat(2, 60, 120);

        return [
            'name' => 'Tangki '.fake()->city(),
            'code' => 'TNK-'.fake()->unique()->bothify('##??'),
            'capacity_liters' => Tank::calculateCapacity($length, $width, $height, $diameter),
            'length_cm' => $length,
            'width_cm' => $width,
            'height_cm' => $height,
            'diameter_cm' => $diameter,
            'description' => fake()->sentence(),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }
}
