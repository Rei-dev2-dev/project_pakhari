@extends('layouts.app')

@section('title', 'Grafik & Analisis Tangki')
@section('page-title', 'Grafik & Analisis Volume Tangki BBM')

@section('page-badge')
    <span class="text-xs px-2.5 py-1 rounded-lg bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 font-mono font-semibold">
        {{ count($tableData) }} Tangki Terintegrasi
    </span>
@endsection

@section('content')
<div class="space-y-6">
    
    <!-- Filter Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl">
        <form action="{{ route('chart.index') }}" method="GET" class="space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <span class="text-xs font-bold text-white uppercase tracking-wider">Filter Grafik & Data Tangki</span>
                </div>

                @if(request()->hasAny(['year', 'month', 'tank_id', 'metric']))
                    <a href="{{ route('chart.index') }}" class="text-[11px] text-slate-400 hover:text-rose-400 transition">
                        Reset Filter
                    </a>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Select Year -->
                <div>
                    <label for="year" class="block text-xs font-semibold text-slate-300 mb-1.5">Tahun</label>
                    <select name="year" id="year" class="w-full px-3 py-2 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-cyan-500 font-mono">
                        @foreach($availableYears as $yr)
                            <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>
                                Tahun {{ $yr }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Select Month -->
                <div>
                    <label for="month" class="block text-xs font-semibold text-slate-300 mb-1.5">Bulan</label>
                    <select name="month" id="month" class="w-full px-3 py-2 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-cyan-500">
                        <option value="">Semua Bulan (Tahunan)</option>
                        @php
                            $monthsList = [
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ];
                        @endphp
                        @foreach($monthsList as $num => $name)
                            <option value="{{ $num }}" {{ $selectedMonth == $num ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Select Tank -->
                <div>
                    <label for="tank_id" class="block text-xs font-semibold text-slate-300 mb-1.5">Pilihan Tangki</label>
                    <select name="tank_id" id="tank_id" class="w-full px-3 py-2 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-cyan-500">
                        <option value="">Semua Tangki Aktif</option>
                        @foreach($allTanks as $t)
                            <option value="{{ $t->id }}" {{ $selectedTankId == $t->id ? 'selected' : '' }}>
                                {{ $t->name }} ({{ $t->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Select Metric -->
                <div>
                    <label for="metric" class="block text-xs font-semibold text-slate-300 mb-1.5">Metrik Tampilan</label>
                    <select name="metric" id="metric" class="w-full px-3 py-2 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-cyan-500">
                        <option value="volume" {{ $selectedMetric === 'volume' ? 'selected' : '' }}>Volume BBM Saat Ini (Liter)</option>
                        <option value="comparison" {{ $selectedMetric === 'comparison' ? 'selected' : '' }}>Perbandingan (Volume vs Kapasitas)</option>
                        <option value="pemasukan" {{ $selectedMetric === 'pemasukan' ? 'selected' : '' }}>Frekuensi Pemasukan BBM</option>
                        <option value="pemakaian" {{ $selectedMetric === 'pemakaian' ? 'selected' : '' }}>Frekuensi Pemakaian BBM</option>
                    </select>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between gap-3 pt-2">
                <span class="text-[11px] text-slate-400">
                    Menampilkan data periode: <strong class="text-slate-200">{{ $selectedMonth ? $monthsList[$selectedMonth] : 'Sepanjang Tahun' }} {{ $selectedYear }}</strong>
                </span>
                <button type="submit" class="px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold shadow-lg shadow-cyan-600/20 transition flex items-center gap-2">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>Terapkan Filter</span>
                </button>
            </div>
        </form>
    </div>

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Kapasitas -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Total Kapasitas</span>
                <div class="text-2xl font-black text-white font-mono mt-1">
                    {{ number_format($totalCapacity, 1) }} <span class="text-xs font-sans text-slate-400 font-normal">Liter</span>
                </div>
                <span class="text-[10px] text-slate-500 mt-1 block">Kapasitas seluruh tangki terdaftar</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-sky-500/10 border border-sky-500/20 flex items-center justify-center text-sky-400 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
        </div>

        <!-- Card 2: Total Stok BBM Tersedia -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Stok BBM Tersedia</span>
                <div class="text-2xl font-black text-cyan-400 font-mono mt-1">
                    {{ number_format($totalCurrentVolume, 1) }} <span class="text-xs font-sans text-slate-400 font-normal">Liter</span>
                </div>
                <span class="text-[10px] text-emerald-400 font-medium mt-1 block flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Volume riil saat ini
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
        </div>

        <!-- Card 3: Rata-rata Terisi -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Rata-rata Terisi</span>
                <div class="text-2xl font-black text-amber-400 font-mono mt-1">
                    {{ number_format($overallPct, 1) }}%
                </div>
                <span class="text-[10px] text-slate-500 mt-1 block">Rasio okupansi volume tangki</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
        </div>

        <!-- Card 4: Total Transaksi -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Total Transaksi</span>
                <div class="text-2xl font-black text-purple-400 font-mono mt-1">
                    {{ $totalPemasukanCount + $totalPemakaianCount }}
                </div>
                <span class="text-[10px] text-slate-400 mt-1 block">
                    <span class="text-blue-400">{{ $totalPemasukanCount }} Masuk</span> &bull; <span class="text-orange-400">{{ $totalPemakaianCount }} Pakai</span>
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
            </div>
        </div>
    </div>

    <!-- MAIN CHART CARD: Vertical Bar Chart Per Tank -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-800 pb-4">
            <div>
                <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <span>Grafik Volume BBM Per Tangki</span>
                    <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                        {{ $selectedMonth ? $monthsList[$selectedMonth] : 'Tahunan' }} {{ $selectedYear }}
                    </span>
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Grafik batang otomatis terintegrasi langsung dengan setiap penambahan tangki baru oleh Admin.</p>
            </div>

            <!-- Chart Quick View Switcher -->
            <div class="flex items-center space-x-1.5 bg-slate-950 p-1 rounded-xl border border-slate-800">
                <button type="button" onclick="switchChartMode('bar')" id="btnChartBar" class="px-3 py-1 rounded-lg text-xs font-semibold bg-cyan-600 text-white shadow transition">
                    Batang
                </button>
                <button type="button" onclick="switchChartMode('line')" id="btnChartLine" class="px-3 py-1 rounded-lg text-xs font-semibold text-slate-400 hover:text-white transition">
                    Garis
                </button>
            </div>
        </div>

        <!-- Canvas Container -->
        <div class="relative w-full h-[380px]">
            <canvas id="tankMainChart"></canvas>
        </div>
    </div>

    <!-- SECONDARY CHARTS GRID: 2 COLUMNS -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left: Donut Chart Distribusi Volume Tangki -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4">
            <div class="border-b border-slate-800 pb-3">
                <h3 class="text-xs font-bold text-white uppercase tracking-wider">Distribusi Proporsi Stok BBM</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Persentase perbandingan volume antar tangki</p>
            </div>
            <div class="relative w-full h-[260px] flex items-center justify-center">
                <canvas id="tankDistributionChart"></canvas>
            </div>
        </div>

        <!-- Right: Tren Transaksi Bulanan (Jan - Des) -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4">
            <div class="border-b border-slate-800 pb-3">
                <h3 class="text-xs font-bold text-white uppercase tracking-wider">Tren Transaksi BBM Bulanan ({{ $selectedYear }})</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Frekuensi aktivitas pemasukan vs pemakaian BBM per bulan</p>
            </div>
            <div class="relative w-full h-[260px]">
                <canvas id="monthlyTrendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- DATA TABLE: Rekapitulasi Data Tangki -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
            <h3 class="text-xs font-bold text-white uppercase tracking-wider">Tabel Rincian Volume & Status Tangki</h3>
            <span class="text-xs text-slate-400 font-mono">{{ count($tableData) }} Tangki Terdaftar</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-950/60 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider text-[10px]">
                        <th class="py-3 px-4">Kode</th>
                        <th class="py-3 px-4">Nama Tangki</th>
                        <th class="py-3 px-4">Kapasitas Maks</th>
                        <th class="py-3 px-4">Volume Terkini</th>
                        <th class="py-3 px-4">Persentase</th>
                        <th class="py-3 px-4">Ketinggian</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Transaksi Periode</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-mono text-[11px]">
                    @forelse($tableData as $row)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3 px-4 font-bold text-cyan-400">{{ $row['code'] }}</td>
                            <td class="py-3 px-4 font-sans font-bold text-white">{{ $row['name'] }}</td>
                            <td class="py-3 px-4 text-slate-300">{{ number_format($row['capacity'], 1) }} L</td>
                            <td class="py-3 px-4 font-bold text-white">{{ number_format($row['current_volume'], 1) }} L</td>
                            <td class="py-3 px-4">
                                <div class="flex items-center space-x-2">
                                    <div class="w-16 bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-1.5 rounded-full {{ $row['percentage'] >= 95 ? 'bg-rose-500' : ($row['percentage'] <= 20 ? 'bg-amber-500' : 'bg-cyan-500') }}" style="width: {{ min(100, $row['percentage']) }}%"></div>
                                    </div>
                                    <span class="text-slate-300 font-bold">{{ number_format($row['percentage'], 1) }}%</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-slate-400">{{ number_format($row['height_cm'], 1) }} cm</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold
                                    @if($row['status'] === 'warning_full') bg-rose-500/10 text-rose-400 border border-rose-500/30
                                    @elseif($row['status'] === 'low') bg-amber-500/10 text-amber-400 border border-amber-500/30
                                    @else bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 @endif">
                                    {{ strtoupper(str_replace('_', ' ', $row['status'])) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-sans">
                                <span class="text-blue-400 font-mono font-semibold">{{ $row['pemasukan_count'] }} Masuk</span> / 
                                <span class="text-orange-400 font-mono font-semibold">{{ $row['pemakaian_count'] }} Pakai</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('monitoring.show', $row['id']) }}" title="Lihat Visual Tangki 3D" class="inline-flex items-center justify-center p-1.5 rounded-lg bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-500 font-sans">Tidak ada data tangki ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // --- COLOR PALETTE (Vibrant Cylindrical / 3D-like Style) ---
    const TANK_COLORS = [
        { bg: 'rgba(56, 189, 248, 0.85)', border: '#38bdf8', hover: 'rgba(56, 189, 248, 1)' },   // Sky Blue
        { bg: 'rgba(251, 191, 36, 0.85)', border: '#fbbf24', hover: 'rgba(251, 191, 36, 1)' },   // Amber Gold
        { bg: 'rgba(132, 204, 22, 0.85)', border: '#84cc16', hover: 'rgba(132, 204, 22, 1)' },   // Lime Green
        { bg: 'rgba(249, 115, 22, 0.85)', border: '#f97316', hover: 'rgba(249, 115, 22, 1)' },   // Orange Coral
        { bg: 'rgba(20, 184, 166, 0.85)', border: '#14b8a6', hover: 'rgba(20, 184, 166, 1)' },   // Teal
        { bg: 'rgba(244, 63, 94, 0.85)',  border: '#f43f5e', hover: 'rgba(244, 63, 94, 1)' },   // Crimson Red
        { bg: 'rgba(168, 85, 247, 0.85)', border: '#a855f7', hover: 'rgba(168, 85, 247, 1)' },   // Purple
        { bg: 'rgba(99, 102, 241, 0.85)', border: '#6366f1', hover: 'rgba(99, 102, 241, 1)' },   // Indigo
    ];

    const labels = @json($chartLabels);
    const codes = @json($tankCodes);
    const volumes = @json($currentVolumes);
    const capacities = @json($maxCapacities);
    const percentages = @json($currentPercentages);
    const selectedMetric = "{{ $selectedMetric }}";

    // Generate bar background and border colors dynamically for any number of tanks
    const barBgColors = labels.map((_, i) => TANK_COLORS[i % TANK_COLORS.length].bg);
    const barBorderColors = labels.map((_, i) => TANK_COLORS[i % TANK_COLORS.length].border);

    // --- 1. MAIN VERTICAL BAR CHART ---
    const ctxMain = document.getElementById('tankMainChart').getContext('2d');

    let mainDatasets = [];
    if (selectedMetric === 'comparison') {
        mainDatasets = [
            {
                label: 'Volume Terkini (Liter)',
                data: volumes,
                backgroundColor: 'rgba(56, 189, 248, 0.85)',
                borderColor: '#38bdf8',
                borderWidth: 2,
                borderRadius: 8,
                barPercentage: 0.6,
            },
            {
                label: 'Kapasitas Maksimal (Liter)',
                data: capacities,
                backgroundColor: 'rgba(148, 163, 184, 0.3)',
                borderColor: '#64748b',
                borderWidth: 2,
                borderRadius: 8,
                barPercentage: 0.6,
            }
        ];
    } else if (selectedMetric === 'pemasukan') {
        mainDatasets = [{
            label: 'Total Frekuensi Pemasukan BBM',
            data: @json($pemasukanCounts),
            backgroundColor: 'rgba(59, 130, 246, 0.85)',
            borderColor: '#3b82f6',
            borderWidth: 2,
            borderRadius: 8,
            barPercentage: 0.5,
        }];
    } else if (selectedMetric === 'pemakaian') {
        mainDatasets = [{
            label: 'Total Frekuensi Pemakaian BBM',
            data: @json($pemakaianCounts),
            backgroundColor: 'rgba(249, 115, 22, 0.85)',
            borderColor: '#f97316',
            borderWidth: 2,
            borderRadius: 8,
            barPercentage: 0.5,
        }];
    } else {
        // Default: Volume Saat Ini dengan warna per tangki (gaya Salesperson pada gambar)
        mainDatasets = [{
            label: 'Volume BBM (Liter)',
            data: volumes,
            backgroundColor: barBgColors,
            borderColor: barBorderColors,
            borderWidth: 2,
            borderRadius: 12,
            borderSkipped: false,
            barPercentage: 0.55,
        }];
    }

    let mainChart = new Chart(ctxMain, {
        type: 'bar',
        data: {
            labels: labels.map((name, i) => `${name} (${codes[i]})`),
            datasets: mainDatasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: selectedMetric === 'comparison',
                    labels: {
                        color: '#94a3b8',
                        font: { family: 'Plus Jakarta Sans', size: 12 }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                    titleColor: '#ffffff',
                    bodyColor: '#38bdf8',
                    borderColor: '#334155',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            const idx = context.dataIndex;
                            const val = context.raw;
                            if (selectedMetric === 'pemasukan' || selectedMetric === 'pemakaian') {
                                return ` ${val} Transaksi`;
                            }
                            return ` Volume: ${val.toLocaleString('id-ID')} Liter (${percentages[idx]}% dari ${capacities[idx]} L)`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(51, 65, 85, 0.4)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#94a3b8',
                        font: { family: 'JetBrains Mono', size: 11 },
                        callback: function(value) {
                            return value + (selectedMetric.includes('pemasukan') || selectedMetric.includes('pemakaian') ? ' trx' : ' L');
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#cbd5e1',
                        font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' }
                    }
                }
            }
        }
    });

    // Chart Mode Switcher (Bar vs Line)
    window.switchChartMode = function(mode) {
        mainChart.config.type = mode;
        mainChart.update();

        const btnBar = document.getElementById('btnChartBar');
        const btnLine = document.getElementById('btnChartLine');
        if (mode === 'bar') {
            btnBar.className = 'px-3 py-1 rounded-lg text-xs font-semibold bg-cyan-600 text-white shadow transition';
            btnLine.className = 'px-3 py-1 rounded-lg text-xs font-semibold text-slate-400 hover:text-white transition';
        } else {
            btnLine.className = 'px-3 py-1 rounded-lg text-xs font-semibold bg-cyan-600 text-white shadow transition';
            btnBar.className = 'px-3 py-1 rounded-lg text-xs font-semibold text-slate-400 hover:text-white transition';
        }
    };

    // --- 2. DONUT DISTRIBUTION CHART ---
    const ctxDist = document.getElementById('tankDistributionChart').getContext('2d');
    new Chart(ctxDist, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: volumes,
                backgroundColor: barBgColors,
                borderColor: '#0f172a',
                borderWidth: 3,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#94a3b8',
                        font: { family: 'Plus Jakarta Sans', size: 10 },
                        boxWidth: 12,
                        padding: 12
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                    titleColor: '#ffffff',
                    bodyColor: '#38bdf8',
                    borderColor: '#334155',
                    borderWidth: 1,
                    padding: 10,
                    callbacks: {
                        label: function(context) {
                            const val = context.raw;
                            return ` ${context.label}: ${val.toLocaleString('id-ID')} Liter`;
                        }
                    }
                }
            },
            cutout: '65%'
        }
    });

    // --- 3. MONTHLY TREND CHART (Jan - Dec) ---
    const ctxMonthly = document.getElementById('monthlyTrendChart').getContext('2d');
    new Chart(ctxMonthly, {
        type: 'line',
        data: {
            labels: @json($monthlyLabels),
            datasets: [
                {
                    label: 'Pemasukan BBM',
                    data: @json($monthlyPemasukan),
                    borderColor: '#38bdf8',
                    backgroundColor: 'rgba(56, 189, 248, 0.15)',
                    tension: 0.35,
                    fill: true,
                    pointBackgroundColor: '#38bdf8',
                    pointRadius: 4,
                },
                {
                    label: 'Pemakaian BBM',
                    data: @json($monthlyPemakaian),
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249, 115, 22, 0.15)',
                    tension: 0.35,
                    fill: true,
                    pointBackgroundColor: '#f97316',
                    pointRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: '#94a3b8',
                        font: { family: 'Plus Jakarta Sans', size: 11 },
                        boxWidth: 12
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                    titleColor: '#ffffff',
                    bodyColor: '#cbd5e1',
                    borderColor: '#334155',
                    borderWidth: 1,
                    padding: 10
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(51, 65, 85, 0.4)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#94a3b8',
                        font: { family: 'JetBrains Mono', size: 10 },
                        stepSize: 1
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#94a3b8',
                        font: { family: 'Plus Jakarta Sans', size: 10 }
                    }
                }
            }
        }
    });
</script>
@endpush
