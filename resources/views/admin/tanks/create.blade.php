@extends('layouts.app')

@section('title', 'Tambah Tangki Baru')
@section('page-title', 'Tambah Tangki Baru')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.tanks.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white border border-slate-800 text-xs font-semibold transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Daftar Tangki</span>
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-5">
        <div class="border-b border-slate-800 pb-4">
            <h3 class="text-sm font-bold text-white">Formulir Spesifikasi Tangki 3D</h3>
            <p class="text-xs text-slate-400 mt-0.5">Tangki yang ditambahkan akan langsung muncul pada menu monitoring staff.</p>
        </div>

        @if($errors->any())
            <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs space-y-1">
                @foreach($errors->all() as $err)
                    <div>&bull; {{ $err }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('admin.tanks.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-300 mb-1.5">Nama Tangki</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                        placeholder="Contoh: Tangki E (Zona 4)"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500">
                </div>

                <!-- Code -->
                <div>
                    <label for="code" class="block text-xs font-semibold text-slate-300 mb-1.5">Kode Unik Tangki</label>
                    <input type="text" id="code" name="code" value="{{ old('code') }}" required
                        placeholder="Contoh: TNK-E"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500 font-mono">
                </div>
            </div>

            <!-- Parameters Grid (Panjang, Lebar, Tinggi, Diameter) -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-2">Dimensi Fisik Tangki</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div>
                        <label for="length_cm" class="block text-[11px] font-medium text-slate-400 mb-1">Panjang (cm)</label>
                        <input type="number" step="0.5" id="length_cm" name="length_cm" value="{{ old('length_cm', 200.0) }}" required min="1"
                            class="w-full px-3 py-2 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-white font-mono focus:outline-none focus:border-cyan-500 dimension-input">
                    </div>

                    <div>
                        <label for="width_cm" class="block text-[11px] font-medium text-slate-400 mb-1">Lebar (cm)</label>
                        <input type="number" step="0.5" id="width_cm" name="width_cm" value="{{ old('width_cm', 90.0) }}" required min="1"
                            class="w-full px-3 py-2 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-white font-mono focus:outline-none focus:border-cyan-500 dimension-input">
                    </div>

                    <div>
                        <label for="height_cm" class="block text-[11px] font-medium text-slate-400 mb-1">Tinggi (cm)</label>
                        <input type="number" step="0.5" id="height_cm" name="height_cm" value="{{ old('height_cm', 70.0) }}" required min="1"
                            class="w-full px-3 py-2 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-white font-mono focus:outline-none focus:border-cyan-500 dimension-input">
                    </div>

                    <div>
                        <label for="diameter_cm" class="block text-[11px] font-medium text-slate-400 mb-1">Diameter (cm)</label>
                        <input type="number" step="0.5" id="diameter_cm" name="diameter_cm" value="{{ old('diameter_cm', 90.0) }}" required min="1"
                            class="w-full px-3 py-2 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-white font-mono focus:outline-none focus:border-cyan-500 dimension-input">
                    </div>
                </div>
            </div>

            <!-- Auto Calculated Capacity Box -->
            <div class="bg-cyan-950/30 border border-cyan-500/30 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center text-cyan-400 font-bold shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-white">Kapasitas Tangki (Otomatis)</span>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 font-mono font-semibold">Auto-calculated</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-0.5">Dihitung otomatis dari volume silinder (Panjang & Diameter).</p>
                    </div>
                </div>

                <div class="text-right shrink-0 bg-slate-950/60 px-4 py-2 rounded-xl border border-cyan-500/20">
                    <div class="text-lg font-extrabold text-cyan-400 font-mono" id="capacityDisplay">0.0 Liter</div>
                    <input type="hidden" id="capacity_liters" name="capacity_liters" value="{{ old('capacity_liters', 100.0) }}">
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-xs font-semibold text-slate-300 mb-1.5">Deskripsi / Lokasi (Opsional)</label>
                <textarea id="description" name="description" rows="2"
                    placeholder="Contoh: Tangki penampungan suplai BBM genset dermaga barat"
                    class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500">{{ old('description') }}</textarea>
            </div>

            <!-- Status Active Toggle -->
            <div class="flex items-center space-x-2 pt-2">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                    class="w-4 h-4 rounded bg-slate-950 border-slate-800 text-cyan-600 focus:ring-0">
                <label for="is_active" class="text-xs font-semibold text-slate-300 cursor-pointer">
                    Aktifkan Tangki (Tampilkan pada grid monitoring)
                </label>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-slate-800 flex justify-end space-x-3">
                <a href="{{ route('admin.tanks.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold shadow-lg shadow-cyan-600/20 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Simpan Tangki</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const lengthInput = document.getElementById('length_cm');
        const widthInput = document.getElementById('width_cm');
        const heightInput = document.getElementById('height_cm');
        const diameterInput = document.getElementById('diameter_cm');
        const capacityHidden = document.getElementById('capacity_liters');
        const capacityDisplay = document.getElementById('capacityDisplay');

        function updateCapacity() {
            const length = parseFloat(lengthInput.value) || 0;
            const width = parseFloat(widthInput.value) || 0;
            const height = parseFloat(heightInput.value) || 0;
            const diameter = parseFloat(diameterInput.value) || 0;

            let volume = 0;
            if (diameter > 0 && length > 0) {
                const radius = diameter / 2;
                volume = (Math.PI * Math.pow(radius, 2) * length) / 1000;
            } else if (length > 0 && width > 0 && height > 0) {
                volume = (length * width * height) / 1000;
            }

            const finalVol = Math.round(volume * 10) / 10;
            capacityHidden.value = finalVol > 0 ? finalVol.toFixed(1) : '100.0';
            capacityDisplay.textContent = finalVol > 0 ? `${finalVol.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 })} Liter` : '0.0 Liter';
        }

        document.querySelectorAll('.dimension-input').forEach(input => {
            input.addEventListener('input', updateCapacity);
        });

        // Initial run
        updateCapacity();
    });
</script>
@endpush
@endsection

