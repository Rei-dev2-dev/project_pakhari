@extends('layouts.app')

@section('title', 'Laporan & Ekspor')
@section('page-title', 'Laporan & Ekspor Telemetri')

@section('page-badge')
    <span class="text-xs px-2.5 py-1 rounded-lg bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 font-mono font-semibold">
        {{ $logs->total() }} Data Ditemukan
    </span>
@endsection

@section('content')
<div class="space-y-6">
    
    <!-- Filter Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl">
        <form action="{{ route('laporan.index') }}" method="GET" class="space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <span class="text-xs font-bold text-white uppercase tracking-wider">Filter Data Telemetri</span>
                </div>

                @if(request()->hasAny(['tank_id', 'start_date', 'end_date', 'jenis']))
                    <a href="{{ route('laporan.index') }}" class="text-[11px] text-slate-400 hover:text-rose-400 transition">
                        Reset Filter
                    </a>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Select Tank -->
                <div>
                    <label for="tank_id" class="block text-xs font-semibold text-slate-300 mb-1.5">Pilih Tangki</label>
                    <select name="tank_id" id="tank_id" class="w-full px-3 py-2 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-cyan-500">
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
                    <select name="jenis" id="jenis" class="w-full px-3 py-2 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-cyan-500">
                        <option value="">Semua Transaksi</option>
                        <option value="pemasukan" {{ ($selectedJenis ?? '') === 'pemasukan' ? 'selected' : '' }}>Pemasukan BBM</option>
                        <option value="pemakaian" {{ ($selectedJenis ?? '') === 'pemakaian' ? 'selected' : '' }}>Pemakaian BBM</option>
                    </select>
                </div>

                <!-- Start Date -->
                <div>
                    <label for="start_date" class="block text-xs font-semibold text-slate-300 mb-1.5">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="start_date" value="{{ $startDate }}"
                        class="w-full px-3 py-2 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-cyan-500">
                </div>

                <!-- End Date -->
                <div>
                    <label for="end_date" class="block text-xs font-semibold text-slate-300 mb-1.5">Tanggal Selesai</label>
                    <input type="date" name="end_date" id="end_date" value="{{ $endDate }}"
                        class="w-full px-3 py-2 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:border-cyan-500">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition flex items-center gap-2">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>Terapkan Filter</span>
                </button>

                @if(auth()->user()->hasRole(['admin', 'superadmin']))
                    <!-- Export Excel Button (Khusus Admin & SuperAdmin) -->
                    <a href="{{ route('laporan.export', request()->query()) }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-lg shadow-emerald-600/20 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Ekspor ke Excel (.xlsx)</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
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
            <div class="px-6 py-4 border-t border-slate-800 bg-slate-950/40">
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
</script>
@endpush

