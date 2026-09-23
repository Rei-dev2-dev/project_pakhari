<?php

namespace Database\Seeders;

use App\Models\Tank;
use App\Models\TankTelemetry;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Only SuperAdmin User (Production Launch Ready)
        $superadmin = User::updateOrCreate(
            ['username' => 'Daniel'],
            [
                'name' => 'Daniel',
                'username' => 'Daniel',
                'email' => 'daniel@pelindo.co.id',
                'password' => 'Pelindo3',
                'role' => User::ROLE_SUPERADMIN,
            ]
        );

        // Remove any non-superadmin demo accounts
        User::where('username', '!=', 'Daniel')->delete();

        // 2. Seed 4 Default BBM Tanks
        $tankA = Tank::updateOrCreate(
            ['code' => 'TNK-A'],
            [
                'name' => 'Tangki A (Zona 1)',
                'capacity_liters' => 100.0,
                'length_cm' => 200.0,
                'width_cm' => 90.0,
                'height_cm' => 70.0,
                'diameter_cm' => 90.0,
                'description' => 'Tangki distribusi utama BBM Solar Genset Zona 1',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $tankB = Tank::updateOrCreate(
            ['code' => 'TNK-B'],
            [
                'name' => 'Tangki B (Zona 2)',
                'capacity_liters' => 200.0,
                'length_cm' => 250.0,
                'width_cm' => 100.0,
                'height_cm' => 85.0,
                'diameter_cm' => 100.0,
                'description' => 'Tangki cadangan BBM Solar Genset Zona 2',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        $tankC = Tank::updateOrCreate(
            ['code' => 'TNK-C'],
            [
                'name' => 'Tangki C (Zona 3)',
                'capacity_liters' => 150.0,
                'length_cm' => 220.0,
                'width_cm' => 95.0,
                'height_cm' => 75.0,
                'diameter_cm' => 95.0,
                'description' => 'Tangki penampungan BBM Solar Genset Zona 3',
                'is_active' => true,
                'sort_order' => 3,
            ]
        );

        $tankD = Tank::updateOrCreate(
            ['code' => 'TNK-D'],
            [
                'name' => 'Tangki D (Zona Utama)',
                'capacity_liters' => 300.0,
                'length_cm' => 300.0,
                'width_cm' => 110.0,
                'height_cm' => 100.0,
                'diameter_cm' => 110.0,
                'description' => 'Tangki kapasitas besar BBM Solar Genset Zona Utama',
                'is_active' => true,
                'sort_order' => 4,
            ]
        );

        // Clean out any old dummy tanks (such as TG1, TG2)
        Tank::whereNotIn('code', ['TNK-A', 'TNK-B', 'TNK-C', 'TNK-D'])->delete();

        // 3. Clean and Empty All Telemetries (Zero records for clean production launch)
        TankTelemetry::truncate();
    }
}
