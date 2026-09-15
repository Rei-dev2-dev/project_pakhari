@extends('layouts.app')

@section('title', 'Tambah Menu Sidebar')
@section('page-title', 'Tambah Menu Sidebar Baru')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    
    <div class="flex items-center justify-between">
        <a href="{{ route('superadmin.menus.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white border border-slate-800 text-xs font-semibold transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Kelola Menu</span>
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl space-y-5">
        <div class="border-b border-slate-800 pb-4">
            <h3 class="text-sm font-bold text-white">Formulir Tambah Item Menu Sidebar</h3>
            <p class="text-xs text-slate-400 mt-0.5">Menu yang dibuat akan otomatis muncul di sidebar sesuai izin peran (roles) yang dipilih.</p>
        </div>

        @if($errors->any())
            <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs space-y-1">
                @foreach($errors->all() as $err)
                    <div>&bull; {{ $err }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('superadmin.menus.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Title -->
            <div>
                <label for="title" class="block text-xs font-semibold text-slate-300 mb-1.5">Judul Menu</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required
                    placeholder="Contoh: Analitik Sensor"
                    class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500">
            </div>

            <!-- URL & Icon -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="url" class="block text-xs font-semibold text-slate-300 mb-1.5">URL / Path</label>
                    <input type="text" id="url" name="url" value="{{ old('url') }}" required
                        placeholder="/monitoring atau https://..."
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500 font-mono">
                </div>

                <div>
                    <label for="icon" class="block text-xs font-semibold text-slate-300 mb-1.5">Ikon (cube, document-report, database, users, menu)</label>
                    <input type="text" id="icon" name="icon" value="{{ old('icon', 'cube') }}"
                        placeholder="cube / database / users"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500 font-mono">
                </div>
            </div>

            <!-- Roles Checkboxes -->
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-2">Peran yang Dapat Melihat Menu Ini:</label>
                <div class="grid grid-cols-3 gap-2">
                    <label class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-950/80 border border-slate-800 cursor-pointer hover:border-cyan-500/40 transition">
                        <input type="checkbox" name="roles[]" value="staff" {{ in_array('staff', old('roles', ['staff', 'admin', 'superadmin'])) ? 'checked' : '' }}
                            class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-cyan-600 focus:ring-0">
                        <span class="text-xs text-slate-200 font-semibold">Staff</span>
                    </label>

                    <label class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-950/80 border border-slate-800 cursor-pointer hover:border-cyan-500/40 transition">
                        <input type="checkbox" name="roles[]" value="admin" {{ in_array('admin', old('roles', ['staff', 'admin', 'superadmin'])) ? 'checked' : '' }}
                            class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-cyan-600 focus:ring-0">
                        <span class="text-xs text-slate-200 font-semibold">Admin</span>
                    </label>

                    <label class="flex items-center gap-2 p-2.5 rounded-xl bg-slate-950/80 border border-slate-800 cursor-pointer hover:border-cyan-500/40 transition">
                        <input type="checkbox" name="roles[]" value="superadmin" {{ in_array('superadmin', old('roles', ['staff', 'admin', 'superadmin'])) ? 'checked' : '' }}
                            class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-cyan-600 focus:ring-0">
                        <span class="text-xs text-slate-200 font-semibold">SuperAdmin</span>
                    </label>
                </div>
            </div>

            <!-- Sort Order -->
            <div>
                <label for="sort_order" class="block text-xs font-semibold text-slate-300 mb-1.5">Urutan Menu (Angka)</label>
                <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', 10) }}" min="1"
                    class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500 font-mono">
            </div>

            <!-- Status Active Toggle -->
            <div class="flex items-center space-x-2 pt-2">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                    class="w-4 h-4 rounded bg-slate-950 border-slate-800 text-cyan-600 focus:ring-0">
                <label for="is_active" class="text-xs font-semibold text-slate-300 cursor-pointer">
                    Tampilkan Menu di Sidebar
                </label>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-slate-800 flex justify-end space-x-3">
                <a href="{{ route('superadmin.menus.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold shadow-lg shadow-cyan-600/20 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Simpan Menu</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

