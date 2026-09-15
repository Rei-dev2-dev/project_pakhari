<?php

namespace Database\Seeders;

use App\Models\SidebarMenu;
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
        // 1. Seed Required Users
        $seedUsers = [
            [
                'username' => 'Renaldi',
                'name' => 'Renaldi',
                'email' => 'renaldi@pelindo.co.id',
                'password' => 'Pelindo3',
                'role' => User::ROLE_STAFF,
            ],
            [
                'username' => 'Operator',
                'name' => 'Operator BBM',
                'email' => 'operator@pelindo.co.id',
                'password' => 'Pelindo3',
                'role' => User::ROLE_OPERATOR,
            ],
            [
                'username' => 'admin',
                'name' => 'Administrator',
                'email' => 'admin@pelindo.co.id',
                'password' => 'Pelindo3',
                'role' => User::ROLE_ADMIN,
            ],
            [
                'username' => 'Daniel',
                'name' => 'Daniel',
                'email' => 'daniel@pelindo.co.id',
                'password' => 'Pelindo3',
                'role' => User::ROLE_SUPERADMIN,
            ],
        ];

        $operatorUser = null;
        foreach ($seedUsers as $uData) {
            $existing = User::where('username', $uData['username'])
                ->orWhere('email', $uData['email'])
                ->first();

            if ($existing) {
                $existing->update([
                    'name' => $uData['name'],
                    'username' => $uData['username'],
                    'email' => $uData['email'],
                    'role' => $uData['role'],
                ]);
                $userModel = $existing;
            } else {
                $userModel = User::create($uData);
            }

            if ($uData['role'] === User::ROLE_OPERATOR) {
                $operatorUser = $userModel;
            }
        }

        // 2. Seed 4 Default Tanks
        $tankA = Tank::updateOrCreate(
            ['code' => 'TNK-A'],
            [
                'name' => 'Tangki A (Zona 1)',
                'capacity_liters' => 100.0,
                'length_cm' => 200.0,
                'width_cm' => 90.0,
                'height_cm' => 70.0,
                'diameter_cm' => 90.0,
                'description' => 'Tangki distribusi utama air bersih Zona 1',
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
                'description' => 'Tangki cadangan kapasitas sedang Zona 2',
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
                'description' => 'Tangki penampungan perlakuan filter Zona 3',
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
                'description' => 'Tangki kapasitas besar operasional Pelindo Zona Utama',
                'is_active' => true,
                'sort_order' => 4,
            ]
        );

        // 3. Seed Initial Telemetry for each tank if none exists
        $initialData = [
            [$tankA, 45.0],
            [$tankB, 120.0],
            [$tankC, 30.0],
            [$tankD, 210.0],
        ];

        foreach ($initialData as [$tank, $vol]) {
            if ($tank->telemetries()->count() === 0) {
                $pct = round(($vol / $tank->capacity_liters) * 100, 2);
                $h = round(($vol / $tank->capacity_liters) * $tank->height_cm, 2);
                TankTelemetry::create([
                    'tank_id' => $tank->id,
                    'user_id' => $operatorUser->id,
                    'volume_liters' => $vol,
                    'percentage' => $pct,
                    'height_cm' => $h,
                    'status' => TankTelemetry::determineStatus($vol, $tank->capacity_liters),
                    'source' => 'system_init',
                    'device_id' => 'OPERATOR-'.$operatorUser->username,
                    'notes' => 'Inisialisasi sistem sensor '.$tank->name,
                ]);
            }
        }

        // 4. Seed Default Sidebar Menus
        $menus = [
            [
                'title' => 'Monitoring Tangki',
                'url' => '/monitoring',
                'icon' => 'cube',
                'roles' => ['staff', 'operator', 'admin', 'superadmin'],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Laporan & Ekspor',
                'url' => '/laporan',
                'icon' => 'document-report',
                'roles' => ['staff', 'operator', 'admin', 'superadmin'],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Master Tangki',
                'url' => '/admin/tanks',
                'icon' => 'database',
                'roles' => ['admin', 'superadmin'],
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'title' => 'Grafik & Analisis',
                'url' => '/chart',
                'icon' => 'chart-bar',
                'roles' => ['admin', 'superadmin'],
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'title' => 'Manajemen Pengguna',
                'url' => '/superadmin/users',
                'icon' => 'users',
                'roles' => ['superadmin'],
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'title' => 'Kelola Menu Sidebar',
                'url' => '/superadmin/menus',
                'icon' => 'menu',
                'roles' => ['superadmin'],
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($menus as $menu) {
            SidebarMenu::updateOrCreate(
                ['url' => $menu['url']],
                $menu
            );
        }
    }
}
