<?php

namespace App\Http\Controllers;

use App\Models\Tank;
use App\Models\TankTelemetry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChartController extends Controller
{
    /**
     * Display charts and analytics for all tanks with filtering.
     */
    public function index(Request $request): View
    {
        $selectedYear = (int) $request->input('year', date('Y'));
        $selectedMonth = $request->filled('month') && $request->input('month') !== '' ? (int) $request->input('month') : null;
        $selectedTankId = $request->filled('tank_id') && $request->input('tank_id') !== '' ? (int) $request->input('tank_id') : null;
        $selectedMetric = $request->input('metric', 'volume'); // volume, comparison, pemasukan, pemakaian

        // Fetch available years from telemetry history
        // Uses Eloquent whereNotNull + PHP-side year extraction for DB compatibility
        $availableYears = TankTelemetry::whereNotNull('created_at')
            ->pluck('created_at')
            ->map(fn ($d) => (int) date('Y', strtotime((string) $d)))
            ->push((int) date('Y'))
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        if (empty($availableYears)) {
            $availableYears = [(int) date('Y')];
        }

        // Fetch active tanks (automatically integrates any newly added tanks)
        $tanksQuery = Tank::where('is_active', true)->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
        $allTanks = $tanksQuery->get();

        $tanks = $selectedTankId
            ? $allTanks->where('id', $selectedTankId)
            : $allTanks;

        $chartLabels = [];
        $tankCodes = [];
        $currentVolumes = [];
        $maxCapacities = [];
        $currentPercentages = [];
        $pemasukanCounts = [];
        $pemakaianCounts = [];
        $tableData = [];

        $totalCapacity = 0.0;
        $totalCurrentVolume = 0.0;
        $totalPemasukanCount = 0;
        $totalPemakaianCount = 0;

        foreach ($tanks as $tank) {
            $latest = $tank->telemetries()->latest()->first();
            $curVol = $latest ? (float) $latest->volume_liters : 0.0;
            $curPct = $latest ? (float) $latest->percentage : ($tank->capacity_liters > 0 ? round(($curVol / $tank->capacity_liters) * 100, 1) : 0.0);
            $curHeight = $latest ? (float) $latest->height_cm : 0.0;
            $status = $latest ? $latest->status : TankTelemetry::determineStatus($curVol, (float) $tank->capacity_liters);

            $telemetriesQuery = $tank->telemetries();
            if ($selectedYear) {
                $telemetriesQuery->whereYear('created_at', $selectedYear);
            }
            if ($selectedMonth) {
                $telemetriesQuery->whereMonth('created_at', $selectedMonth);
            }

            $inflowCount = (clone $telemetriesQuery)->where('source', 'pemasukan_bbm')->count();
            $outflowCount = (clone $telemetriesQuery)->where('source', 'pemakaian_bbm')->count();

            $chartLabels[] = $tank->name;
            $tankCodes[] = $tank->code;
            $currentVolumes[] = $curVol;
            $maxCapacities[] = (float) $tank->capacity_liters;
            $currentPercentages[] = $curPct;
            $pemasukanCounts[] = $inflowCount;
            $pemakaianCounts[] = $outflowCount;

            $totalCapacity += (float) $tank->capacity_liters;
            $totalCurrentVolume += $curVol;
            $totalPemasukanCount += $inflowCount;
            $totalPemakaianCount += $outflowCount;

            $tableData[] = [
                'id' => $tank->id,
                'name' => $tank->name,
                'code' => $tank->code,
                'capacity' => (float) $tank->capacity_liters,
                'current_volume' => $curVol,
                'percentage' => $curPct,
                'height_cm' => $curHeight,
                'status' => $status,
                'pemasukan_count' => $inflowCount,
                'pemakaian_count' => $outflowCount,
            ];
        }

        $overallPct = $totalCapacity > 0 ? round(($totalCurrentVolume / $totalCapacity) * 100, 1) : 0.0;

        // Monthly Breakdown for the selected Year (Jan - Dec)
        $monthlyLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $monthlyPemasukan = array_fill(0, 12, 0);
        $monthlyPemakaian = array_fill(0, 12, 0);

        $monthStatsQuery = TankTelemetry::whereYear('created_at', $selectedYear);
        if ($selectedTankId) {
            $monthStatsQuery->where('tank_id', $selectedTankId);
        }

        // DB-agnostic month extraction: MySQL uses MONTH(), SQLite uses strftime
        $driver = config('database.default');
        $monthExpr = $driver === 'sqlite'
            ? 'CAST(strftime("%m", created_at) AS INTEGER) as bln'
            : 'MONTH(created_at) as bln';

        $monthStats = $monthStatsQuery->selectRaw("
                {$monthExpr},
                source,
                COUNT(*) as total_transaksi,
                SUM(volume_liters) as total_vol
            ")
            ->whereIn('source', ['pemasukan_bbm', 'pemakaian_bbm'])
            ->groupBy('bln', 'source')
            ->get();

        foreach ($monthStats as $stat) {
            $idx = (int) $stat->bln - 1;
            if ($idx >= 0 && $idx < 12) {
                if ($stat->source === 'pemasukan_bbm') {
                    $monthlyPemasukan[$idx] = (int) $stat->total_transaksi;
                } elseif ($stat->source === 'pemakaian_bbm') {
                    $monthlyPemakaian[$idx] = (int) $stat->total_transaksi;
                }
            }
        }

        return view('chart.index', [
            'allTanks' => $allTanks,
            'selectedYear' => $selectedYear,
            'selectedMonth' => $selectedMonth,
            'selectedTankId' => $selectedTankId,
            'selectedMetric' => $selectedMetric,
            'availableYears' => $availableYears,
            'chartLabels' => $chartLabels,
            'tankCodes' => $tankCodes,
            'currentVolumes' => $currentVolumes,
            'maxCapacities' => $maxCapacities,
            'currentPercentages' => $currentPercentages,
            'pemasukanCounts' => $pemasukanCounts,
            'pemakaianCounts' => $pemakaianCounts,
            'tableData' => $tableData,
            'totalCapacity' => $totalCapacity,
            'totalCurrentVolume' => $totalCurrentVolume,
            'overallPct' => $overallPct,
            'totalPemasukanCount' => $totalPemasukanCount,
            'totalPemakaianCount' => $totalPemakaianCount,
            'monthlyLabels' => $monthlyLabels,
            'monthlyPemasukan' => $monthlyPemasukan,
            'monthlyPemakaian' => $monthlyPemakaian,
        ]);
    }
}
