@extends('layouts.app')

@section('title', 'Monitoring Tangki')
@section('page-title', 'Monitoring Tangki BBM Genset')

@section('page-badge')
    <span class="text-xs px-2.5 py-1 rounded-lg bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 font-mono font-semibold">
        {{ $tanks->count() }} Tangki Aktif
    </span>
@endsection

@push('styles')
    <!-- Three.js Import Map (Local Assets) -->
    <script type="importmap">
    {
      "imports": {
        "three": "{{ asset('vendor/three/three.module.js') }}",
        "three/addons/controls/OrbitControls.js": "{{ asset('vendor/three/controls/OrbitControls.js') }}",
        "three/addons/loaders/GLTFLoader.js": "{{ asset('vendor/three/loaders/GLTFLoader.js') }}",
        "three/addons/": "{{ asset('vendor/three') }}/"
      }
    }
    </script>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Success Alert -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center justify-between shadow-lg">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white text-xs font-bold px-2 py-1 rounded-lg hover:bg-emerald-500/20 transition">
                &times;
            </button>
        </div>
    @endif

    <!-- Validation Errors Alert -->
    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs space-y-1 shadow-lg">
            @foreach($errors->all() as $err)
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ $err }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Header Banner -->
   

    <!-- 2x2 Grid Tank Cards (3D on Left, Specs on Right, Volume on Bottom) -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        @forelse($tanks as $tank)
            @php
                $latest = $tank->telemetries->first();
                $vol = $latest ? (float) $latest->volume_liters : 0.0;
                $cap = (float) $tank->capacity_liters > 0 ? (float) $tank->capacity_liters : 100.0;
                $pct = $latest ? (float) $latest->percentage : round(($vol / $cap) * 100, 1);
                $h = $latest ? (float) $latest->height_cm : round(($vol / $cap) * (float) $tank->height_cm, 1);
                $status = $latest ? $latest->status : 'normal';
            @endphp
            <div class="bg-slate-900 border border-slate-800 hover:border-cyan-500/40 rounded-2xl p-5 sm:p-6 shadow-xl transition-all duration-200 flex flex-col justify-between group">
                <div>
                    <!-- 1. Card Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-xl bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center text-cyan-400 font-bold group-hover:scale-105 transition">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-white group-hover:text-cyan-400 transition">{{ $tank->name }}</h4>
                                <span class="text-[11px] text-slate-400 font-mono">{{ $tank->code }} &bull; Kapasitas {{ number_format($cap, 0) }} Liter</span>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <span class="text-[10px] font-mono font-bold px-2.5 py-1 rounded-full
                            @if($status === 'warning_full') bg-rose-500/10 text-rose-400 border border-rose-500/30
                            @elseif($status === 'low') bg-amber-500/10 text-amber-400 border border-amber-500/30
                            @elseif($status === 'empty') bg-slate-500/10 text-slate-400 border border-slate-500/30
                            @else bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 @endif">
                            {{ strtoupper(str_replace('_', ' ', $status)) }}
                        </span>
                    </div>

                    <!-- 2. Middle Row: 3D Tank on Left, Specs (Ketinggian, Diameter, Panjang, Lebar) on Right -->
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 mb-5">
                        
                        <!-- Left: 3D Fixed Viewport (Static Angle) -->
                        <div class="sm:col-span-6 bg-slate-950/80 border border-slate-800 rounded-xl overflow-hidden relative h-[180px] sm:h-[190px] flex items-center justify-center select-none">
                            <div class="tank-3d-viewport w-full h-full pointer-events-none select-none"
                                 data-tank-id="{{ $tank->id }}"
                                 data-volume="{{ $vol }}"
                                 data-capacity="{{ $cap }}"
                                 data-height="{{ (float) $tank->height_cm }}">
                            </div>
                            <span class="absolute top-2 left-2 text-[9px] font-mono font-bold px-2 py-0.5 rounded bg-slate-900/80 text-cyan-400 border border-slate-700 pointer-events-none">
                                3D LIVE
                            </span>
                        </div>

                        <!-- Right: Specifications (Ketinggian, Diameter, Panjang, Lebar, Kapasitas) -->
                        <div class="sm:col-span-6 flex flex-col justify-between gap-2">
                            <div class="grid grid-cols-2 gap-2">
                                <!-- Ketinggian -->
                                <div class="bg-slate-950/70 border border-slate-800/80 rounded-xl p-2.5">
                                    <span class="text-[10px] text-slate-400 block font-medium">Ketinggian BBM (Solar)</span>
                                    <span class="text-xs font-bold text-slate-200 font-mono">{{ number_format($h, 1) }} cm</span>
                                    <span class="text-[9px] text-slate-500 block font-mono">Max: {{ number_format($tank->height_cm, 0) }}cm</span>
                                </div>

                                <!-- Diameter -->
                                <div class="bg-slate-950/70 border border-slate-800/80 rounded-xl p-2.5">
                                    <span class="text-[10px] text-slate-400 block font-medium">Diameter</span>
                                    <span class="text-xs font-bold text-slate-200 font-mono">{{ number_format($tank->diameter_cm, 0) }} cm</span>
                                    <span class="text-[9px] text-slate-500 block">Silinder</span>
                                </div>

                                <!-- Panjang & Lebar -->
                                <div class="bg-slate-950/70 border border-slate-800/80 rounded-xl p-2.5">
                                    <span class="text-[10px] text-slate-400 block font-medium">Dimensi (P &times; L)</span>
                                    <span class="text-xs font-bold text-slate-200 font-mono">{{ number_format($tank->length_cm ?? 200, 0) }} &times; {{ number_format($tank->width_cm, 0) }} cm</span>
                                    <span class="text-[9px] text-slate-500 block">Panjang &times; Lebar</span>
                                </div>

                                <!-- Kapasitas Total -->
                                <div class="bg-slate-950/70 border border-slate-800/80 rounded-xl p-2.5">
                                    <span class="text-[10px] text-slate-400 block font-medium">Kapasitas</span>
                                    <span class="text-xs font-bold text-cyan-400 font-mono">{{ number_format($cap, 0) }} L</span>
                                    <span class="text-[9px] text-slate-500 block">Total Volume</span>
                                </div>
                            </div>

                            @if($tank->description)
                                <div class="px-2.5 py-1.5 rounded-lg bg-slate-950/50 border border-slate-800/60 text-[10px] text-slate-400 truncate">
                                    <span class="font-medium text-slate-300">Lokasi:</span> {{ $tank->description }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- 3. Bottom Row: Volume Terkini & Progress Bar -->
                    <div class="space-y-2 mb-4 pt-1">
                        <div class="flex items-baseline justify-between text-xs">
                            <span class="text-slate-400 font-medium">Volume Terkini</span>
                            <div class="flex items-baseline gap-1">
                                <span class="text-base font-extrabold text-white font-mono">{{ number_format($vol, 1) }}</span>
                                <span class="text-xs text-slate-400 font-mono">/ {{ number_format($cap, 1) }} Liter</span>
                                <span class="text-xs font-bold font-mono ml-1
                                    @if($pct >= 90) text-rose-400
                                    @elseif($pct <= 20) text-amber-400
                                    @else text-cyan-400 @endif">
                                    ({{ number_format($pct, 1) }}%)
                                </span>
                            </div>
                        </div>

                        <!-- Level Bar -->
                        <div class="w-full bg-slate-950 rounded-full h-2.5 p-0.5 border border-slate-800 overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500
                                @if($pct >= 90) bg-gradient-to-r from-rose-500 to-red-400
                                @elseif($pct <= 20) bg-gradient-to-r from-amber-500 to-yellow-400
                                @else bg-gradient-to-r from-cyan-500 to-sky-400 @endif"
                                style="width: {{ min(100, max(2, $pct)) }}%">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Card Footer: Operator Action Buttons / 3D Navigation -->
                <div class="pt-3 border-t border-slate-800/60 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                    <span class="text-[11px] text-slate-500 font-mono">Update: {{ $latest ? $latest->created_at->diffForHumans() : '-' }}</span>
                    
                    <div class="flex items-center gap-2 flex-wrap">
                        @if(auth()->user()->isOperator())
                            <!-- Button 1: Input Pemasukan BBM (Khusus Operator) -->
                            <button type="button" onclick="openPemasukanModal({{ $tank->id }})" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span>Input Pemasukan BBM</span>
                            </button>

                            <!-- Button 2: Input Posisi Tangki Pemakaian (Khusus Operator) -->
                            <button type="button" onclick="openPemakaianModal({{ $tank->id }})" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-500 active:bg-amber-700 text-white text-xs font-bold shadow-md shadow-amber-600/20 transition">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                </svg>
                                <span>Input Pemakaian</span>
                            </button>
                        @endif

                        <!-- Button 3: View Tangki 3D (Ikon Mata) untuk Semua User (Operator & Staff) -->
                        <a href="{{ route('monitoring.show', $tank->id) }}" 
                            title="Lihat Detail Visual 3D Tangki" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-cyan-400 border border-slate-700 hover:border-cyan-500/50 text-xs font-bold transition shadow-sm group">
                            <svg class="w-4 h-4 text-cyan-400 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span>View Tangki</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- ================= MODAL 1: INPUT PEMASUKAN BBM ================= -->
            <div id="modal-pemasukan-{{ $tank->id }}" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm hidden">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5 animate-in fade-in zoom-in-95 duration-150">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3.5">
                        <div class="flex items-center space-x-2.5">
                            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-bold">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-white">Input Pemasukan BBM</h3>
                                <p class="text-[11px] text-slate-400 font-mono">{{ $tank->name }} ({{ $tank->code }})</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('monitoring.show', $tank->id) }}" target="_blank" title="Buka Detail Visual 3D Tangki" 
                                class="px-2 py-1 rounded-lg text-slate-400 hover:text-cyan-400 hover:bg-slate-800 transition flex items-center gap-1 text-[11px] font-semibold">
                                <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span class="hidden sm:inline">View 3D</span>
                            </a>
                            <button type="button" onclick="closePemasukanModal({{ $tank->id }})" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Tank Parameters Reference -->
                    <div class="grid grid-cols-3 gap-2 bg-slate-950/60 p-3 rounded-xl border border-slate-800 text-[11px]">
                        <div>
                            <span class="text-slate-500 block">Tinggi Maks Admin</span>
                            <span class="font-bold text-slate-200 font-mono">{{ number_format($tank->height_cm, 1) }} cm</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Kapasitas Maks</span>
                            <span class="font-bold text-cyan-400 font-mono">{{ number_format($cap, 0) }} L</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Posisi Sekarang</span>
                            <span class="font-bold text-emerald-400 font-mono">{{ number_format($h, 1) }} cm ({{ number_format($vol, 1) }} L)</span>
                        </div>
                    </div>

                    <form action="{{ route('monitoring.recordBbm', $tank->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="type" value="pemasukan">

                        <!-- Input Ketinggian BBM (cm) -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="pemasukan_height_{{ $tank->id }}" class="block text-xs font-semibold text-slate-200">
                                    Ketinggian BBM Baru (cm) <span class="text-rose-400">*</span>
                                </label>
                                <span class="text-[10px] text-slate-400 font-mono">Max: {{ number_format($tank->height_cm, 1) }} cm</span>
                            </div>
                            <input type="number" step="0.1" min="0" max="{{ (float)$tank->height_cm }}" 
                                id="pemasukan_height_{{ $tank->id }}" name="height_cm" value="{{ $h }}" required
                                oninput="calcPemasukan({{ $tank->id }}, {{ (float)$tank->height_cm }}, {{ (float)$cap }}, {{ (float)$vol }})"
                                class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white font-mono focus:outline-none focus:border-emerald-500">
                            <p id="pemasukan_warning_{{ $tank->id }}" class="text-[11px] text-rose-400 mt-1 hidden font-medium">
                                &bull; Ketinggian melebihi batas maksimal tangki ({{ number_format($tank->height_cm, 1) }} cm) yang ditentukan oleh Admin!
                            </p>
                        </div>

                        <!-- Auto Calculation Box (Liter Ketahuan dari Ketinggian) -->
                        <div class="bg-emerald-950/20 border border-emerald-500/30 rounded-xl p-3.5 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-300 font-medium">Volume Otomatis Terhitung:</span>
                                <span class="text-base font-extrabold text-emerald-400 font-mono" id="pemasukan_vol_{{ $tank->id }}">
                                    {{ number_format($vol, 1) }} Liter
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] pt-1 border-t border-emerald-500/20 text-slate-400">
                                <span>Penambahan Volume BBM:</span>
                                <span class="font-bold text-emerald-300 font-mono" id="pemasukan_delta_{{ $tank->id }}">+0.0 Liter</span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-slate-400">
                                <span>Persentase Kapasitas:</span>
                                <span class="font-bold text-cyan-300 font-mono" id="pemasukan_pct_{{ $tank->id }}">{{ number_format($pct, 1) }}%</span>
                            </div>
                        </div>

                        <!-- Upload Foto Bukti (Tanda + dengan Pilihan File / Kamera) -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>Foto Bukti Pemasukan</span>
                                </span>
                                <span class="text-[10px] text-slate-500 font-mono">JPG/PNG maks 10MB</span>
                            </label>
                            
                            <!-- Hidden File & Camera Inputs -->
                            <input type="file" name="photo" id="pemasukan_photo_{{ $tank->id }}" accept="image/*" class="hidden"
                                onchange="previewPhoto(event, 'pemasukan_preview_{{ $tank->id }}', 'pemasukan_file_label_{{ $tank->id }}')">
                            <input type="file" id="pemasukan_camera_{{ $tank->id }}" accept="image/*" capture="environment" class="hidden"
                                onchange="handleCameraCapture(event, 'pemasukan_photo_{{ $tank->id }}', 'pemasukan_preview_{{ $tank->id }}', 'pemasukan_file_label_{{ $tank->id }}')">

                            <!-- Plus Button Container with Dropdown Menu -->
                            <div class="relative photo-menu-container">
                                <div class="flex items-center gap-3 p-2 bg-slate-950 border border-slate-800 rounded-xl">
                                    <button type="button" 
                                        onclick="togglePhotoMenu(event, 'pemasukan_menu_{{ $tank->id }}')" 
                                        class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 active:scale-95 text-slate-200 hover:text-white flex items-center justify-center transition border border-slate-700 shadow-sm"
                                        title="Tambah Foto Bukti">
                                        <svg class="w-4 h-4 text-cyan-400 font-bold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                    <span class="text-xs text-slate-400 truncate flex-1" id="pemasukan_file_label_{{ $tank->id }}">
                                        Klik tanda plus (+) untuk unggah bukti...
                                    </span>
                                </div>

                                <!-- Floating Dropdown Menu (Matching User Reference Image) -->
                                <div id="pemasukan_menu_{{ $tank->id }}" 
                                    class="photo-dropdown-menu hidden absolute left-0 top-12 z-30 w-64 bg-slate-900/95 border border-slate-700/80 rounded-2xl shadow-2xl p-1.5 backdrop-blur-md">
                                    <button type="button" 
                                        onclick="triggerUploadOption('pemasukan_photo_{{ $tank->id }}', 'pemasukan_menu_{{ $tank->id }}')" 
                                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-left hover:bg-slate-800 text-slate-200 hover:text-white transition group">
                                        <div class="flex items-center gap-2.5">
                                            <svg class="w-4 h-4 text-slate-400 group-hover:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                            </svg>
                                            <span class="text-xs font-medium">Tambahkan file atau foto</span>
                                        </div>
                                    </button>
                                    <button type="button" 
                                        onclick="openLiveCamera('pemasukan_photo_{{ $tank->id }}', 'pemasukan_preview_{{ $tank->id }}', 'pemasukan_file_label_{{ $tank->id }}', 'pemasukan_menu_{{ $tank->id }}')" 
                                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-left hover:bg-slate-800 text-slate-200 hover:text-white transition group">
                                        <div class="flex items-center gap-2.5">
                                            <svg class="w-4 h-4 text-slate-400 group-hover:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span class="text-xs font-medium">Ambil tangkapan kamera</span>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- Live Image Preview Container -->
                            <div id="pemasukan_preview_{{ $tank->id }}" class="mt-2.5 hidden rounded-xl overflow-hidden border border-slate-800 bg-slate-950/80 p-2 flex items-center gap-3">
                                <img src="" alt="Pratinjau Foto" class="w-12 h-12 object-cover rounded-lg border border-slate-700 preview-img">
                                <div class="flex-1 min-w-0">
                                    <span class="text-xs font-semibold text-white block truncate preview-filename">filename.jpg</span>
                                    <span class="text-[10px] text-emerald-400 font-mono">Foto bukti siap disimpan</span>
                                </div>
                                <button type="button" onclick="clearPhoto('pemasukan_photo_{{ $tank->id }}', 'pemasukan_preview_{{ $tank->id }}', 'pemasukan_file_label_{{ $tank->id }}', 'pemasukan_camera_{{ $tank->id }}')" 
                                    class="text-slate-400 hover:text-rose-400 p-1.5 rounded-lg hover:bg-slate-800 transition text-xs font-bold">
                                    Batal
                                </button>
                            </div>
                        </div>

                        <!-- Catatan / Keterangan -->
                        <div>
                            <label for="pemasukan_notes_{{ $tank->id }}" class="block text-xs font-semibold text-slate-300 mb-1.5">Keterangan / No. DO / Sumber (Opsional)</label>
                            <input type="text" id="pemasukan_notes_{{ $tank->id }}" name="notes" 
                                placeholder="Contoh: Penerimaan BBM Truk Tangki #12"
                                class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500">
                        </div>

                        <!-- Submit Buttons -->
                        <div class="pt-3 border-t border-slate-800 flex justify-end space-x-2.5">
                            <button type="button" onclick="closePemasukanModal({{ $tank->id }})" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-lg shadow-emerald-600/25 transition flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Simpan Pemasukan BBM</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ================= MODAL 2: INPUT POSISI TANGKI PEMAKAIAN ================= -->
            <div id="modal-pemakaian-{{ $tank->id }}" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm hidden">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5 animate-in fade-in zoom-in-95 duration-150">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3.5">
                        <div class="flex items-center space-x-2.5">
                            <div class="w-8 h-8 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 font-bold">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-white">Input Posisi Tangki Pemakaian</h3>
                                <p class="text-[11px] text-slate-400 font-mono">{{ $tank->name }} ({{ $tank->code }})</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('monitoring.show', $tank->id) }}" target="_blank" title="Buka Detail Visual 3D Tangki" 
                                class="px-2 py-1 rounded-lg text-slate-400 hover:text-cyan-400 hover:bg-slate-800 transition flex items-center gap-1 text-[11px] font-semibold">
                                <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span class="hidden sm:inline">View 3D</span>
                            </a>
                            <button type="button" onclick="closePemakaianModal({{ $tank->id }})" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Tank Parameters Reference -->
                    <div class="grid grid-cols-3 gap-2 bg-slate-950/60 p-3 rounded-xl border border-slate-800 text-[11px]">
                        <div>
                            <span class="text-slate-500 block">Posisi Awal Pengisian</span>
                            <span class="font-bold text-white font-mono">{{ number_format($h, 1) }} cm</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Volume Awal</span>
                            <span class="font-bold text-cyan-400 font-mono">{{ number_format($vol, 1) }} L</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block">Tinggi Maks Tangki</span>
                            <span class="font-bold text-slate-300 font-mono">{{ number_format($tank->height_cm, 1) }} cm</span>
                        </div>
                    </div>

                    <form action="{{ route('monitoring.recordBbm', $tank->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="type" value="pemakaian">

                        <!-- Input Sisa Ketinggian BBM (cm) -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="pemakaian_height_{{ $tank->id }}" class="block text-xs font-semibold text-slate-200">
                                    Sisa Ketinggian BBM (cm) <span class="text-rose-400">*</span>
                                </label>
                                <span class="text-[10px] text-slate-400 font-mono">Posisi Awal: {{ number_format($h, 1) }} cm</span>
                            </div>
                            <input type="number" step="0.1" min="0" max="{{ (float)$tank->height_cm }}" 
                                id="pemakaian_height_{{ $tank->id }}" name="height_cm" value="{{ $h }}" required
                                oninput="calcPemakaian({{ $tank->id }}, {{ (float)$tank->height_cm }}, {{ (float)$cap }}, {{ (float)$vol }})"
                                class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-sm text-white font-mono focus:outline-none focus:border-amber-500">
                            <p id="pemakaian_warning_{{ $tank->id }}" class="text-[11px] text-rose-400 mt-1 hidden font-medium">
                                &bull; Ketinggian melebihi batas maksimal tangki ({{ number_format($tank->height_cm, 1) }} cm)!
                            </p>
                        </div>

                        <!-- Auto Calculation Box (Liter Terpakai Dikurangi dari Posisi Awal) -->
                        <div class="bg-amber-950/20 border border-amber-500/30 rounded-xl p-3.5 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-300 font-medium">BBM Terpakai (Berkurang):</span>
                                <span class="text-base font-extrabold text-amber-400 font-mono" id="pemakaian_used_{{ $tank->id }}">
                                    -0.0 Liter
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] pt-1 border-t border-amber-500/20 text-slate-400">
                                <span>Volume Sisa di Tangki:</span>
                                <span class="font-bold text-slate-200 font-mono" id="pemakaian_vol_{{ $tank->id }}">{{ number_format($vol, 1) }} Liter</span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-slate-400">
                                <span>Persentase Sisa:</span>
                                <span class="font-bold text-cyan-300 font-mono" id="pemakaian_pct_{{ $tank->id }}">{{ number_format($pct, 1) }}%</span>
                            </div>
                        </div>

                        <!-- Upload Foto Bukti (Tanda + dengan Pilihan File / Kamera) -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>Foto Bukti Pemakaian</span>
                                </span>
                                <span class="text-[10px] text-slate-500 font-mono">JPG/PNG maks 10MB</span>
                            </label>
                            
                            <!-- Hidden File & Camera Inputs -->
                            <input type="file" name="photo" id="pemakaian_photo_{{ $tank->id }}" accept="image/*" class="hidden"
                                onchange="previewPhoto(event, 'pemakaian_preview_{{ $tank->id }}', 'pemakaian_file_label_{{ $tank->id }}')">
                            <input type="file" id="pemakaian_camera_{{ $tank->id }}" accept="image/*" capture="environment" class="hidden"
                                onchange="handleCameraCapture(event, 'pemakaian_photo_{{ $tank->id }}', 'pemakaian_preview_{{ $tank->id }}', 'pemakaian_file_label_{{ $tank->id }}')">

                            <!-- Plus Button Container with Dropdown Menu -->
                            <div class="relative photo-menu-container">
                                <div class="flex items-center gap-3 p-2 bg-slate-950 border border-slate-800 rounded-xl">
                                    <button type="button" 
                                        onclick="togglePhotoMenu(event, 'pemakaian_menu_{{ $tank->id }}')" 
                                        class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 active:scale-95 text-slate-200 hover:text-white flex items-center justify-center transition border border-slate-700 shadow-sm"
                                        title="Tambah Foto Bukti">
                                        <svg class="w-4 h-4 text-cyan-400 font-bold" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                    <span class="text-xs text-slate-400 truncate flex-1" id="pemakaian_file_label_{{ $tank->id }}">
                                        Klik tanda plus (+) untuk unggah bukti...
                                    </span>
                                </div>

                                <!-- Floating Dropdown Menu (Matching User Reference Image) -->
                                <div id="pemakaian_menu_{{ $tank->id }}" 
                                    class="photo-dropdown-menu hidden absolute left-0 top-12 z-30 w-64 bg-slate-900/95 border border-slate-700/80 rounded-2xl shadow-2xl p-1.5 backdrop-blur-md">
                                    <button type="button" 
                                        onclick="triggerUploadOption('pemakaian_photo_{{ $tank->id }}', 'pemakaian_menu_{{ $tank->id }}')" 
                                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-left hover:bg-slate-800 text-slate-200 hover:text-white transition group">
                                        <div class="flex items-center gap-2.5">
                                            <svg class="w-4 h-4 text-slate-400 group-hover:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                            </svg>
                                            <span class="text-xs font-medium">Tambahkan file atau foto</span>
                                        </div>
                                    </button>
                                    <button type="button" 
                                        onclick="openLiveCamera('pemakaian_photo_{{ $tank->id }}', 'pemakaian_preview_{{ $tank->id }}', 'pemakaian_file_label_{{ $tank->id }}', 'pemakaian_menu_{{ $tank->id }}')" 
                                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-left hover:bg-slate-800 text-slate-200 hover:text-white transition group">
                                        <div class="flex items-center gap-2.5">
                                            <svg class="w-4 h-4 text-slate-400 group-hover:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span class="text-xs font-medium">Ambil tangkapan kamera</span>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- Live Image Preview Container -->
                            <div id="pemakaian_preview_{{ $tank->id }}" class="mt-2.5 hidden rounded-xl overflow-hidden border border-slate-800 bg-slate-950/80 p-2 flex items-center gap-3">
                                <img src="" alt="Pratinjau Foto" class="w-12 h-12 object-cover rounded-lg border border-slate-700 preview-img">
                                <div class="flex-1 min-w-0">
                                    <span class="text-xs font-semibold text-white block truncate preview-filename">filename.jpg</span>
                                    <span class="text-[10px] text-amber-400 font-mono">Foto bukti siap disimpan</span>
                                </div>
                                <button type="button" onclick="clearPhoto('pemakaian_photo_{{ $tank->id }}', 'pemakaian_preview_{{ $tank->id }}', 'pemakaian_file_label_{{ $tank->id }}', 'pemakaian_camera_{{ $tank->id }}')" 
                                    class="text-slate-400 hover:text-rose-400 p-1.5 rounded-lg hover:bg-slate-800 transition text-xs font-bold">
                                    Batal
                                </button>
                            </div>
                        </div>

                        <!-- Catatan / Keterangan Keperluan -->
                        <div>
                            <label for="pemakaian_notes_{{ $tank->id }}" class="block text-xs font-semibold text-slate-300 mb-1.5">Keperluan Pemakaian / Unit Operasional (Opsional)</label>
                            <input type="text" id="pemakaian_notes_{{ $tank->id }}" name="notes" 
                                placeholder="Contoh: Suplai genset operasional dermaga barat"
                                class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-500">
                        </div>

                        <!-- Submit Buttons -->
                        <div class="pt-3 border-t border-slate-800 flex justify-end space-x-2.5">
                            <button type="button" onclick="closePemakaianModal({{ $tank->id }})" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold shadow-lg shadow-amber-600/25 transition flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Simpan Pemakaian BBM</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-2 text-center py-12 bg-slate-900 border border-slate-800 rounded-2xl">
                <p class="text-slate-400 text-sm">Belum ada data tangki aktif.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Live Camera Capture Modal -->
