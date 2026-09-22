<?php

namespace Database\Seeders;

use App\Models\TankTelemetry;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CleanLaporanSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     * Cleans all telemetry logs / reports and cleans up proof photos from storage.
     */
    public function run(): void
    {
        $count = TankTelemetry::count();

        // 1. Kosongkan tabel tank_telemetries (Laporan Telemetri & Transaksi BBM)
        Schema::disableForeignKeyConstraints();
        TankTelemetry::truncate();
        Schema::enableForeignKeyConstraints();

        // 2. Bersihkan file foto bukti di storage agar tidak menumpuk
        if (Storage::disk('public')->exists('telemetry_proofs')) {
            Storage::disk('public')->deleteDirectory('telemetry_proofs');
            Storage::disk('public')->makeDirectory('telemetry_proofs');
        }

        if ($this->command) {
            $this->command->info("✓ Berhasil membersihkan {$count} data laporan & foto bukti dari sistem.");
        }
    }
}
