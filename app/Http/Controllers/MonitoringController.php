<?php

namespace App\Http\Controllers;

use App\Models\RiwayatTransaksiBbm;
use App\Models\Tank;
use App\Models\TankTelemetry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'notes' => $validated['notes'] ?? 'Pembaruan level BBM (Solar)',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Level BBM tangki berhasil diperbarui.',
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
            'photo' => ['nullable'],
            'photo_cam' => ['nullable'],
            'photo_base64' => ['nullable', 'string'],
        ], [
            'height_cm.max' => "Ketinggian BBM tidak boleh melebihi tinggi maksimal tangki ({$maxHeight} cm) yang ditentukan oleh Admin.",
            'height_cm.min' => 'Ketinggian BBM tidak boleh bernilai negatif.',
        ]);

        $inputHeight = (float) $validated['height_cm'];
        $type = $validated['type'];

        // Handle photo upload if present (Supports base64 string from canvas, camera input, and file upload)
        $photoPath = null;
        if (! empty($validated['photo_base64'])) {
            $base64Data = $validated['photo_base64'];
            $ext = 'jpg';
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $typeMatch)) {
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
                $ext = strtolower($typeMatch[1]);
                if ($ext === 'jpeg') {
                    $ext = 'jpg';
                }
            }
            $decoded = base64_decode($base64Data);
            if ($decoded !== false) {
                $fileName = 'proof_'.now()->format('Ymd_His').'_'.uniqid().'.'.$ext;
                Storage::disk('public')->put('telemetry_proofs/'.$fileName, $decoded);
                $photoPath = 'telemetry_proofs/'.$fileName;
            }
        } elseif ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $photoPath = $request->file('photo')->store('telemetry_proofs', 'public');
        } elseif ($request->hasFile('photo_cam') && $request->file('photo_cam')->isValid()) {
            $photoPath = $request->file('photo_cam')->store('telemetry_proofs', 'public');
        }

        if (! $photoPath) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Foto bukti wajib dilampirkan.',
                    'errors' => ['photo' => ['Foto bukti wajib dilampirkan saat melakukan input BBM.']],
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['photo' => 'Foto bukti wajib dilampirkan saat melakukan input BBM.']);
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
            $userNote = ! empty($validated['notes']) ? ' ('.$validated['notes'].')' : '';
            $notes = 'Pemasukan BBM (+'.max(0, $delta)." L, Ketinggian: {$inputHeight} cm){$userNote}";
            $source = 'pemasukan_bbm';
            $successMessage = "Pemasukan BBM {$tank->name} berhasil dicatat: Tinggi {$inputHeight} cm (".number_format($volumeLiters, 1).' L).';
        } else {
            $usedVol = round($initialVol - $volumeLiters, 1);
            $userNote = ! empty($validated['notes']) ? ' ('.$validated['notes'].')' : '';
            $notes = 'Pemakaian BBM (-'.max(0, $usedVol).' L dari awal '.number_format($initialVol, 1)." L, Sisa: {$volumeLiters} L){$userNote}";
            $source = 'pemakaian_bbm';
            $successMessage = "Pemakaian BBM {$tank->name} berhasil dicatat: Terpakai ".max(0, $usedVol)." L (Sisa {$inputHeight} cm / ".number_format($volumeLiters, 1).' L).';
        }

        $telemetry = $tank->telemetries()->create([
            'user_id' => auth()->id(),
            'volume_awal' => $initialVol,
            'volume_perubahan' => $type === 'pemasukan' ? $delta : -abs($usedVol),
            'volume_liters' => $volumeLiters,
            'percentage' => $percentage,
            'height_cm' => $inputHeight,
            'status' => $status,
            'source' => $source,
            'device_id' => 'OPERATOR-'.$operatorName,
            'notes' => $notes,
            'photo_path' => $photoPath,
        ]);

        RiwayatTransaksiBbm::create([
            'tank_id' => $tank->id,
            'user_id' => auth()->id(),
            'jenis_transaksi' => $type,
            'volume_awal' => $initialVol,
            'volume_perubahan' => $type === 'pemasukan' ? $delta : -abs($usedVol),
            'volume_akhir' => $volumeLiters,
            'ketinggian_awal_cm' => $initialHeight,
            'ketinggian_akhir_cm' => $inputHeight,
            'nomor_do' => $type === 'pemasukan' ? ($validated['notes'] ?? null) : null,
            'unit_tujuan' => $type === 'pemakaian' ? ($validated['notes'] ?? null) : null,
            'foto_bukti' => $photoPath,
            'catatan' => $validated['notes'] ?? null,
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
