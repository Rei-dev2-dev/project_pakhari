@extends('layouts.app')

@section('title', auth()->user()->hasRole(['admin', 'superadmin']) ? 'Laporan & Ekspor' : 'Laporan Rekapitulasi')
@section('page-title', auth()->user()->hasRole(['admin', 'superadmin']) ? 'Laporan & Ekspor Telemetri' : 'Laporan Rekapitulasi Telemetri')

@push('styles')
<style>
    /* Transparent Glass Acrylic Calendar Design */
    .win-cal-card {
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 12px 32px -4px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.06);
        color: #f1f5f9;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
    .win-cal-btn {
        width: 36px;
        height: 36px;
        font-size: 13.5px;
        font-weight: 500;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: none;
        background: transparent;
        color: #e2e8f0;
        transition: all 0.15s ease;
        user-select: none;
        margin: 0 auto;
    }
    .win-cal-btn:hover {
        background-color: rgba(255, 255, 255, 0.12);
        color: #ffffff;
    }
    .win-cal-btn.is-dimmed {
        color: #475569;
    }
    .win-cal-btn.is-dimmed:hover {
        color: #94a3b8;
        background-color: rgba(255, 255, 255, 0.06);
    }
    /* Tanggal Sekarang (Hari Ini): Abu-Abu Transparan */
    .win-cal-btn.is-today:not(.is-selected) {
        background-color: rgba(148, 163, 184, 0.2) !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        border: 1px solid rgba(148, 163, 184, 0.4) !important;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2) !important;
    }
    .win-cal-btn.is-selected {
        background: #0284c7 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 0 14px rgba(2, 132, 199, 0.55) !important;
        z-index: 10;
    }
    .win-cal-btn.in-range {
        background-color: rgba(2, 132, 199, 0.25) !important;
        color: #38bdf8 !important;
        border-radius: 0 !important;
    }
    /* Month Picker Buttons */
    .win-month-btn {
        width: 46px;
        height: 46px;
        border-radius: 9999px;
        font-size: 13px;
        font-weight: 500;
        color: #cbd5e1;
        background: transparent;
        border: none;
        cursor: pointer;
        transition: all 0.15s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 1px auto;
        user-select: none;
    }
    .win-month-btn:hover {
        background-color: rgba(255, 255, 255, 0.12);
        color: #ffffff;
    }
    .win-month-btn.is-current-month:not(.is-selected-month) {
        background-color: rgba(148, 163, 184, 0.2) !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        border: 1px solid rgba(148, 163, 184, 0.35) !important;
    }
    .win-month-btn.is-selected-month {
        background: #0284c7 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 0 14px rgba(2, 132, 199, 0.55) !important;
    }
    /* Year Picker Buttons */
    .win-year-btn {
        width: 50px;
        height: 50px;
        border-radius: 9999px;
        font-size: 13px;
        font-weight: 500;
        color: #cbd5e1;
        background: transparent;
        border: none;
        cursor: pointer;
        transition: all 0.15s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 1px auto;
        user-select: none;
    }
    .win-year-btn:hover {
        background-color: rgba(255, 255, 255, 0.12);
        color: #ffffff;
    }
    .win-year-btn.is-dimmed {
        color: #64748b !important;
    }
    .win-year-btn.is-dimmed:hover {
        color: #94a3b8 !important;
    }
    .win-year-btn.is-current-year:not(.is-selected-year) {
        background-color: rgba(148, 163, 184, 0.2) !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        border: 1px solid rgba(148, 163, 184, 0.35) !important;
    }
    .win-year-btn.is-selected-year {
        background: #0284c7 !important;
        color: #ffffff !important;
        font-weight: 700 !important;
        box-shadow: 0 0 14px rgba(2, 132, 199, 0.55) !important;
    }
    /* Smooth AJAX table transitions */
    #reportTableContainer {
        transition: opacity 0.2s ease, filter 0.2s ease;
    }
    #reportTableContainer.is-loading {
        opacity: 0.45;
        pointer-events: none;
        filter: blur(0.5px);
    }
</style>
@endpush

@section('page-badge')
    <span id="pageBadgeCount" class="text-xs px-2.5 py-1 rounded-lg bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 font-mono font-semibold">
        {{ $logs->total() }} Data Ditemukan
    </span>
@endsection

