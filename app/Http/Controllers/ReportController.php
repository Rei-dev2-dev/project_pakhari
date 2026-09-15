<?php

namespace App\Http\Controllers;

use App\Models\Tank;
use App\Models\TankTelemetry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /** Source values mapped from UI filter names. */
    private const SOURCE_MAP = [
        'pemasukan' => 'pemasukan_bbm',
        'pemakaian' => 'pemakaian_bbm',
    ];

    /**
     * Display report filtering and preview page.
     */
    public function index(Request $request): View
    {
        $tanks = Tank::orderBy('sort_order', 'asc')->get();

        $query = TankTelemetry::with('tank')->latest();

        if ($request->filled('tank_id')) {
            $query->where('tank_id', $request->input('tank_id'));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        $jenis = $request->input('jenis');
        if ($jenis && isset(self::SOURCE_MAP[$jenis])) {
            $query->where('source', self::SOURCE_MAP[$jenis]);
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('laporan.index', [
            'tanks' => $tanks,
            'logs' => $logs,
            'selectedTankId' => $request->input('tank_id'),
            'startDate' => $request->input('start_date'),
            'endDate' => $request->input('end_date'),
            'selectedJenis' => $jenis,
        ]);
    }

    /**
     * Export telemetry logs to Excel-compatible CSV format.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = TankTelemetry::with('tank')->latest();

        $tankName = 'Semua-Tangki';
        if ($request->filled('tank_id')) {
            $query->where('tank_id', $request->input('tank_id'));
            $tank = Tank::find($request->input('tank_id'));
            if ($tank) {
                $tankName = str_replace(' ', '_', $tank->name);
            }
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        $jenis = $request->input('jenis');
        if ($jenis && isset(self::SOURCE_MAP[$jenis])) {
            $query->where('source', self::SOURCE_MAP[$jenis]);
        }

        $fileName = 'Laporan_Monitoring_'.$tankName.'_'.now()->format('Ymd_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility (ensures Indonesian accents and special characters render cleanly)
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Column Headers
            fputcsv($handle, [
                'No',
                'Nama Tangki',
                'Kode Tangki',
                'Kapasitas Maksimal (Liter)',
                'Volume (Liter)',
                'Persentase (%)',
                'Ketinggian (cm)',
                'Status',
                'Jenis Transaksi',
                'Device ID',
                'Catatan',
                'Foto Bukti (URL)',
                'Waktu',
            ]);

            $index = 1;
            $query->chunk(200, function ($records) use ($handle, &$index) {
                foreach ($records as $log) {
                    $tank = $log->tank;
                    fputcsv($handle, [
                        $index++,
                        $tank ? $tank->name : 'Tangki Utama',
                        $tank ? $tank->code : 'TNK-01',
                        $tank ? number_format($tank->capacity_liters, 1) : '100.0',
                        number_format($log->volume_liters, 2),
                        number_format($log->percentage, 2).'%',
                        number_format($log->height_cm, 2).' cm',
                        ucfirst(str_replace('_', ' ', $log->status)),
                        $log->source,
                        $log->device_id ?? '-',
                        $log->notes ?? '-',
                        $log->photo_path ? Storage::disk('public')->url($log->photo_path) : '-',
                        $log->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
