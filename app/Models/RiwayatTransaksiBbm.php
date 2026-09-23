<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatTransaksiBbm extends Model
{
    use HasFactory;

    protected $table = 'riwayat_transaksi_bbm';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tank_id',
        'user_id',
        'jenis_transaksi',
        'volume_awal',
        'volume_perubahan',
        'volume_akhir',
        'ketinggian_awal_cm',
        'ketinggian_akhir_cm',
        'nomor_do',
        'unit_tujuan',
        'foto_bukti',
        'catatan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'volume_awal' => 'float',
            'volume_perubahan' => 'float',
            'volume_akhir' => 'float',
            'ketinggian_awal_cm' => 'float',
            'ketinggian_akhir_cm' => 'float',
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
}
