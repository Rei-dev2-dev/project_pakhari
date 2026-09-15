@extends('layouts.app')

@section('title', 'Kelola Menu Sidebar')
@section('page-title', 'Kelola Menu Sidebar')

@section('page-badge')
    <span class="text-xs px-2.5 py-1 rounded-lg bg-purple-500/10 text-purple-400 border border-purple-500/20 font-mono font-semibold">
        {{ $menus->count() }} Menu Terkonfigurasi
    </span>
@endsection

@section('content')
<div class="space-y-6">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl">
        <div>
            <h3 class="text-sm font-bold text-white">Kelola Menu & Visibilitas Sidebar</h3>
            <p class="text-xs text-slate-400 mt-0.5">SuperAdmin dapat menambahkan menu baru, menyembunyikan/menampilkan menu, dan mengatur hak akses per peran.</p>
        </div>
        <a href="{{ route('superadmin.menus.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl text-xs font-bold shadow-lg shadow-cyan-600/20 transition shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Menu Baru</span>
        </a>
    </div>

    <!-- Menus Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-950/60 border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider text-[10px]">
                        <th class="py-3.5 px-4">Urutan</th>
                        <th class="py-3.5 px-4">Judul Menu</th>
                        <th class="py-3.5 px-4">URL / Path</th>
                        <th class="py-3.5 px-4">Ikon</th>
                        <th class="py-3.5 px-4">Hak Akses Peran</th>
                        <th class="py-3.5 px-4">Status Tampil</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($menus as $menu)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3.5 px-4 font-mono text-slate-500 font-bold">
                                #{{ $menu->sort_order }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-white">
                                {{ $menu->title }}
                            </td>
                            <td class="py-3.5 px-4 font-mono text-cyan-400 text-[11px]">
                                {{ $menu->url }}
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-400">
                                <span class="px-2 py-0.5 rounded bg-slate-950 border border-slate-800 text-[10px]">
                                    {{ $menu->icon ?? 'default' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap gap-1">
                                    @if(is_array($menu->roles))
                                        @foreach($menu->roles as $r)
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-mono font-bold
                                                @if($r === 'superadmin') bg-purple-500/10 text-purple-400 border border-purple-500/30
                                                @elseif($r === 'admin') bg-sky-500/10 text-sky-400 border border-sky-500/30
                                                @else bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 @endif">
                                                {{ strtoupper($r) }}
                                            </span>
                                        @endforeach
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <form action="{{ route('superadmin.menus.toggle', $menu->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" title="Klik untuk ubah visibilitas" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold cursor-pointer transition
                                        @if($menu->is_active) bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                                        @else bg-slate-800 hover:bg-slate-700 text-slate-400 border border-slate-700 @endif">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $menu->is_active ? 'bg-emerald-400' : 'bg-slate-500' }}"></span>
                                        <span>{{ $menu->is_active ? 'Ditampilkan' : 'Disembunyikan' }}</span>
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ route('superadmin.menus.edit', $menu->id) }}" title="Edit Menu" class="p-1.5 rounded-lg bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>

                                    <form action="{{ route('superadmin.menus.destroy', $menu->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus menu ini dari sidebar?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Menu" class="p-1.5 rounded-lg bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 transition">
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
                            <td colspan="7" class="py-8 text-center text-slate-500">Belum ada menu sidebar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

