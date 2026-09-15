<?php

namespace App\Http\Controllers;

use App\Models\Tank;
use App\Models\TankTelemetry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    /**
     * Display the 2x2 grid of all active tanks for monitoring.
     */
    public function index(): View
    {
        $tanks = Tank::where('is_active', true)
            ->with(['telemetries' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Ensure each tank has an initial telemetry record if empty
        foreach ($tanks as $tank) {
            if ($tank->telemetries->isEmpty()) {
                $defaultVol = round($tank->capacity_liters * 0.35, 1);
                $pct = 35.0;
                $height = round(($defaultVol / $tank->capacity_liters) * $tank->height_cm, 1);
                $tank->telemetries()->create([
                    'volume_liters' => $defaultVol,
                    'percentage' => $pct,
                    'height_cm' => $height,
                    'status' => TankTelemetry::determineStatus($defaultVol, $tank->capacity_liters),
                    'source' => 'system_default',
                    'device_id' => 'ESP32-'.$tank->code,
                    'notes' => 'Nilai awal sistem '.$tank->name,
                ]);
                $tank->load('telemetries');
            }
        }

        return view('monitoring.index', [
            'tanks' => $tanks,
        ]);
    }

    /**
     * Display the 3D monitoring viewport for a single selected tank.
     */
    public function show(Tank $tank): View
    {
        $latestTelemetry = $tank->telemetries()->latest()->first();

        if (! $latestTelemetry) {
            $defaultVol = round($tank->capacity_liters * 0.35, 1);
            $pct = 35.0;
            $height = round(($defaultVol / $tank->capacity_liters) * $tank->height_cm, 1);
            $latestTelemetry = $tank->telemetries()->create([
                'volume_liters' => $defaultVol,
                'percentage' => $pct,
                'height_cm' => $height,
                'status' => TankTelemetry::determineStatus($defaultVol, $tank->capacity_liters),
                'source' => 'system_default',
                'device_id' => 'ESP32-'.$tank->code,
                'notes' => 'Nilai awal sistem '.$tank->name,
            ]);
        }

        $recentLogs = $tank->telemetries()->with('user')->latest()->take(10)->get();

        return view('monitoring.show', [
            'tank' => $tank,
            'latest' => $latestTelemetry,
            'recentLogs' => $recentLogs,
            'maxCapacity' => (float) $tank->capacity_liters,
            'maxHeight' => (float) $tank->height_cm,
        ]);
    }

    /**
     * Update the tank water level (via Web UI or Ajax simulation).
     */
    public function updateLevel(Request $request, Tank $tank): JsonResponse
    {
        $maxCapacity = (float) $tank->capacity_liters;
        $maxHeight = (float) $tank->height_cm;

        $validated = $request->validate([
            'volume_liters' => ['required', 'numeric', 'min:0', 'max:'.$maxCapacity],
            'source' => ['nullable', 'string', 'max:50'],
            'device_id' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $volume = (float) $validated['volume_liters'];
        $percentage = round(($volume / $maxCapacity) * 100, 2);
        $heightCm = round(($volume / $maxCapacity) * $maxHeight, 2);
        $status = TankTelemetry::determineStatus($volume, $maxCapacity);

        $telemetry = $tank->telemetries()->create([
            'user_id' => auth()->id(),
            'volume_liters' => $volume,
            'percentage' => $percentage,
            'height_cm' => $heightCm,
            'status' => $status,
            'source' => $validated['source'] ?? 'web_slider',
            'device_id' => $validated['device_id'] ?? ('ESP32-'.$tank->code),
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
    public function getStatus(Tank $tank): JsonResponse
    {
        $latest = $tank->telemetries()->latest()->first();

        return response()->json([
            'success' => true,
            'tank' => [
                'id' => $tank->id,
                'name' => $tank->name,
                'code' => $tank->code,
                'max_capacity' => (float) $tank->capacity_liters,
                'max_height' => (float) $tank->height_cm,
                'width' => (float) $tank->width_cm,
                'diameter' => (float) $tank->diameter_cm,
            ],
            'telemetry' => $latest,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /**
     * Record BBM Inflow (Pemasukan) or Outflow (Pemakaian) from height input.
     */
    public function recordBbm(Request $request, Tank $tank): mixed
    {
        $maxHeight = (float) $tank->height_cm;
        $maxCapacity = (float) $tank->capacity_liters;

        $validated = $request->validate([
            'type' => ['required', 'in:pemasukan,pemakaian'],
            'height_cm' => ['required', 'numeric', 'min:0', 'max:'.$maxHeight],
            'notes' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:10240'],
        ], [
            'height_cm.max' => "Ketinggian BBM tidak boleh melebihi tinggi maksimal tangki ({$maxHeight} cm) yang ditentukan oleh Admin.",
            'height_cm.min' => 'Ketinggian BBM tidak boleh bernilai negatif.',
            'photo.image' => 'File bukti harus berupa gambar (foto).',
            'photo.max' => 'Ukuran file foto bukti maksimal 10 MB.',
        ]);

        $inputHeight = (float) $validated['height_cm'];
        $type = $validated['type'];

        // Handle photo upload if present
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('telemetry_proofs', 'public');
        }

        // Get latest telemetry to know initial state
        $latest = $tank->telemetries()->latest()->first();
        $initialVol = $latest ? (float) $latest->volume_liters : 0.0;
        $initialHeight = $latest ? (float) $latest->height_cm : 0.0;

        // Calculate liters automatically from height
        $volumeLiters = round(($inputHeight / $maxHeight) * $maxCapacity, 1);
        $percentage = round(($volumeLiters / $maxCapacity) * 100, 1);
        $status = TankTelemetry::determineStatus($volumeLiters, $maxCapacity);

        $operatorName = auth()->user()?->username ?? 'Operator';

        if ($type === 'pemasukan') {
            $delta = round($volumeLiters - $initialVol, 1);
            $userNote = $validated['notes'] ? ' &mdash; '.$validated['notes'] : '';
            $notes = 'Pemasukan BBM (+'.max(0, $delta)." L, Ketinggian: {$inputHeight} cm){$userNote}";
            $source = 'pemasukan_bbm';
            $successMessage = "Pemasukan BBM {$tank->name} berhasil dicatat: Tinggi {$inputHeight} cm (".number_format($volumeLiters, 1).' L).';
        } else {
            $usedVol = round($initialVol - $volumeLiters, 1);
            $userNote = $validated['notes'] ? ' &mdash; '.$validated['notes'] : '';
            $notes = 'Pemakaian BBM (-'.max(0, $usedVol).' L dari awal '.number_format($initialVol, 1)." L, Sisa: {$volumeLiters} L){$userNote}";
            $source = 'pemakaian_bbm';
            $successMessage = "Pemakaian BBM {$tank->name} berhasil dicatat: Terpakai ".max(0, $usedVol)." L (Sisa {$inputHeight} cm / ".number_format($volumeLiters, 1).' L).';
        }

        $telemetry = $tank->telemetries()->create([
            'user_id' => auth()->id(),
            'volume_liters' => $volumeLiters,
            'percentage' => $percentage,
            'height_cm' => $inputHeight,
            'status' => $status,
            'source' => $source,
            'device_id' => 'OPERATOR-'.$operatorName,
            'notes' => $notes,
            'photo_path' => $photoPath,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'telemetry' => $telemetry,
                'volume_liters' => $volumeLiters,
                'percentage' => $percentage,
                'height_cm' => $inputHeight,
            ]);
        }

        return redirect()->back()->with('success', $successMessage);
    }
}
