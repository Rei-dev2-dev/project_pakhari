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

        // ── Main Header row (13 Kolom) ──────────────────────────────────────────
        $columns = [
            'A' => 'No',
            'B' => 'Nama Tangki',
            'C' => 'Petugas',
            'D' => 'Jenis Transaksi',
            'E' => 'Ketinggian (cm)',
            'F' => 'Kapasitas Tangki',
            'G' => 'Volume Awal',
            'H' => 'Volume Perubahan',
            'I' => 'Volume Akhir',
            'J' => 'Persentase (%)',
            'K' => 'Catatan',
            'L' => 'Foto Bukti',
            'M' => 'Waktu',
        ];

        foreach ($columns as $col => $label) {
            $sheet->setCellValue($col.'1', $label);
        }

        $sheet->getStyle('A1:M1')->applyFromArray([
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

        $sheet->getRowDimension(1)->setRowHeight(26);

        // Column widths
        $colWidths = [
            'A' => 6,
            'B' => 20,
            'C' => 18,
            'D' => 18,
            'E' => 16,
            'F' => 16,
            'G' => 15,
            'H' => 18,
            'I' => 15,
            'J' => 15,
            'K' => 28,
            'L' => 22,
            'M' => 20,
            'N' => 4, // Spacer
            'O' => 18, // Side Summary Table
            'P' => 16,
            'Q' => 18,
        ];

        foreach ($colWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // ── Main Data rows ─────────────────────────────────────────────────────
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

            $jenisFormatted = match ($log->source) {
                'pemasukan_bbm' => 'Pemasukan BBM',
                'pemakaian_bbm' => 'Pemakaian BBM',
                'manual_update' => 'Manual Update',
                default => ucwords(str_replace('_', ' ', $log->source)),
            };

            $volAwal = $log->volume_awal ?? ($log->volume_liters - ($log->volume_perubahan ?? 0));
            $volPerubahan = $log->volume_perubahan ?? 0;
            $volPerubahanStr = ($volPerubahan > 0 ? '+' : '').number_format($volPerubahan, 1).' L';

            $sheet->setCellValue('A'.$rowIndex, $no++);
            $sheet->setCellValue('B'.$rowIndex, $tank ? $tank->name : 'Tangki Utama');
            $sheet->setCellValue('C'.$rowIndex, $userName);
            $sheet->setCellValue('D'.$rowIndex, $jenisFormatted);
            $sheet->setCellValue('E'.$rowIndex, number_format($log->height_cm, 1).' cm');
            $sheet->setCellValue('F'.$rowIndex, number_format($tank ? $tank->capacity_liters : 100, 1).' L');
            $sheet->setCellValue('G'.$rowIndex, number_format($volAwal, 1).' L');
            $sheet->setCellValue('H'.$rowIndex, $volPerubahanStr);
            $sheet->setCellValue('I'.$rowIndex, number_format($log->volume_liters, 1).' L');
            $cleanNotes = $log->notes ? preg_replace('/\s*&mdash;\s*(.*?)$/', ' ($1)', $log->notes) : '-';
            $cleanNotes = str_replace('&mdash;', '-', $cleanNotes);

            $sheet->setCellValue('K'.$rowIndex, $cleanNotes);
            $sheet->setCellValue('M'.$rowIndex, $log->created_at->format('Y-m-d H:i:s'));

            $sheet->getStyle('A'.$rowIndex.':M'.$rowIndex)->applyFromArray($rowDataStyle);
            $sheet->getStyle('A'.$rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E'.$rowIndex.':J'.$rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('M'.$rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // ── Embed photo thumbnail on column L ──────────────────────────────
            $rowHeight = 22;

            if ($log->photo_path) {
                $physicalPath = storage_path('app/public/'.$log->photo_path);

                if (file_exists($physicalPath)) {
                    try {
                        $imgType = @exif_imagetype($physicalPath);
                        $supported = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];

                        if ($imgType && in_array($imgType, $supported)) {
                            [$imgW, $imgH] = @getimagesize($physicalPath) ?: [1, 1];
                            $isPortrait = $imgH >= $imgW;
                            $colWidthPx = 150;

                            if ($isPortrait) {
                                $imgDisplayH = (int) round($colWidthPx * 16 / 9);
                                $imgDisplayW = $colWidthPx;
                            } else {
                                $imgDisplayH = (int) round($colWidthPx * 9 / 16);
                                $imgDisplayW = $colWidthPx;
                            }

                            $rowHeightPt = ($imgDisplayH + 8) * 0.75;

                            $drawing = new Drawing;
                            $drawing->setName('Foto Bukti');
                            $drawing->setDescription('Foto Bukti');
                            $drawing->setPath($physicalPath);
                            $drawing->setCoordinates('L'.$rowIndex);
                            $drawing->setOffsetX(4);
                            $drawing->setOffsetY(4);
                            $drawing->setWidth($imgDisplayW);
                            $drawing->setHeight($imgDisplayH);
                            $drawing->setResizeProportional(false);
                            $drawing->setWorksheet($sheet);

                            $rowHeight = $rowHeightPt;
                        } else {
                            $sheet->setCellValue('L'.$rowIndex, asset('storage/'.$log->photo_path));
                        }
                    } catch (\Throwable) {
                        $sheet->setCellValue('L'.$rowIndex, asset('storage/'.$log->photo_path));
                    }
                } else {
                    $sheet->setCellValue('L'.$rowIndex, asset('storage/'.$log->photo_path));
                }
            } else {
                $sheet->setCellValue('L'.$rowIndex, '-');
            }

            $sheet->getRowDimension($rowIndex)->setRowHeight($rowHeight);
            $rowIndex++;
        }

        // ── Side Summary Table (Ringkasan Kapasitas & Volume Akhir Per Tangki) ──
        $allTanks = Tank::where('is_active', true)->orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();
        if ($allTanks->isEmpty()) {
            $allTanks = Tank::orderBy('id', 'asc')->get();
        }

        // Header for Side Table
        $sheet->setCellValue('O1', '');
        $sheet->setCellValue('P1', 'Kapasitas');
        $sheet->setCellValue('Q1', 'Volume AKHIR');

        $sideHeaderStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '000000'], 'size' => 10],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];
        $sheet->getStyle('O1:Q1')->applyFromArray($sideHeaderStyle);

        $sideRow = 2;
        $totalCap = 0.0;
        $totalAkhir = 0.0;

        foreach ($allTanks as $t) {
            $latestTel = $t->latestTelemetry();
            $capVal = (float) $t->capacity_liters;
            $akhirVal = $latestTel ? (float) $latestTel->volume_liters : 0.0;

            $totalCap += $capVal;
            $totalAkhir += $akhirVal;

            $sheet->setCellValue('O'.$sideRow, $t->name);
            $sheet->setCellValue('P'.$sideRow, number_format($capVal, 1).' L');
            $sheet->setCellValue('Q'.$sideRow, number_format($akhirVal, 1).' L');

            $sheet->getStyle('O'.$sideRow.':Q'.$sideRow)->applyFromArray([
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);
            $sheet->getStyle('O'.$sideRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('P'.$sideRow.':Q'.$sideRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $sideRow++;
        }

        // Total Row for Side Table
        $sheet->setCellValue('O'.$sideRow, 'TOTAL');
        $sheet->setCellValue('P'.$sideRow, number_format($totalCap, 1).' L');
        $sheet->setCellValue('Q'.$sideRow, number_format($totalAkhir, 1).' L');

        $sheet->getStyle('O'.$sideRow.':Q'.$sideRow)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '000000'], 'size' => 10],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFE599'], // Light yellow/orange highlight matching screenshot
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
        $sheet->getStyle('O'.$sideRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('P'.$sideRow.':Q'.$sideRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

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
