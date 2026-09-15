<?php

namespace App\Models;

use Database\Factories\TankTelemetryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TankTelemetry extends Model
{
    /** @use HasFactory<TankTelemetryFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tank_id',
        'user_id',
        'volume_liters',
        'percentage',
        'height_cm',
        'status',
        'source',
        'device_id',
        'notes',
        'photo_path',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tank_id' => 'integer',
            'user_id' => 'integer',
            'volume_liters' => 'float',
            'percentage' => 'float',
            'height_cm' => 'float',
        ];
    }

    /**
     * @return BelongsTo<Tank, $this>
     */
    public function tank(): BelongsTo
    {
        return $this->belongsTo(Tank::class, 'tank_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Determine tank status based on volume in liters and capacity.
     */
    public static function determineStatus(float $liters, float $maxCapacity = 100.0): string
    {
        if ($liters <= 0) {
            return 'empty';
        }
        $ratio = $maxCapacity > 0 ? ($liters / $maxCapacity) : ($liters / 100.0);
        if ($ratio < 0.20) {
            return 'low';
        }
        if ($ratio >= 0.95) {
            return 'warning_full';
        }

        return 'normal';
    }
}
