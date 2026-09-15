<?php

namespace Tests\Feature;

use App\Models\Tank;
use App\Models\TankTelemetry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChartTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_chart(): void
    {
        $response = $this->get('/chart');
        $response->assertRedirect('/login');
    }

    public function test_staff_and_operator_cannot_access_chart(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $response = $this->actingAs($staff)->get('/chart');
        $response->assertForbidden();

        $operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);
        $response2 = $this->actingAs($operator)->get('/chart');
        $response2->assertForbidden();
    }

    public function test_admin_and_superadmin_can_access_chart(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $response = $this->actingAs($admin)->get('/chart');
        $response->assertOk();
        $response->assertSee('Grafik & Analisis Volume Tangki BBM');

        $superadmin = User::factory()->create(['role' => User::ROLE_SUPERADMIN]);
        $response2 = $this->actingAs($superadmin)->get('/chart');
        $response2->assertOk();
        $response2->assertSee('Grafik & Analisis Volume Tangki BBM');
    }

    public function test_newly_added_tank_automatically_integrates_into_chart(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        // Create 2 initial tanks
        $tank1 = Tank::create([
            'name' => 'Tangki Alpha',
            'code' => 'TNK-ALPHA',
            'capacity_liters' => 500,
            'height_cm' => 120,
            'width_cm' => 100,
            'is_active' => true,
        ]);

        $tank2 = Tank::create([
            'name' => 'Tangki Beta',
            'code' => 'TNK-BETA',
            'capacity_liters' => 750,
            'height_cm' => 150,
            'width_cm' => 110,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/chart');
        $response->assertOk();
        $response->assertSee('Tangki Alpha');
        $response->assertSee('Tangki Beta');

        // Admin adds a brand new tank
        $tank3 = Tank::create([
            'name' => 'Tangki Gamma Baru',
            'code' => 'TNK-GAMMA',
            'capacity_liters' => 1200,
            'height_cm' => 180,
            'width_cm' => 130,
            'is_active' => true,
        ]);

        // The newly added tank automatically appears in the chart view
        $response2 = $this->actingAs($admin)->get('/chart');
        $response2->assertOk();
        $response2->assertSee('Tangki Gamma Baru');
        $response2->assertSee('TNK-GAMMA');
        $response2->assertSee('1,200.0');
    }

    public function test_chart_filters_by_year_month_and_metric(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $tank = Tank::create([
            'name' => 'Tangki Delta',
            'code' => 'TNK-DELTA',
            'capacity_liters' => 600,
            'height_cm' => 100,
            'width_cm' => 90,
            'is_active' => true,
        ]);

        TankTelemetry::create([
            'tank_id' => $tank->id,
            'volume_liters' => 300,
            'percentage' => 50,
            'height_cm' => 50,
            'status' => 'normal',
            'source' => 'pemasukan_bbm',
            'notes' => 'Pemasukan BBM',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/chart?year='.date('Y').'&month='.date('n').'&tank_id='.$tank->id.'&metric=comparison');
        $response->assertOk();
        $response->assertSee('Tangki Delta');
    }
}
