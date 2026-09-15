<?php

namespace Database\Factories;

use App\Models\TankTelemetry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TankTelemetry>
 */
class TankTelemetryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $volume = fake()->randomFloat(2, 0, 100);

        return [
            'volume_liters' => $volume,
            'percentage' => round(($volume / 100) * 100, 2),
            'height_cm' => round(($volume / 100) * 70, 2),
            'status' => TankTelemetry::determineStatus($volume),
            'source' => 'simulation',
            'device_id' => 'ESP32-TND-01',
            'notes' => fake()->sentence(),
        ];
    }
}
