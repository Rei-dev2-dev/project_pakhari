<?php

namespace App\Http\Controllers;

use App\Models\TankTelemetry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TandonMonitoringController extends Controller
{
    public const float MAX_CAPACITY_LITERS = 100.0;

    public const float MAX_HEIGHT_CM = 70.0;

    /**
     * Display the 3D tank monitoring dashboard.
     */
    public function index(): View
    {
        $latestTelemetry = TankTelemetry::latest()->first();

        if (! $latestTelemetry) {
            $latestTelemetry = TankTelemetry::create([
                'volume_liters' => 30.0,
                'percentage' => 30.0,
                'height_cm' => 21.0,
                'status' => 'normal',
                'source' => 'system_default',
                'device_id' => 'ESP32-TND-01',
                'notes' => 'Nilai awal sistem tandon air',
            ]);
        }

        $recentLogs = TankTelemetry::latest()->take(10)->get();

        return view('tandon.index', [
            'latest' => $latestTelemetry,
            'recentLogs' => $recentLogs,
            'maxCapacity' => self::MAX_CAPACITY_LITERS,
            'maxHeight' => self::MAX_HEIGHT_CM,
        ]);
    }

    /**
     * Update the tank water level (via Web UI or Ajax simulation).
     */
    public function updateLevel(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'volume_liters' => ['required', 'numeric', 'min:0', 'max:'.self::MAX_CAPACITY_LITERS],
            'source' => ['nullable', 'string', 'max:50'],
            'device_id' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $volume = (float) $validated['volume_liters'];
        $percentage = round(($volume / self::MAX_CAPACITY_LITERS) * 100, 2);
        $heightCm = round(($volume / self::MAX_CAPACITY_LITERS) * self::MAX_HEIGHT_CM, 2);
        $status = TankTelemetry::determineStatus($volume);

        $telemetry = TankTelemetry::create([
            'volume_liters' => $volume,
            'percentage' => $percentage,
            'height_cm' => $heightCm,
            'status' => $status,
            'source' => $validated['source'] ?? 'web_slider',
            'device_id' => $validated['device_id'] ?? 'ESP32-TND-01',
            'notes' => $validated['notes'] ?? 'Pembaruan level air',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Level air tandon berhasil diperbarui.',
            'telemetry' => $telemetry,
        ]);
    }

    /**
     * Get current status as JSON (for IoT polling / realtime sync).
     */
    public function getStatus(): JsonResponse
    {
        $latest = TankTelemetry::latest()->first();

        return response()->json([
            'success' => true,
            'max_capacity' => self::MAX_CAPACITY_LITERS,
            'max_height' => self::MAX_HEIGHT_CM,
            'telemetry' => $latest,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get recent telemetry logs for dashboard table.
     */
    public function getRecentLogs(): JsonResponse
    {
        $logs = TankTelemetry::latest()->take(15)->get();

        return response()->json([
            'success' => true,
            'logs' => $logs,
        ]);
    }

    /**
     * API endpoint for physical IoT microcontrollers (ESP32/Arduino/Raspberry Pi).
     */
    public function receiveTelemetry(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'volume_liters' => ['required', 'numeric', 'min:0', 'max:'.self::MAX_CAPACITY_LITERS],
            'device_id' => ['required', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $volume = (float) $validated['volume_liters'];
        $percentage = round(($volume / self::MAX_CAPACITY_LITERS) * 100, 2);
        $heightCm = round(($volume / self::MAX_CAPACITY_LITERS) * self::MAX_HEIGHT_CM, 2);
        $status = TankTelemetry::determineStatus($volume);

        $telemetry = TankTelemetry::create([
            'volume_liters' => $volume,
            'percentage' => $percentage,
            'height_cm' => $heightCm,
            'status' => $status,
            'source' => 'iot_hardware',
            'device_id' => $validated['device_id'],
            'notes' => $validated['notes'] ?? 'Data telemetri dari mikrokontroler IoT',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Telemetri IoT berhasil disimpan.',
            'telemetry' => $telemetry,
        ], 201);
    }
}
