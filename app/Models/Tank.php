<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tank extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'capacity_liters',
        'length_cm',
        'width_cm',
        'height_cm',
        'diameter_cm',
        'description',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capacity_liters' => 'float',
            'length_cm' => 'float',
            'width_cm' => 'float',
            'height_cm' => 'float',
            'diameter_cm' => 'float',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Hitung otomatis kapasitas tangki dalam liter berdasarkan dimensi fisik.
     */
    public static function calculateCapacity(float $length, float $width = 0, float $height = 0, float $diameter = 0): float
    {
        if ($diameter > 0 && $length > 0) {
            $radius = $diameter / 2;
            $volume = (M_PI * ($radius ** 2) * $length) / 1000;

            return round($volume, 1);
        }

        if ($length > 0 && $width > 0 && $height > 0) {
            $volume = ($length * $width * $height) / 1000;

            return round($volume, 1);
        }

        return 100.0;
    }

    /**
     * @return HasMany<TankTelemetry, $this>
     */
    public function telemetries(): HasMany
    {
        return $this->hasMany(TankTelemetry::class, 'tank_id');
    }

    public function latestTelemetry(): ?TankTelemetry
    {
        return $this->telemetries()->latest()->first();
    }
}
