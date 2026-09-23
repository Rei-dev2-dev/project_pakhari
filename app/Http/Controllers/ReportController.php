<?php

namespace App\Http\Controllers;

use App\Models\Tank;
use App\Models\TankTelemetry;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

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

        $query = TankTelemetry::with(['tank', 'user'])->latest();

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
     * Export telemetry logs to Excel (.xlsx) with embedded photo thumbnails.
     */
    public function export(Request $request): Response
    {
        if (! auth()->user()->hasRole(['admin', 'superadmin'])) {
            abort(403, 'Akses ekspor laporan hanya untuk Admin dan SuperAdmin.');
        }

        $query = TankTelemetry::with(['tank', 'user'])->latest();

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

        $fileName = 'Laporan_Monitoring_'.$tankName.'_'.now()->format('Ymd_His').'.xlsx';

        // ── Build Spreadsheet ──────────────────────────────────────────────────
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan BBM');

        // ── Header row ─────────────────────────────────────────────────────────
        $columns = [
            'A' => 'No',
            'B' => 'Nama Tangki',
            'C' => 'Kode Tangki',
            'D' => 'Kapasitas (L)',
            'E' => 'Volume (L)',
            'F' => 'Persentase (%)',
            'G' => 'Ketinggian (cm)',
            'H' => 'Status',
            'I' => 'Jenis Transaksi',
            'J' => 'Petugas / User',
            'K' => 'Device ID',
            'L' => 'Catatan',
            'M' => 'Foto Bukti',
            'N' => 'Waktu',
        ];

        foreach ($columns as $col => $label) {
            $sheet->setCellValue($col.'1', $label);
        }

        $sheet->getStyle('A1:N1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3A5F'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '4A90D9'],
                ],
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(24);

        // Column widths
        foreach (['A' => 5, 'B' => 22, 'C' => 12, 'D' => 14, 'E' => 12, 'F' => 14, 'G' => 14, 'H' => 14, 'I' => 18, 'J' => 20, 'K' => 24, 'L' => 32, 'M' => 20, 'N' => 20] as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // ── Data rows ──────────────────────────────────────────────────────────
        $records = $query->get();
        $rowDataStyle = [
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D0D7E4'],
                ],
            ],
        ];

        $no = 1;
        $rowIndex = 2;

        foreach ($records as $log) {
            $tank = $log->tank;
            $userName = $log->user ? $log->user->name : ($log->device_id ? str_replace(['OPERATOR-', 'MANUAL-'], '', $log->device_id) : 'Sistem');

            $sheet->setCellValue('A'.$rowIndex, $no++);
            $sheet->setCellValue('B'.$rowIndex, $tank ? $tank->name : 'Tangki Utama');
            $sheet->setCellValue('C'.$rowIndex, $tank ? $tank->code : 'TNK-01');
            $sheet->setCellValue('D'.$rowIndex, $tank ? number_format($tank->capacity_liters, 1) : '100.0');
            $sheet->setCellValue('E'.$rowIndex, number_format($log->volume_liters, 2));
            $sheet->setCellValue('F'.$rowIndex, number_format($log->percentage, 2).'%');
            $sheet->setCellValue('G'.$rowIndex, number_format($log->height_cm, 2).' cm');
            $sheet->setCellValue('H'.$rowIndex, ucfirst(str_replace('_', ' ', $log->status)));
            $sheet->setCellValue('I'.$rowIndex, $log->source);
            $sheet->setCellValue('J'.$rowIndex, $userName);
            $sheet->setCellValue('K'.$rowIndex, $log->device_id ?? '-');
            $sheet->setCellValue('L'.$rowIndex, $log->notes ?? '-');
            $sheet->setCellValue('N'.$rowIndex, $log->created_at->format('Y-m-d H:i:s'));

            $sheet->getStyle('A'.$rowIndex.':N'.$rowIndex)->applyFromArray($rowDataStyle);
            $sheet->getStyle('A'.$rowIndex)->getAlignment()->setHorizontal(
                Alignment::HORIZONTAL_CENTER
            );

            // ── Embed photo thumbnail ──────────────────────────────────────────
            $rowHeight = 20;

            if ($log->photo_path) {
                $physicalPath = storage_path('app/public/'.$log->photo_path);

                if (file_exists($physicalPath)) {
                    try {
                        $imgType = @exif_imagetype($physicalPath);
                        $supported = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];

                        if ($imgType && in_array($imgType, $supported)) {
                            [$imgW, $imgH] = @getimagesize($physicalPath) ?: [1, 1];
                            $isPortrait = $imgH >= $imgW;

                            // Column M width in Excel units (~7.5px per unit)
                            // Portrait: fill column height at 9:16 → col width stays 20 units ≈ 150px
                            // Landscape: fill column width at 16:9 → col width stays 20 units ≈ 150px
                            $colWidthPx = 150; // approx pixels for 20 Excel column-width units

                            if ($isPortrait) {
                                // 9:16 → height = colWidth * 16/9
                                $imgDisplayH = (int) round($colWidthPx * 16 / 9);
                                $imgDisplayW = $colWidthPx;
                            } else {
                                // 16:9 → height = colWidth * 9/16
                                $imgDisplayH = (int) round($colWidthPx * 9 / 16);
                                $imgDisplayW = $colWidthPx;
                            }

                            // Excel row height is in points (≈ 0.75px); add 8px padding
                            $rowHeightPt = ($imgDisplayH + 8) * 0.75;

                            $drawing = new Drawing;
                            $drawing->setName('Foto Bukti');
                            $drawing->setDescription('Foto Bukti');
                            $drawing->setPath($physicalPath);
                            $drawing->setCoordinates('M'.$rowIndex);
                            $drawing->setOffsetX(4);
                            $drawing->setOffsetY(4);
                            $drawing->setWidth($imgDisplayW);
                            $drawing->setHeight($imgDisplayH);
                            $drawing->setResizeProportional(false);
                            $drawing->setWorksheet($sheet);

                            $rowHeight = $rowHeightPt;
                        } else {
                            $sheet->setCellValue('M'.$rowIndex, asset('storage/'.$log->photo_path));
                        }
                    } catch (\Throwable) {
                        $sheet->setCellValue('M'.$rowIndex, asset('storage/'.$log->photo_path));
                    }
                } else {
                    $sheet->setCellValue('M'.$rowIndex, asset('storage/'.$log->photo_path));
                }
            } else {
                $sheet->setCellValue('M'.$rowIndex, '-');
            }

            $sheet->getRowDimension($rowIndex)->setRowHeight($rowHeight);
            $rowIndex++;
        }

        // Freeze top header row while scrolling
        $sheet->freezePane('A2');

        // ── Stream .xlsx to browser ────────────────────────────────────────────
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