<div id="camera-capture-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md hidden">
    <div class="bg-slate-900 border border-slate-700/80 rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="px-5 py-3.5 border-b border-slate-800 flex items-center justify-between bg-slate-950/50">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <h3 class="text-xs font-bold text-white uppercase tracking-wider">Tangkapan Kamera Langsung</h3>
            </div>
            <button type="button" onclick="closeCameraModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Video Stream Viewport -->
        <div class="relative bg-black aspect-video flex items-center justify-center overflow-hidden">
            <video id="camera-video" autoplay playsinline class="w-full h-full object-cover"></video>
            <canvas id="camera-canvas" class="hidden"></canvas>
            
            <div id="camera-loading" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-950/80 text-slate-300 gap-2">
                <svg class="w-6 h-6 animate-spin text-cyan-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-xs">Menghubungkan ke kamera...</span>
            </div>

            <!-- Error state -->
            <div id="camera-error" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-950 p-6 text-center hidden">
                <p class="text-rose-400 text-xs font-semibold mb-1" id="camera-error-msg">Tidak dapat mengakses kamera.</p>
                <p class="text-[11px] text-slate-400 mb-4">Pastikan izin kamera diizinkan pada browser Anda.</p>
                <button type="button" onclick="closeCameraModal()" class="px-4 py-1.5 rounded-xl bg-slate-800 text-slate-300 text-xs font-bold hover:bg-slate-700">Tutup</button>
            </div>
        </div>

        <!-- Camera Controls -->
        <div class="p-4 bg-slate-950 border-t border-slate-800 flex items-center justify-between">
            <button type="button" onclick="closeCameraModal()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">
                Batal
            </button>
            <button type="button" onclick="captureCameraSnapshot()" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white text-xs font-bold shadow-lg shadow-emerald-600/30 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <circle cx="12" cy="12" r="3" stroke-width="2"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                </svg>
                <span>Ambil Foto</span>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="module">
    import * as THREE from 'three';
    import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';

    // Model path
    const MODEL_URL = "{{ asset('assets/tangki.glb') }}";

    // Geometry parameters matching tangki.glb
    const WATER_RADIUS      = 0.450;
    const WATER_LENGTH      = 2.000;
    const WATER_CENTER_Y    = 1.000;
    const WATER_MIN_Y       = WATER_CENTER_Y - WATER_RADIUS; // 0.550
    const WATER_MAX_Y       = WATER_CENTER_Y + WATER_RADIUS; // 1.450
    const WATER_FILL_RANGE  = WATER_MAX_Y - WATER_MIN_Y;    // 0.900 m
    const TANK_Z            = 0.000;

    // Load GLTF once and reuse across cards
    const gltfLoader = new GLTFLoader();
    gltfLoader.load(MODEL_URL, (gltf) => {
        const baseModel = gltf.scene;

        document.querySelectorAll('.tank-3d-viewport').forEach((container) => {
            initMini3dCard(container, baseModel);
        });
    }, undefined, (err) => {
        console.warn('Could not load base model for cards:', err);
    });

    function initMini3dCard(container, baseModel) {
        const width = container.clientWidth || 200;
        const height = container.clientHeight || 180;
        const volume = parseFloat(container.dataset.volume) || 0;
        const capacity = parseFloat(container.dataset.capacity) || 100;

        // Scene
        const scene = new THREE.Scene();
        scene.background = new THREE.Color(0x050b14);

        // Camera (Fixed Position & Fixed Target)
        const camera = new THREE.PerspectiveCamera(40, width / height, 0.1, 50);
        camera.position.set(2.4, 1.6, 2.4);
        camera.lookAt(0, 1.0, 0);

        // Renderer
        const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        renderer.setSize(width, height);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        renderer.toneMapping = THREE.ACESFilmicToneMapping;
        renderer.toneMappingExposure = 1.1;
        renderer.localClippingEnabled = true;
        container.appendChild(renderer.domElement);

        // Lighting
        const ambientLight = new THREE.AmbientLight(0xffffff, 1.5);
        scene.add(ambientLight);

        const dirLight = new THREE.DirectionalLight(0xffffff, 2.0);
        dirLight.position.set(3, 5, 3);
        scene.add(dirLight);

        const fillLight = new THREE.DirectionalLight(0x38bdf8, 1.2);
        fillLight.position.set(-3, 3, -3);
        scene.add(fillLight);

        // Pedestal
        const pedestalGeo = new THREE.BoxGeometry(2.5, 0.03, 2.5);
        const pedestalMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.8 });
        const pedestal = new THREE.Mesh(pedestalGeo, pedestalMat);
        pedestal.position.y = -0.015;
        scene.add(pedestal);

        // Water Material & Clipping Plane
        const ratio = Math.max(0, Math.min(1, volume / capacity));
        const currentHeight = WATER_MIN_Y + ratio * WATER_FILL_RANGE;

        const waterClipPlane = new THREE.Plane(new THREE.Vector3(0, -1, 0), currentHeight);
        
        let waterColor = 0xf59e0b;
        let waterEmissive = 0xd97706;
        if (ratio >= 0.95) {
            waterColor = 0xf43f5e;
            waterEmissive = 0xbe123c;
        } else if (ratio <= 0.20 && volume > 0) {
            waterColor = 0xf97316;
            waterEmissive = 0xc2410c;
        }

        const waterMaterial = new THREE.MeshPhysicalMaterial({
            color: waterColor,
            emissive: waterEmissive,
            emissiveIntensity: 0.2,
            roughness: 0.1,
            metalness: 0.05,
            transmission: 0.5,
            transparent: true,
            opacity: 0.85,
            clippingPlanes: [waterClipPlane],
            side: THREE.DoubleSide,
            depthWrite: false
        });

        // Water Cylinder
        const waterCylinderGeo = new THREE.CylinderGeometry(WATER_RADIUS, WATER_RADIUS, WATER_LENGTH, 32, 1, false);
        waterCylinderGeo.rotateZ(Math.PI / 2);
        const waterMesh = new THREE.Mesh(waterCylinderGeo, waterMaterial);
        waterMesh.position.set(0, WATER_CENTER_Y, TANK_Z);

        if (volume > 0.05) {
            scene.add(waterMesh);

            // Water Top Surface Cap
            const surfaceMaterial = new THREE.MeshPhysicalMaterial({
                color: waterColor,
                emissive: waterEmissive,
                emissiveIntensity: 0.25,
                roughness: 0.05,
                transparent: true,
                opacity: 0.9,
                side: THREE.DoubleSide,
                depthWrite: false
            });
            const surfaceGeo = new THREE.PlaneGeometry(WATER_LENGTH, 1.0, 1, 1);
            surfaceGeo.rotateX(-Math.PI / 2);
            const surfaceMesh = new THREE.Mesh(surfaceGeo, surfaceMaterial);
            surfaceMesh.position.set(0, currentHeight, TANK_Z);

            const dy = currentHeight - WATER_CENTER_Y;
            const rSq = Math.max(0, WATER_RADIUS * WATER_RADIUS - dy * dy);
            const surfaceWidthZ = 2 * Math.sqrt(rSq);
            surfaceMesh.scale.set(1.0, 1.0, Math.max(0.005, surfaceWidthZ - 0.006));
            scene.add(surfaceMesh);
        }

        // Clone Tank Model
        const tankClone = baseModel.clone();
        tankClone.position.set(-1.0, 0, -0.9);

        // Glass tank material for outer body
        const glassMaterial = new THREE.MeshPhysicalMaterial({
            color: 0x94a3b8,
            roughness: 0.15,
            transmission: 0.85,
            transparent: true,
            opacity: 0.4,
            ior: 1.45,
            depthWrite: false,
            side: THREE.DoubleSide
        });

        tankClone.traverse((child) => {
            if (child.isMesh && (child.name.includes('Body') || child.name.includes('TankKiri_Body'))) {
                child.material = glassMaterial;
            }
            if (child.name && child.name.includes('Manhole_Lid_Hinge')) {
                // Open lid slightly in preview
                child.rotation.x = (2 * Math.PI / 3);
            }
        });

        scene.add(tankClone);

        // Animation render loop
        function animate() {
            requestAnimationFrame(animate);
            renderer.render(scene, camera);
        }
        animate();

        // Responsive resize
        const resizeObserver = new ResizeObserver(() => {
            const newW = container.clientWidth;
            const newH = container.clientHeight;
            if (newW > 0 && newH > 0) {
                camera.aspect = newW / newH;
                camera.updateProjectionMatrix();
                renderer.setSize(newW, newH);
            }
        });
        resizeObserver.observe(container);
    }

    // Modal Control & Calculation Functions (Attached to window for inline onclick handlers)
    window.openPemasukanModal = function(tankId) {
        const modal = document.getElementById(`modal-pemasukan-${tankId}`);
        if (modal) modal.classList.remove('hidden');
    };

    window.closePemasukanModal = function(tankId) {
        const modal = document.getElementById(`modal-pemasukan-${tankId}`);
        if (modal) modal.classList.add('hidden');
    };

    window.openPemakaianModal = function(tankId) {
        const modal = document.getElementById(`modal-pemakaian-${tankId}`);
        if (modal) modal.classList.remove('hidden');
    };

    window.closePemakaianModal = function(tankId) {
        const modal = document.getElementById(`modal-pemakaian-${tankId}`);
        if (modal) modal.classList.add('hidden');
    };

    window.calcPemasukan = function(tankId, maxHeight, maxCapacity, currentVol) {
        const input = document.getElementById(`pemasukan_height_${tankId}`);
        const previewVol = document.getElementById(`pemasukan_vol_${tankId}`);
        const previewDelta = document.getElementById(`pemasukan_delta_${tankId}`);
        const previewPct = document.getElementById(`pemasukan_pct_${tankId}`);
        const warning = document.getElementById(`pemasukan_warning_${tankId}`);

        let h = parseFloat(input.value) || 0;
        if (h > maxHeight) {
            if (warning) warning.classList.remove('hidden');
            input.classList.add('border-rose-500');
        } else {
            if (warning) warning.classList.add('hidden');
            input.classList.remove('border-rose-500');
        }

        const vol = Math.min(maxCapacity, Math.max(0, (h / maxHeight) * maxCapacity));
        const delta = vol - currentVol;
        const pct = Math.min(100, Math.max(0, (vol / maxCapacity) * 100));

        if (previewVol) previewVol.textContent = `${vol.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 })} Liter`;
        if (previewDelta) {
            previewDelta.textContent = delta >= 0 
                ? `+${delta.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 })} Liter` 
                : `${delta.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 })} Liter`;
        }
        if (previewPct) previewPct.textContent = `${pct.toFixed(1)}%`;
    };

    window.calcPemakaian = function(tankId, maxHeight, maxCapacity, currentVol) {
        const input = document.getElementById(`pemakaian_height_${tankId}`);
        const previewVol = document.getElementById(`pemakaian_vol_${tankId}`);
        const previewUsed = document.getElementById(`pemakaian_used_${tankId}`);
        const previewPct = document.getElementById(`pemakaian_pct_${tankId}`);
        const warning = document.getElementById(`pemakaian_warning_${tankId}`);

        let h = parseFloat(input.value) || 0;
        if (h > maxHeight) {
            if (warning) warning.classList.remove('hidden');
            input.classList.add('border-rose-500');
        } else {
            if (warning) warning.classList.add('hidden');
            input.classList.remove('border-rose-500');
        }

        const volRemaining = Math.min(maxCapacity, Math.max(0, (h / maxHeight) * maxCapacity));
        const volUsed = currentVol - volRemaining; // Dikurangi dari awal pengisian!
        const pct = Math.min(100, Math.max(0, (volRemaining / maxCapacity) * 100));

        if (previewVol) previewVol.textContent = `${volRemaining.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 })} Liter`;
        if (previewUsed) previewUsed.textContent = `-${Math.max(0, volUsed).toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 })} Liter`;
        if (previewPct) previewPct.textContent = `${pct.toFixed(1)}%`;
    };

    window.previewPhoto = function(event, previewId, labelId) {
        const file = event.target.files[0];
        if (!file) { return; }
        const preview = document.getElementById(previewId);
        const label = document.getElementById(labelId);
        const reader = new FileReader();
        reader.onload = function(e) {
            if (preview) {
                preview.classList.remove('hidden');
                const img = preview.querySelector('.preview-img');
                const fname = preview.querySelector('.preview-filename');
                if (img) { img.src = e.target.result; }
                if (fname) { fname.textContent = file.name; }
            }
            if (label) { label.textContent = file.name; }
        };
        reader.readAsDataURL(file);
    };

    window.handleCameraCapture = function(event, mainInputId, previewId, labelId) {
        const file = event.target.files[0];
        if (!file) { return; }
        const mainInput = document.getElementById(mainInputId);
        if (mainInput && event.target.files) {
            try {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                mainInput.files = dataTransfer.files;
            } catch (e) {
                console.warn('DataTransfer sync error:', e);
            }
        }
        window.previewPhoto(event, previewId, labelId);
    };

    window.togglePhotoMenu = function(event, menuId) {
        if (event) { event.stopPropagation(); }
        const menu = document.getElementById(menuId);
        if (!menu) { return; }
        const isHidden = menu.classList.contains('hidden');
        document.querySelectorAll('.photo-dropdown-menu').forEach(el => el.classList.add('hidden'));
        if (isHidden) {
            menu.classList.remove('hidden');
        }
    };

    window.triggerUploadOption = function(inputId, menuId) {
        const menu = document.getElementById(menuId);
        if (menu) { menu.classList.add('hidden'); }
        const input = document.getElementById(inputId);
        if (input) { input.click(); }
    };

    window.clearPhoto = function(mainInputId, previewId, labelId, camInputId) {
        const input = document.getElementById(mainInputId);
        const camInput = camInputId ? document.getElementById(camInputId) : null;
        const preview = document.getElementById(previewId);
        const label = document.getElementById(labelId);
        if (input) { input.value = ''; }
        if (camInput) { camInput.value = ''; }
        if (preview) { preview.classList.add('hidden'); }
        if (label) { label.textContent = 'Klik tanda plus (+) untuk unggah bukti...'; }
    };

    // Close any open popover when clicking anywhere outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.photo-menu-container') && !e.target.closest('.photo-dropdown-menu')) {
            document.querySelectorAll('.photo-dropdown-menu').forEach(el => el.classList.add('hidden'));
        }
    });
</script>
@endpush


