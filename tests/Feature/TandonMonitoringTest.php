<?php

namespace Tests\Feature;

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
}
