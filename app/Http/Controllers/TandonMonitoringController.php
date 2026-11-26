<?php

namespace App\Http\Controllers;

use App\Models\Tank;
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
    public function getStatus(Request $request): JsonResponse
    {
        $tank = null;
        if ($request->filled('tank_code')) {
            $tank = Tank::where('code', $request->input('tank_code'))->first();
        } elseif ($request->filled('tank_id')) {
            $tank = Tank::find($request->input('tank_id'));
        }

        if ($tank) {
            $latest = $tank->telemetries()->latest()->first();
            $maxCap = (float) $tank->capacity_liters > 0 ? (float) $tank->capacity_liters : self::MAX_CAPACITY_LITERS;
            $maxH = (float) $tank->height_cm > 0 ? (float) $tank->height_cm : self::MAX_HEIGHT_CM;
        } else {
            $latest = TankTelemetry::latest()->first();
            $maxCap = self::MAX_CAPACITY_LITERS;
            $maxH = self::MAX_HEIGHT_CM;
        }

        return response()->json([
            'success' => true,
            'max_capacity' => $maxCap,
            'max_height' => $maxH,
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
     * API endpoint for physical IoT microcontrollers (WT32-ETH01 / ESP32 + JSN-SR04T ultrasonic sensors).
     * Supports multi-tank binding via tank_code or tank_id, plus calculation from distance_cm, height_cm, or volume_liters.
     */
    public function receiveTelemetry(Request $request): JsonResponse
    {
        // Optional IoT API token protection (active when IOT_API_TOKEN is set in .env)
        $configuredToken = env('IOT_API_TOKEN');
        if (! empty($configuredToken)) {
            $providedToken = $request->header('X-IOT-TOKEN') ?? $request->input('api_token');
            if (! hash_equals((string) $configuredToken, (string) $providedToken)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token autentikasi IoT tidak valid.',
                ], 401);
            }
        }

        // Resolve target tank if tank_code or tank_id is provided
        $tank = null;
        if ($request->filled('tank_code')) {
            $tank = Tank::where('code', $request->input('tank_code'))->first();
        } elseif ($request->filled('tank_id')) {
            $tank = Tank::find($request->input('tank_id'));
        }

        $maxCapacity = ($tank && (float) $tank->capacity_liters > 0)
            ? (float) $tank->capacity_liters
            : self::MAX_CAPACITY_LITERS;

        $maxHeight = ($tank && (float) $tank->height_cm > 0)
            ? (float) $tank->height_cm
            : self::MAX_HEIGHT_CM;

        $validated = $request->validate([
            'tank_id' => ['nullable', 'integer', 'exists:tanks,id'],
            'tank_code' => ['nullable', 'string', 'max:50'],
            'volume_liters' => ['nullable', 'numeric', 'min:0', 'max:'.$maxCapacity],
            'height_cm' => ['nullable', 'numeric', 'min:0', 'max:'.$maxHeight],
            'distance_cm' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'sensor_mount_height_cm' => ['nullable', 'numeric', 'min:1', 'max:1000'],
            'device_id' => ['required', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        // Calculate height_cm and volume_liters depending on what the sensor sent:
        // 1. If volume_liters is directly provided
        // 2. Else if height_cm (liquid height) is provided
        // 3. Else if distance_cm (JSN-SR04T ultrasonic distance from top sensor to liquid surface) is provided
        if (isset($validated['volume_liters'])) {
            $volume = round((float) $validated['volume_liters'], 2);
            $heightCm = isset($validated['height_cm'])
                ? round((float) $validated['height_cm'], 2)
                : round(($volume / $maxCapacity) * $maxHeight, 2);
        } elseif (isset($validated['height_cm'])) {
            $heightCm = round(min($maxHeight, max(0.0, (float) $validated['height_cm'])), 2);
            $volume = round(($heightCm / $maxHeight) * $maxCapacity, 2);
        } elseif (isset($validated['distance_cm'])) {
            // JSN-SR04T measures distance from sensor at top of tank to fuel surface.
            // sensor_mount_height_cm allows accounting for the 20cm blind-spot offset if mounted higher than maxHeight.
            $mountHeight = isset($validated['sensor_mount_height_cm'])
                ? (float) $validated['sensor_mount_height_cm']
                : $maxHeight;
            $rawHeight = $mountHeight - (float) $validated['distance_cm'];
            $heightCm = round(min($maxHeight, max(0.0, $rawHeight)), 2);
            $volume = round(($heightCm / $maxHeight) * $maxCapacity, 2);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Data sensor harus menyertakan minimal salah satu dari: volume_liters, height_cm, atau distance_cm.',
                'errors' => ['volume_liters' => ['Field volume_liters, height_cm, atau distance_cm wajib diisi.']],
            ], 422);
        }

        $percentage = round(($volume / $maxCapacity) * 100, 2);
        $status = TankTelemetry::determineStatus($volume, $maxCapacity);

        $latest = $tank
            ? $tank->telemetries()->latest()->first()
            : TankTelemetry::whereNull('tank_id')->latest()->first();

        $initialVol = $latest ? (float) $latest->volume_liters : $volume;
        $deltaVol = round($volume - $initialVol, 2);

        $telemetry = TankTelemetry::create([
            'tank_id' => $tank?->id,
            'volume_awal' => $initialVol,
            'volume_perubahan' => $deltaVol,
            'volume_liters' => $volume,
            'percentage' => $percentage,
            'height_cm' => $heightCm,
            'status' => $status,
            'source' => 'iot_hardware',
            'device_id' => $validated['device_id'],
            'notes' => $validated['notes'] ?? ('Data telemetri dari mikrokontroler IoT'.(isset($validated['distance_cm']) ? " (Jarak sensor: {$validated['distance_cm']} cm)" : '')),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Telemetri IoT berhasil disimpan.',
            'tank' => $tank ? [
                'id' => $tank->id,
                'code' => $tank->code,
                'name' => $tank->name,
                'max_capacity' => $maxCapacity,
                'max_height' => $maxHeight,
            ] : null,
            'telemetry' => $telemetry,
        ], 201);
    }
}
