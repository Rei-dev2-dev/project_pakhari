@extends('layouts.app')

@section('title', 'Master Tangki')
@section('page-title', 'Master Data Tangki')

@section('page-badge')
    <span class="text-xs px-2.5 py-1 rounded-lg bg-sky-500/10 text-sky-400 border border-sky-500/20 font-mono font-semibold">
        {{ $tanks->total() }} Tangki Terdaftar
    </span>
@endsection

@section('content')
<div class="space-y-6">
    
    <!-- Top Bar with Add Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl">
        <div>
            <h3 class="text-sm font-bold text-white">Kelola Data & Parameter Fisik Tangki</h3>
            <p class="text-xs text-slate-400 mt-0.5">Admin dapat menambah, mengubah spesifikasi (kapasitas, tinggi, diameter, lebar), atau menghapus tangki.</p>
        </div>
        <a href="{{ route('admin.tanks.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-cyan-600/20 transition shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Tangki Baru</span>
        </a>
    </div>

    <!-- Tanks Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-950/60 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider text-[10px]">
                        <th class="py-3.5 px-4">Nama & Kode</th>
                        <th class="py-3.5 px-4">Kapasitas</th>
                        <th class="py-3.5 px-4">Panjang (P)</th>
                        <th class="py-3.5 px-4">Lebar (L)</th>
                        <th class="py-3.5 px-4">Tinggi (T)</th>
                        <th class="py-3.5 px-4">Diameter (D)</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($tanks as $tank)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-white text-sm">{{ $tank->name }}</div>
                                <div class="text-[11px] text-cyan-400 font-mono font-medium">{{ $tank->code }}</div>
                                @if($tank->description)
                                    <div class="text-[10px] text-slate-500 mt-0.5 max-w-xs truncate">{{ $tank->description }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-200">
                                {{ number_format($tank->capacity_liters, 1) }} L
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-300">
                                {{ number_format($tank->length_cm ?? 200, 1) }} cm
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-300">
                                {{ number_format($tank->width_cm, 1) }} cm
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-300">
                                {{ number_format($tank->height_cm, 1) }} cm
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-300">
                                {{ number_format($tank->diameter_cm, 1) }} cm
                            </td>
                            <td class="py-3.5 px-4">
                                @if($tank->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        <span>Aktif</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                                        <span>Nonaktif</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ route('monitoring.show', $tank->id) }}" title="Lihat 3D" class="p-1.5 rounded-lg bg-cyan-500/10 text-cyan-400 hover:bg-cyan-500/20 transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    <a href="{{ route('admin.tanks.edit', $tank->id) }}" title="Edit Tangki" class="p-1.5 rounded-lg bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>

                                    <form action="{{ route('admin.tanks.destroy', $tank->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tangki ini? Semua data telemetri terkait juga akan dihapus.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Tangki" class="p-1.5 rounded-lg bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 transition">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500">Belum ada tangki yang ditambahkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tanks->hasPages())
            <div class="px-6 py-4 border-t border-slate-800 bg-slate-950/40">
                {{ $tanks->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

