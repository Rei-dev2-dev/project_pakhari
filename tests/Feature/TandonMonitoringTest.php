<?php

namespace Tests\Feature;

use App\Models\Tank;
use App\Models\TankTelemetry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TandonMonitoringTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the 3D monitoring dashboard page renders successfully.
     */
    public function test_can_render_tank_monitoring_page(): void
    {
        TankTelemetry::factory()->create([
            'volume_liters' => 50.0,
            'percentage' => 50.0,
            'height_cm' => 35.0,
            'status' => 'normal',
        ]);

        $response = $this->get('/tandon');

        $response->assertOk();
        $response->assertSee('Monitoring Tandon Air');
        $response->assertSee('Model 3D');
        $response->assertSee('100 Liter');
    }

    /**
     * Test updating water level via web ajax.
     */
    public function test_can_update_water_level_via_ajax(): void
    {
        $response = $this->postJson('/tandon/level', [
            'volume_liters' => 65.5,
            'source' => 'web_slider',
            'device_id' => 'ESP32-TND-01',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Level air tandon berhasil diperbarui.',
            ])
            ->assertJsonPath('telemetry.volume_liters', 65.5)
            ->assertJsonPath('telemetry.status', 'normal');

        $this->assertDatabaseHas('tank_telemetries', [
            'volume_liters' => 65.5,
            'source' => 'web_slider',
        ]);
    }

    /**
     * Test water level validation rejects values exceeding 100 liters.
     */
    public function test_validates_water_level_within_100_liter_limit(): void
    {
        $response = $this->postJson('/tandon/level', [
            'volume_liters' => 125.0, // Exceeds 100 liters
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['volume_liters']);
    }

    /**
     * Test water level validation rejects negative values.
     */
    public function test_validates_water_level_rejects_negative(): void
    {
        $response = $this->postJson('/tandon/level', [
            'volume_liters' => -5.0,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['volume_liters']);
    }

    /**
     * Test status endpoint returns JSON with max capacity 100L.
     */
    public function test_can_get_tank_status_api(): void
    {
        TankTelemetry::factory()->create([
            'volume_liters' => 75.0,
        ]);

        $response = $this->getJson('/api/tandon/status');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'max_capacity' => 100.0,
            ])
            ->assertJsonStructure([
                'success',
                'max_capacity',
                'max_height',
                'telemetry',
                'server_time',
            ]);
    }

    /**
     * Test receiving telemetry payload from physical IoT microcontroller.
     */
    public function test_can_receive_telemetry_from_iot_hardware(): void
    {
        $response = $this->postJson('/api/tandon/telemetry', [
            'volume_liters' => 96.5,
            'device_id' => 'ESP32-PROD-01',
            'notes' => 'Ultrasonic ping 14cm',
        ]);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Telemetri IoT berhasil disimpan.',
            ])
            ->assertJsonPath('telemetry.status', 'warning_full');

        $this->assertDatabaseHas('tank_telemetries', [
            'volume_liters' => 96.5,
            'device_id' => 'ESP32-PROD-01',
            'status' => 'warning_full',
        ]);
    }

    /**
     * Test receiving ultrasonic distance_cm payload from WT32-ETH01 + JSN-SR04T for a specific tank_code.
     */
    public function test_can_receive_ultrasonic_distance_for_multi_tank_iot(): void
    {
        $tankB = Tank::create([
            'name' => 'Tangki B (Zona 2)',
            'code' => 'TNK-B',
            'capacity_liters' => 200.0,
            'length_cm' => 250.0,
            'width_cm' => 100.0,
            'height_cm' => 80.0,
            'diameter_cm' => 100.0,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // Sensor mounted at 100cm from bottom (20cm above 80cm max fuel height to avoid JSN-SR04T 20cm blind zone)
        // Sensor reads distance_cm = 60cm => fuel height = 100 - 60 = 40cm (50% of 80cm => 100.0 Liters)
        $response = $this->postJson('/api/iot/telemetry', [
            'tank_code' => 'TNK-B',
            'distance_cm' => 60.0,
            'sensor_mount_height_cm' => 100.0,
            'device_id' => 'ESP32-ETH01-01',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('tank.code', 'TNK-B')
            ->assertJsonPath('telemetry.height_cm', 40)
            ->assertJsonPath('telemetry.volume_liters', 100)
            ->assertJsonPath('telemetry.percentage', 50);

        $this->assertDatabaseHas('tank_telemetries', [
            'tank_id' => $tankB->id,
            'height_cm' => 40.0,
            'volume_liters' => 100.0,
            'device_id' => 'ESP32-ETH01-01',
        ]);
    }
}