@section('content')
<div class="space-y-6">
    
    <!-- Filter Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl">
        <form action="{{ route('laporan.index') }}" method="GET" id="filterForm" class="space-y-4" onsubmit="return false;">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <span class="text-xs font-bold text-white uppercase tracking-wider">Filter Data Telemetri</span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <!-- Kolom Kiri: Filter Dropdown & Ekspor -->
                <div class="lg:col-span-5 space-y-4">
                    <!-- Select Tank -->
                    <div>
                        <label for="tank_id" class="block text-xs font-semibold text-slate-300 mb-1.5">Pilih Tangki</label>
                        <select name="tank_id" id="tank_id"
                            class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-cyan-500 cursor-pointer">
                            <option value="">Semua Tangki</option>
                            @foreach($tanks as $tank)
                                <option value="{{ $tank->id }}" {{ $selectedTankId == $tank->id ? 'selected' : '' }}>
                                    {{ $tank->name }} ({{ $tank->code }}) - {{ number_format($tank->capacity_liters, 0) }}L
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Jenis Transaksi -->
                    <div>
                        <label for="jenis" class="block text-xs font-semibold text-slate-300 mb-1.5">Jenis Transaksi</label>
                        <select name="jenis" id="jenis"
                            class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-cyan-500 cursor-pointer">
                            <option value="">Semua Transaksi</option>
                            <option value="pemasukan" {{ ($selectedJenis ?? '') === 'pemasukan' ? 'selected' : '' }}>Pemasukan BBM</option>
                            <option value="pemakaian" {{ ($selectedJenis ?? '') === 'pemakaian' ? 'selected' : '' }}>Pemakaian BBM</option>
                        </select>
                    </div>

                    {{-- Ekspor ke Excel --}}
                    @if(auth()->user()->hasRole(['admin', 'superadmin']))
                        <div class="pt-2">
                            <a id="btnExportExcel" href="{{ route('laporan.export', request()->query()) }}" class="w-full px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-lg shadow-emerald-600/20 transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>Ekspor ke Excel (.xlsx)</span>
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Kolom Kanan: Kalender Transparan -->
                <div class="lg:col-span-7 flex flex-col items-center sm:items-start">
                    <!-- Hidden Inputs for Form State -->
                    <input type="hidden" name="start_date" id="start_date" value="{{ $startDate }}">
                    <input type="hidden" name="end_date" id="end_date" value="{{ $endDate }}">

                    <!-- Card Kalender Transparan -->
                    <div class="win-cal-card w-full max-w-[340px] rounded-2xl overflow-hidden p-4">
                        
                        <!-- Top Header: Day, Month Day -->
                        <div id="calTopDateHeaderBtn" class="flex items-center justify-between pb-3.5 pt-0.5 px-1 border-b border-slate-800/80 cursor-pointer group" title="Kembali ke tampilan bulan ini">
                            <span id="calTopDateHeader" class="text-sm font-semibold text-slate-200 group-hover:text-cyan-300 transition">
                                Today
                            </span>
                            <div class="w-7 h-7 bg-slate-800/80 group-hover:bg-slate-700 rounded-lg flex items-center justify-center text-slate-300 group-hover:text-cyan-300 shadow-sm border border-slate-700/80 transition">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        <!-- Month Title + Up/Down Arrows -->
                        <div class="flex items-center justify-between pt-3 pb-2 px-1">
                            <button type="button" id="calCurrentMonthYear" class="font-bold text-sm text-white tracking-tight hover:text-cyan-400 transition" title="Pilih Bulan & Tahun">
                                September 2026
                            </button>
                            <div id="calDayNavBtns" class="flex items-center space-x-3 text-slate-400">
                                <button type="button" id="calPrevMonth" class="hover:text-white text-xs font-bold transition p-0.5" title="Bulan Sebelumnya">▲</button>
                                <button type="button" id="calNextMonth" class="hover:text-white text-xs font-bold transition p-0.5" title="Bulan Berikutnya">▼</button>
                            </div>
                        </div>

                        <!-- Day View -->
                        <div id="calDayView">
                            <div class="grid grid-cols-7 gap-1 text-center mb-2 px-0.5">
                                <span class="text-xs font-semibold text-slate-400 py-1">Su</span>
                                <span class="text-xs font-semibold text-slate-400 py-1">Mo</span>
                                <span class="text-xs font-semibold text-slate-400 py-1">Tu</span>
                                <span class="text-xs font-semibold text-slate-400 py-1">We</span>
                                <span class="text-xs font-semibold text-slate-400 py-1">Th</span>
                                <span class="text-xs font-semibold text-slate-400 py-1">Fr</span>
                                <span class="text-xs font-semibold text-slate-400 py-1">Sa</span>
                            </div>
                            <div id="calDaysGrid" class="grid grid-cols-7 gap-y-1 text-center">
                                <!-- Injected via JS -->
                            </div>
                        </div>

                        <!-- Month Picker View -->
                        <div id="calMonthView" class="hidden">
                            <div id="calMonthGrid" class="grid grid-cols-4 gap-y-2 gap-x-1 overflow-y-auto py-1" style="max-height: 224px; scrollbar-width: thin; scrollbar-color: rgba(100,116,139,0.4) transparent;">
                                <!-- Injected directly via JS as seamless 4-column month stream -->
                            </div>
                        </div>

                        <!-- Year / Decade Picker View -->
                        <div id="calYearView" class="hidden">
                            <div id="calYearGrid" class="grid grid-cols-4 gap-y-2 gap-x-1 overflow-y-auto py-1" style="max-height: 224px; scrollbar-width: thin; scrollbar-color: rgba(100,116,139,0.4) transparent;">
                                <!-- Injected directly via JS as seamless 4-column year stream -->
                            </div>
                        </div>
                    </div>

                    <!-- Presets Bawah -->
                    <div class="flex items-center gap-1.5 pt-3 max-w-[340px] w-full">
                        <button type="button" data-preset="today" class="cal-preset flex-1 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-[11px] font-medium text-slate-300 transition text-center">Hari Ini</button>
                        <button type="button" data-preset="last7" class="cal-preset flex-1 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-[11px] font-medium text-slate-300 transition text-center">7 Hari</button>
                        <button type="button" data-preset="thisMonth" class="cal-preset flex-1 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-[11px] font-medium text-slate-300 transition text-center">Bulan Ini</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Data Table Card Container (Smooth AJAX Target) -->
    <div id="reportTableContainer" class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
            <h3 class="text-xs font-bold text-white uppercase tracking-wider">Daftar Rekapitulasi Sensor</h3>
            <span class="text-xs text-slate-400 font-mono">Halaman {{ $logs->currentPage() }} dari {{ $logs->lastPage() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-950/60 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider text-[10px]">
                        <th class="py-3 px-4">No</th>
                        <th class="py-3 px-4">Tangki</th>
                        <th class="py-3 px-4">Volume</th>
                        <th class="py-3 px-4">Persentase</th>
                        <th class="py-3 px-4">Ketinggian</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Jenis</th>
                        <th class="py-3 px-4">Petugas</th>
                        <th class="py-3 px-4">Catatan</th>
                        <th class="py-3 px-4">Foto Bukti</th>
                        <th class="py-3 px-4">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-mono text-[11px]">
                    @forelse($logs as $index => $log)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3 px-4 text-slate-500">{{ $logs->firstItem() + $index }}</td>
                            <td class="py-3 px-4 font-sans font-bold text-slate-200">
                                {{ $log->tank ? $log->tank->name : 'Tangki Utama' }}
                                <span class="block text-[10px] text-slate-400 font-mono font-normal">{{ $log->tank ? $log->tank->code : 'TNK-01' }}</span>
                            </td>
                            <td class="py-3 px-4 font-bold text-white">{{ number_format($log->volume_liters, 2) }} L</td>
                            <td class="py-3 px-4 text-cyan-400 font-bold">{{ number_format($log->percentage, 2) }}%</td>
                            <td class="py-3 px-4 text-slate-300">{{ number_format($log->height_cm, 2) }} cm</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold font-mono
                                    @if($log->status === 'warning_full') bg-rose-500/10 text-rose-400 border border-rose-500/30
                                    @elseif($log->status === 'low') bg-amber-500/10 text-amber-400 border border-amber-500/30
                                    @else bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 @endif">
                                    {{ strtoupper(str_replace('_', ' ', $log->status)) }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if($log->source === 'pemasukan_bbm')
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-blue-500/10 text-blue-400 border border-blue-500/30">PEMASUKAN</span>
                                @elseif($log->source === 'pemakaian_bbm')
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-orange-500/10 text-orange-400 border border-orange-500/30">PEMAKAIAN</span>
                                @else
                                    <span class="text-slate-500">{{ $log->source }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($log->user)
                                    <span class="font-sans font-semibold text-slate-200 block">{{ $log->user->name }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono font-normal uppercase">{{ $log->user->role }}</span>
                                @elseif($log->device_id)
                                    <span class="font-sans text-slate-300 block">{{ str_replace(['OPERATOR-', 'MANUAL-'], '', $log->device_id) }}</span>
                                    <span class="text-[10px] text-slate-500 font-mono">OPERATOR</span>
                                @else
                                    <span class="text-slate-500 font-sans">Sistem</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-400 font-sans max-w-[120px] truncate" title="{{ $log->notes ?? '-' }}">
                                {{ $log->notes ? Str::limit($log->notes, 30) : '-' }}
                            </td>
                            <td class="py-3 px-4">
                            @if($log->photo_path)
                                <button type="button"
                                    onclick="openLightbox('{{ asset('storage/' . $log->photo_path) }}')"
                                    class="group relative block">
                                    <img src="{{ asset('storage/' . $log->photo_path) }}"
                                        alt="Foto Bukti"
                                        class="w-10 h-10 rounded-lg object-cover border border-slate-700 group-hover:border-cyan-500 transition cursor-pointer">
                                    <span class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 rounded-lg transition">
                                        <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </span>
                                </button>
                            @else
                                <span class="text-slate-600">—</span>
                            @endif
                            </td>
                            <td class="py-3 px-4 text-slate-400">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="py-8 text-center text-slate-500 font-sans">
                                Tidak ada data telemetri yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($logs->hasPages())
            <div id="tablePagination" class="px-6 py-4 border-t border-slate-800 bg-slate-950/40">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Lightbox Modal -->
<div id="photo-lightbox" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/85 backdrop-blur-md p-4">
    <div class="relative max-w-3xl w-full bg-slate-900 border border-slate-700/80 rounded-2xl overflow-hidden shadow-2xl p-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-3">
            <span class="text-xs font-bold text-white flex items-center gap-2">
                <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Foto Bukti Transaksi BBM
            </span>
            <button onclick="closeLightbox()" class="text-slate-400 hover:text-white transition text-xs font-bold flex items-center gap-1.5 bg-slate-800 px-3 py-1.5 rounded-lg hover:bg-slate-700">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Tutup
            </button>
        </div>

        <div class="flex items-center justify-center min-h-[250px] max-h-[75vh] bg-slate-950 rounded-xl overflow-hidden relative">
            <img id="lightbox-img" src="" alt="Foto Bukti" 
                class="w-full max-h-[75vh] object-contain rounded-xl"
                onerror="this.classList.add('hidden'); document.getElementById('lightbox-error').classList.remove('hidden');"
                onload="this.classList.remove('hidden'); document.getElementById('lightbox-error').classList.add('hidden');">
            
            <div id="lightbox-error" class="hidden flex flex-col items-center justify-center p-8 text-center">
                <svg class="w-10 h-10 text-rose-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p class="text-xs font-semibold text-rose-300">File foto tidak dapat dimuat atau symlink storage belum terhubung.</p>
                <p class="text-[11px] text-slate-400 mt-1">Pastikan perintah <code class="text-cyan-400 font-mono">rm -rf public/storage && php artisan storage:link</code> sudah dijalankan di server.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.openLightbox = function(src) {
        const lb = document.getElementById('photo-lightbox');
        const img = document.getElementById('lightbox-img');
        const err = document.getElementById('lightbox-error');
        if (err) { err.classList.add('hidden'); }
        if (img) { 
            img.classList.remove('hidden');
            img.src = src; 
        }
        if (lb) {
            lb.classList.remove('hidden');
            lb.classList.add('flex');
        }
    };

    window.closeLightbox = function() {
        const lb = document.getElementById('photo-lightbox');
        if (lb) {
            lb.classList.add('hidden');
            lb.classList.remove('flex');
        }
    };

    // Close on backdrop click
    document.getElementById('photo-lightbox')?.addEventListener('click', function(e) {
        if (e.target === this) { window.closeLightbox(); }
    });

    // ==========================================
    // Exact Windows Fluent Calendar & Smooth AJAX Logic
    // ==========================================
    (function() {
        const monthNames = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        const startInput   = document.getElementById('start_date');
        const endInput     = document.getElementById('end_date');
        const tankSelect   = document.getElementById('tank_id');
        const jenisSelect  = document.getElementById('jenis');
        const topHeaderBtn = document.getElementById('calTopDateHeaderBtn');
        const topHeaderEl  = document.getElementById('calTopDateHeader');
        const monthYearEl  = document.getElementById('calCurrentMonthYear');
        const daysGrid     = document.getElementById('calDaysGrid');
        const prevBtn      = document.getElementById('calPrevMonth');
        const nextBtn      = document.getElementById('calNextMonth');
        const tableContainer = document.getElementById('reportTableContainer');
        const pageBadgeEl  = document.getElementById('pageBadgeCount');
        const exportBtn    = document.getElementById('btnExportExcel');
        const dayView      = document.getElementById('calDayView');
        const monthView    = document.getElementById('calMonthView');
        const monthGrid    = document.getElementById('calMonthGrid');
        const yearView     = document.getElementById('calYearView');
        const yearGrid     = document.getElementById('calYearGrid');
        const dayNavBtns   = document.getElementById('calDayNavBtns');

        let selectedStart = startInput && startInput.value ? startInput.value : null;
        let selectedEnd   = endInput && endInput.value ? endInput.value : null;
        let viewMode      = 'days'; // 'days' | 'months' | 'years'

        const todayObj = new Date();
        const todayStr = toIso(todayObj.getFullYear(), todayObj.getMonth() + 1, todayObj.getDate());

        let curDate = selectedStart ? parseIso(selectedStart) : new Date();
        let viewYear = curDate.getFullYear();
        let viewMonth = curDate.getMonth(); // 0 - 11

        function toIso(y, m, d) {
            const mm = String(m).padStart(2, '0');
            const dd = String(d).padStart(2, '0');
            return `${y}-${mm}-${dd}`;
        }

        function parseIso(isoStr) {
            const parts = isoStr.split('-');
            return new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
        }

        function updateTopHeader() {
            if (!topHeaderEl) return;
            const dayName = dayNames[todayObj.getDay()];
            const mName = monthNames[todayObj.getMonth()];
            topHeaderEl.textContent = `${dayName}, ${mName} ${todayObj.getDate()}`;
        }

        function updateDayClasses(hoveredIso) {
            const cells = daysGrid.querySelectorAll('.win-cal-btn');
            cells.forEach(btn => {
                const iso = btn.dataset.date;
                const isDimmed = btn.dataset.dimmed === '1';
                const isToday = (iso === todayStr);
                const isStart = selectedStart && iso === selectedStart;
                const isEnd   = selectedEnd && iso === selectedEnd;

                let inRange = false;
                if (selectedStart && selectedEnd) {
                    inRange = iso > selectedStart && iso < selectedEnd;
                } else if (selectedStart && !selectedEnd && hoveredIso) {
                    inRange = (hoveredIso >= selectedStart) 
                        ? (iso > selectedStart && iso <= hoveredIso)
                        : (iso >= hoveredIso && iso < selectedStart);
                }

                btn.className = 'win-cal-btn';
                if (isDimmed) btn.classList.add('is-dimmed');
                if (isToday) btn.classList.add('is-today');
                if (isStart || isEnd) btn.classList.add('is-selected');
                if (inRange) btn.classList.add('in-range');
            });

            updateTopHeader();
            updatePresetButtons();
        }

        function updatePresetButtons() {
            const now = new Date();
            const tStart = todayStr;
            const tEnd = todayStr;

            const past = new Date();
            past.setDate(now.getDate() - 6);
            const l7Start = toIso(past.getFullYear(), past.getMonth() + 1, past.getDate());
            const l7End = todayStr;

            const tmStart = toIso(now.getFullYear(), now.getMonth() + 1, 1);
            const lastD = new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate();
            const tmEnd = toIso(now.getFullYear(), now.getMonth() + 1, lastD);

            document.querySelectorAll('.cal-preset').forEach(btn => {
                const preset = btn.dataset.preset;
                let isActive = false;
                if (preset === 'today' && selectedStart === tStart && selectedEnd === tEnd) isActive = true;
                if (preset === 'last7' && selectedStart === l7Start && selectedEnd === l7End) isActive = true;
                if (preset === 'thisMonth' && selectedStart === tmStart && selectedEnd === tmEnd) isActive = true;

                if (isActive) {
                    btn.className = 'cal-preset flex-1 py-1.5 rounded-lg bg-cyan-600/30 text-cyan-300 border border-cyan-500/40 text-[11px] font-bold transition text-center shadow-sm';
                } else {
                    btn.className = 'cal-preset flex-1 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 border border-transparent text-[11px] font-medium transition text-center';
                }
            });
        }

        function renderCalendar() {
            if (!monthYearEl || !daysGrid) return;
            monthYearEl.textContent = `${monthNames[viewMonth]} ${viewYear}`;

            const firstDayIndex = new Date(viewYear, viewMonth, 1).getDay(); // 0=Sun..6=Sat
            const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
            const daysInPrevMonth = new Date(viewYear, viewMonth, 0).getDate();

            daysGrid.innerHTML = '';

            // Leading padding days from previous month
            for (let i = firstDayIndex - 1; i >= 0; i--) {
                const dayNum = daysInPrevMonth - i;
                const prevM = viewMonth === 0 ? 12 : viewMonth;
                const prevY = viewMonth === 0 ? viewYear - 1 : viewYear;
                const iso = toIso(prevY, prevM, dayNum);

                const cell = createDayCell(dayNum, iso, true);
                daysGrid.appendChild(cell);
            }

            // Current month days
            for (let day = 1; day <= daysInMonth; day++) {
                const iso = toIso(viewYear, viewMonth + 1, day);
                const cell = createDayCell(day, iso, false);
                daysGrid.appendChild(cell);
            }

            // Trailing padding days to always have 42 total cells (6 rows x 7 cols)
            const totalCells = firstDayIndex + daysInMonth;
            const remaining = 42 - totalCells;
            for (let j = 1; j <= remaining; j++) {
                const nextM = viewMonth === 11 ? 1 : viewMonth + 2;
                const nextY = viewMonth === 11 ? viewYear + 1 : viewYear;
                const iso = toIso(nextY, nextM, j);

                const cell = createDayCell(j, iso, true);
                daysGrid.appendChild(cell);
            }

            updateDayClasses(null);
        }

        function createDayCell(dayNum, iso, isDimmed) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.dataset.date = iso;
            btn.dataset.dimmed = isDimmed ? '1' : '0';
            btn.className = 'win-cal-btn' + (isDimmed ? ' is-dimmed' : '');
            if (iso === todayStr) {
                btn.classList.add('is-today');
            }
            btn.textContent = dayNum;

            btn.addEventListener('mouseenter', () => {
                if (selectedStart && !selectedEnd) {
                    updateDayClasses(iso);
                }
            });

            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                onDateSelect(iso);
            });

            return btn;
        }

        daysGrid.addEventListener('mouseleave', () => {
            if (selectedStart && !selectedEnd) {
                updateDayClasses(null);
            }
        });

        // ==========================================
        // View Mode Navigation (Days -> Months -> Years / Decades)
        // ==========================================
        const monthShort = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        let pickerMonthStartYear = 2020;
        let pickerMonthEndYear   = 2035;
        let pickerYearStart      = 2000;
        let pickerYearEnd        = 2050;

        function setViewMode(mode) {
            viewMode = mode;
            dayView.classList.toggle('hidden', viewMode !== 'days');
            monthView.classList.toggle('hidden', viewMode !== 'months');
            yearView.classList.toggle('hidden', viewMode !== 'years');

            if (viewMode === 'days') {
                monthYearEl.className = 'font-bold text-sm text-white tracking-tight hover:text-cyan-400 transition cursor-pointer';
                monthYearEl.style.pointerEvents = 'auto';
                monthYearEl.textContent = `${monthNames[viewMonth]} ${viewYear}`;
                renderCalendar();
            } else if (viewMode === 'months') {
                monthYearEl.className = 'font-bold text-sm text-white tracking-tight hover:text-cyan-400 transition cursor-pointer';
                monthYearEl.style.pointerEvents = 'auto';
                renderMonthPicker();
            } else if (viewMode === 'years') {
                monthYearEl.className = 'font-bold text-sm text-slate-400 tracking-tight cursor-default select-none pointer-events-none';
                monthYearEl.style.pointerEvents = 'none';
                renderYearPicker();
            }
        }

        // ── 1. Month Picker Logic (Continuous 4-column Stream) ──
        function createMonthBtn(y, m, nowY, nowM) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = monthShort[m];
            btn.dataset.year = y;
            btn.dataset.month = m;
            btn.className = 'win-month-btn';
            
            if (y === nowY && m === nowM) {
                btn.classList.add('is-current-month');
            }
            if (y === viewYear && m === viewMonth) {
                btn.classList.add('is-selected-month');
            }

            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                viewYear  = parseInt(btn.dataset.year, 10);
                viewMonth = parseInt(btn.dataset.month, 10);
                setViewMode('days');
            });

            return btn;
        }

        function renderMonthPicker() {
            if (!monthGrid) return;
            monthGrid.innerHTML = '';
            
            pickerMonthStartYear = viewYear - 5;
            pickerMonthEndYear   = viewYear + 5;
            monthYearEl.textContent = `${viewYear}`;

            const nowM = todayObj.getMonth();
            const nowY = todayObj.getFullYear();
            const frag = document.createDocumentFragment();

            for (let y = pickerMonthStartYear; y <= pickerMonthEndYear; y++) {
                for (let m = 0; m < 12; m++) {
                    frag.appendChild(createMonthBtn(y, m, nowY, nowM));
                }
            }
            monthGrid.appendChild(frag);

            // Scroll immediately so viewYear starts in view
            const targetRow = (viewYear - pickerMonthStartYear) * 3;
            monthGrid.scrollTop = targetRow * 48;
        }

        if (monthGrid) {
            monthGrid.addEventListener('scroll', () => {
                if (viewMode !== 'months') return;
                const st = monthGrid.scrollTop;
                const rowHeight = 48;
                const visibleRow = Math.floor((st + 24) / rowHeight);
                const activeYear = pickerMonthStartYear + Math.floor(visibleRow / 3);
                
                if (activeYear) {
                    monthYearEl.textContent = `${activeYear}`;
                }

                if (st < 80) {
                    const fromY = pickerMonthStartYear - 4;
                    const toY   = pickerMonthStartYear - 1;
                    const numYears = toY - fromY + 1;
                    const nowM = todayObj.getMonth();
                    const nowY = todayObj.getFullYear();
                    const frag = document.createDocumentFragment();

                    for (let y = fromY; y <= toY; y++) {
                        for (let m = 0; m < 12; m++) {
                            frag.appendChild(createMonthBtn(y, m, nowY, nowM));
                        }
                    }
                    monthGrid.insertBefore(frag, monthGrid.firstChild);
                    monthGrid.scrollTop += (numYears * 3 * rowHeight);
                    pickerMonthStartYear = fromY;
                } else if (st + monthGrid.clientHeight > monthGrid.scrollHeight - 80) {
                    const fromY = pickerMonthEndYear + 1;
                    const toY   = pickerMonthEndYear + 4;
                    const nowM = todayObj.getMonth();
                    const nowY = todayObj.getFullYear();
                    const frag = document.createDocumentFragment();

                    for (let y = fromY; y <= toY; y++) {
                        for (let m = 0; m < 12; m++) {
                            frag.appendChild(createMonthBtn(y, m, nowY, nowM));
                        }
                    }
                    monthGrid.appendChild(frag);
                    pickerMonthEndYear = toY;
                }
            });
        }

        // ── 2. Year / Decade Picker Logic (Windows 11 Fluent 4-col Stream) ──
        function createYearBtn(y, nowY, curDecadeStart, curDecadeEnd) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = y;
            btn.dataset.year = y;
            btn.className = 'win-year-btn';
            
            const isDimmed = (y < curDecadeStart || y > curDecadeEnd);
            if (isDimmed) btn.classList.add('is-dimmed');
            if (y === nowY) btn.classList.add('is-current-year');
            if (y === viewYear) btn.classList.add('is-selected-year');

            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                viewYear = parseInt(btn.dataset.year, 10);
                setViewMode('months');
            });

            return btn;
        }

        function updateYearDimmedStates(curDecadeStart, curDecadeEnd) {
            if (!yearGrid) return;
            const buttons = yearGrid.querySelectorAll('.win-year-btn');
            buttons.forEach(btn => {
                const y = parseInt(btn.dataset.year, 10);
                if (y < curDecadeStart || y > curDecadeEnd) {
                    btn.classList.add('is-dimmed');
                } else {
                    btn.classList.remove('is-dimmed');
                }
            });
        }

        function renderYearPicker() {
            if (!yearGrid) return;
            yearGrid.innerHTML = '';

            const currentDecadeStart = Math.floor(viewYear / 10) * 10;
            const currentDecadeEnd   = currentDecadeStart + 9;
            monthYearEl.textContent  = `${currentDecadeStart} - ${currentDecadeEnd}`;

            pickerYearStart = currentDecadeStart - 40;
            pickerYearEnd   = currentDecadeStart + 40;

            const nowY = todayObj.getFullYear();
            const frag = document.createDocumentFragment();

            for (let y = pickerYearStart; y <= pickerYearEnd; y++) {
                frag.appendChild(createYearBtn(y, nowY, currentDecadeStart, currentDecadeEnd));
            }
            yearGrid.appendChild(frag);

            // Scroll to center on viewYear decade:
            // 4 columns = 4 years per row (row height ~52px).
            // Position of currentDecadeStart - 2 padding years:
            const targetRow = Math.floor((currentDecadeStart - pickerYearStart) / 4);
            yearGrid.scrollTop = targetRow * 52;
        }

        if (yearGrid) {
            yearGrid.addEventListener('scroll', () => {
                if (viewMode !== 'years') return;
                const st = yearGrid.scrollTop;
                const rowHeight = 52;
                // Year visible in upper viewport:
                const visibleRow = Math.floor((st + 26) / rowHeight);
                const activeYear = pickerYearStart + (visibleRow * 4) + 2;
                const activeDecadeStart = Math.floor(activeYear / 10) * 10;
                const activeDecadeEnd   = activeDecadeStart + 9;
                
                monthYearEl.textContent = `${activeDecadeStart} - ${activeDecadeEnd}`;
                updateYearDimmedStates(activeDecadeStart, activeDecadeEnd);

                // Infinite scroll check for years
                if (st < 80) {
                    const fromY = pickerYearStart - 20;
                    const toY   = pickerYearStart - 1;
                    const numYears = toY - fromY + 1;
                    const nowY = todayObj.getFullYear();
                    const frag = document.createDocumentFragment();

                    for (let y = fromY; y <= toY; y++) {
                        frag.appendChild(createYearBtn(y, nowY, activeDecadeStart, activeDecadeEnd));
                    }
                    yearGrid.insertBefore(frag, yearGrid.firstChild);
                    yearGrid.scrollTop += (Math.ceil(numYears / 4) * rowHeight);
                    pickerYearStart = fromY;
                } else if (st + yearGrid.clientHeight > yearGrid.scrollHeight - 80) {
                    const fromY = pickerYearEnd + 1;
                    const toY   = pickerYearEnd + 20;
                    const nowY = todayObj.getFullYear();
                    const frag = document.createDocumentFragment();

                    for (let y = fromY; y <= toY; y++) {
                        frag.appendChild(createYearBtn(y, nowY, activeDecadeStart, activeDecadeEnd));
                    }
                    yearGrid.appendChild(frag);
                    pickerYearEnd = toY;
                }
            });
        }

        // ── 3. Header Title Click: Days -> Months -> Years ──
        if (monthYearEl) {
            monthYearEl.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (viewMode === 'days') {
                    setViewMode('months');
                } else if (viewMode === 'months') {
                    setViewMode('years');
                }
            });
        }

        // ── 4. Top Header Click (Wednesday, September 23): Reset Calendar View to Today (Days View) ──
        if (topHeaderBtn) {
            topHeaderBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                viewYear  = todayObj.getFullYear();
                viewMonth = todayObj.getMonth();
                setViewMode('days');
            });
        }

        // ==========================================
        // Smooth AJAX Filter Function (No Page Blink)
        // ==========================================
        let abortController = null;

        function applyFilterAjax(customUrl = null) {
            if (tableContainer) {
                tableContainer.classList.add('is-loading');
            }

            if (abortController) {
                abortController.abort();
            }
            abortController = new AbortController();

            let targetUrl = customUrl;
            if (!targetUrl) {
                const url = new URL("{{ route('laporan.index') }}", window.location.origin);
                if (selectedStart) url.searchParams.set('start_date', selectedStart);
                if (selectedEnd) url.searchParams.set('end_date', selectedEnd);
                if (tankSelect && tankSelect.value) url.searchParams.set('tank_id', tankSelect.value);
                if (jenisSelect && jenisSelect.value) url.searchParams.set('jenis', jenisSelect.value);
                targetUrl = url.toString();
            }

            fetch(targetUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: abortController.signal
            })
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                // 1. Update Table Container
                const newTable = doc.getElementById('reportTableContainer');
                if (newTable && tableContainer) {
                    tableContainer.innerHTML = newTable.innerHTML;
                }

                // 2. Update Header Badge Count
                const newBadge = doc.getElementById('pageBadgeCount');
                if (newBadge && pageBadgeEl) {
                    pageBadgeEl.innerHTML = newBadge.innerHTML;
                }

                // 3. Update Export Excel URL
                const newExport = doc.getElementById('btnExportExcel');
                if (newExport && exportBtn) {
                    exportBtn.href = newExport.href;
                }

                // Update Browser URL history seamlessly
                window.history.pushState({ path: targetUrl }, '', targetUrl);
            })
            .catch(err => {
                if (err.name !== 'AbortError') {
                    console.error('Filter AJAX error:', err);
                }
            })
            .finally(() => {
                if (tableContainer) {
                    tableContainer.classList.remove('is-loading');
                }
            });
        }

        // ==========================================
        // Deselection & Date Click Logic
        // ==========================================
        function onDateSelect(iso) {
            // Case 1: Clicking End Date again -> Deselect End Date, keep Start Date
            if (selectedEnd && iso === selectedEnd) {
                selectedEnd = null;
                if (endInput) endInput.value = '';
                updateDayClasses(null);
                applyFilterAjax();
                return;
            }

            // Case 2: Clicking Start Date again
            if (selectedStart && iso === selectedStart) {
                if (selectedEnd) {
                    // Start date deselected, end date becomes the active start date
                    selectedStart = selectedEnd;
                    selectedEnd = null;
                    if (startInput) startInput.value = selectedStart;
                    if (endInput) endInput.value = '';
                    updateDayClasses(null);
                    applyFilterAjax();
                } else {
                    // Only start date was selected -> Deselect all, return to default (show all)
                    selectedStart = null;
                    selectedEnd = null;
                    if (startInput) startInput.value = '';
                    if (endInput) endInput.value = '';
                    updateDayClasses(null);
                    applyFilterAjax();
                }
                return;
            }

            // Case 3: First click (or starting fresh click) -> Select Start Date
            if (!selectedStart || (selectedStart && selectedEnd)) {
                selectedStart = iso;
                selectedEnd = null;
                if (startInput) startInput.value = selectedStart;
                if (endInput) endInput.value = '';
                updateDayClasses(null);
                // Highlight start date; wait for second click or stay selected
            } 
            // Case 4: Second click -> Select End Date & Automatically apply filter smoothly
            else if (selectedStart && !selectedEnd) {
                if (iso < selectedStart) {
                    selectedEnd = selectedStart;
                    selectedStart = iso;
                } else {
                    selectedEnd = iso;
                }
                if (startInput) startInput.value = selectedStart;
                if (endInput) endInput.value = selectedEnd;
                updateDayClasses(null);

                // Smooth AJAX filter
                applyFilterAjax();
            }
        }

        // ==========================================
        // Dropdowns, Presets, & Pagination Handlers
        // ==========================================
        if (tankSelect) {
            tankSelect.addEventListener('change', () => applyFilterAjax());
        }

        if (jenisSelect) {
            jenisSelect.addEventListener('change', () => applyFilterAjax());
        }

        // Navigation (Up / Down buttons for Day, Month, and Decade/Year views)
        if (prevBtn) {
            prevBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (viewMode === 'years' && yearGrid) {
                    yearGrid.scrollBy({ top: -130, behavior: 'smooth' });
                } else if (viewMode === 'months' && monthGrid) {
                    monthGrid.scrollBy({ top: -144, behavior: 'smooth' });
                } else {
                    viewMonth--;
                    if (viewMonth < 0) {
                        viewMonth = 11;
                        viewYear--;
                    }
                    renderCalendar();
                }
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (viewMode === 'years' && yearGrid) {
                    yearGrid.scrollBy({ top: 130, behavior: 'smooth' });
                } else if (viewMode === 'months' && monthGrid) {
                    monthGrid.scrollBy({ top: 144, behavior: 'smooth' });
                } else {
                    viewMonth++;
                    if (viewMonth > 11) {
                        viewMonth = 0;
                        viewYear++;
                    }
                    renderCalendar();
                }
            });
        }

        // Presets Click Handlers with Toggle Behavior (Click twice -> reset to default)
        document.querySelectorAll('.cal-preset').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const preset = btn.dataset.preset;
                const now = new Date();
                let targetStart = null;
                let targetEnd = null;

                if (preset === 'today') {
                    targetStart = todayStr;
                    targetEnd   = todayStr;
                } else if (preset === 'last7') {
                    const past = new Date();
                    past.setDate(now.getDate() - 6);
                    targetStart = toIso(past.getFullYear(), past.getMonth() + 1, past.getDate());
                    targetEnd   = todayStr;
                } else if (preset === 'thisMonth') {
                    targetStart = toIso(now.getFullYear(), now.getMonth() + 1, 1);
                    const lastD = new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate();
                    targetEnd = toIso(now.getFullYear(), now.getMonth() + 1, lastD);
                }

                // If already active, toggle off to default (no date filter)
                if (selectedStart === targetStart && selectedEnd === targetEnd) {
                    selectedStart = null;
                    selectedEnd   = null;
                } else {
                    selectedStart = targetStart;
                    selectedEnd   = targetEnd;
                }

                if (startInput) startInput.value = selectedStart || '';
                if (endInput) endInput.value = selectedEnd || '';

                if (selectedStart) {
                    const parsed = parseIso(selectedStart);
                    viewYear = parsed.getFullYear();
                    viewMonth = parsed.getMonth();
                }
                renderCalendar();

                applyFilterAjax();
            });
        });

        // AJAX Pagination delegation inside table container
        if (tableContainer) {
            tableContainer.addEventListener('click', (e) => {
                const paginationLink = e.target.closest('nav a');
                if (paginationLink && paginationLink.href) {
                    e.preventDefault();
                    applyFilterAjax(paginationLink.href);
                }
            });
        }

        // Browser Back/Forward navigation support
        window.addEventListener('popstate', () => {
            window.location.reload();
        });

        // Init on page load
        renderCalendar();
    })();
</script>
@endpush



