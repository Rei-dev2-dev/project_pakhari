<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Monitoring Tandon Air') - Pelindo IoT</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace']
                    }
                }
            }
        }
    </script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>

    @stack('styles')
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex antialiased selection:bg-cyan-500 selection:text-white">

    @php
        $currentRole = auth()->user()->role ?? 'staff';
        $sidebarMenus = \App\Models\SidebarMenu::where('is_active', true)
            ->whereJsonContains('roles', $currentRole)
            ->orderBy('sort_order', 'asc')
            ->get();
    @endphp

    <!-- SIDEBAR -->
    <aside class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col shrink-0 min-h-screen sticky top-0 h-screen z-30">
        <!-- Brand Header -->
        <div class="h-16 px-5 border-b border-slate-800 flex items-center justify-between">
            <a href="{{ route('monitoring.index') }}" class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-cyan-600 flex items-center justify-center text-white shadow-md shadow-cyan-600/20 font-black">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                </div>
                <div>
                    <span class="text-sm font-bold tracking-tight text-white block leading-tight">Pelindo Monitoring</span>
                    <span class="text-[10px] text-cyan-400 font-mono font-medium">IoT Tangki BBM Genset</span>
                </div>
            </a>
        </div>

        <!-- Navigation Links -->
        <div class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <div class="px-3 pb-2 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Menu Navigasi</div>

            @foreach($sidebarMenus as $menu)
                @php
                    $isActive = request()->is(ltrim($menu->url, '/') . '*') || request()->fullUrl() == url($menu->url);
                @endphp
                <a href="{{ url($menu->url) }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold transition {{ $isActive ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/30 shadow-sm' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white border border-transparent' }}">
                    @if($menu->icon == 'cube')
                        <svg class="w-4 h-4 shrink-0 {{ $isActive ? 'text-cyan-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    @elseif($menu->icon == 'document-report')
                        <svg class="w-4 h-4 shrink-0 {{ $isActive ? 'text-cyan-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    @elseif($menu->icon == 'database')
                        <svg class="w-4 h-4 shrink-0 {{ $isActive ? 'text-cyan-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                        </svg>
                    @elseif($menu->icon == 'users')
                        <svg class="w-4 h-4 shrink-0 {{ $isActive ? 'text-cyan-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    @elseif($menu->icon == 'chart-bar' || $menu->icon == 'chart')
                        <svg class="w-4 h-4 shrink-0 {{ $isActive ? 'text-cyan-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    @elseif($menu->icon == 'menu')
                        <svg class="w-4 h-4 shrink-0 {{ $isActive ? 'text-cyan-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    @else
                        <svg class="w-4 h-4 shrink-0 {{ $isActive ? 'text-cyan-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    @endif
                    <span>{{ $menu->title }}</span>
                </a>
            @endforeach
        </div>

        <!-- User Profile & Logout Bottom Card -->
        <div class="p-3 border-t border-slate-800 bg-slate-900/60">
            <div class="bg-slate-950/70 border border-slate-800/80 rounded-xl p-3 flex items-center justify-between mb-2">
                <div class="flex items-center space-x-2.5 overflow-hidden">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-cyan-600 to-sky-400 flex items-center justify-center font-bold text-white text-xs shrink-0">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="truncate">
                        <div class="text-xs font-bold text-slate-200 truncate">{{ auth()->user()->name ?? 'Pengguna' }}</div>
                        <div class="text-[10px] text-slate-400 font-mono">{{ auth()->user()->username ?? '' }}</div>
                    </div>
                </div>
                <span class="text-[10px] font-mono px-2 py-0.5 rounded-full font-bold
                    @if(auth()->user()->role === 'superadmin') bg-purple-500/20 text-purple-400 border border-purple-500/30
                    @elseif(auth()->user()->role === 'admin') bg-sky-500/20 text-sky-400 border border-sky-500/30
                    @else bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 @endif">
                    {{ strtoupper(auth()->user()->role ?? 'STAFF') }}
                </span>
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold text-rose-400 hover:bg-rose-500/10 hover:text-rose-300 border border-transparent hover:border-rose-500/20 transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Keluar (Logout)</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- MAIN CONTENT AREA -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen">
        <!-- Top Bar -->
        <header class="h-16 bg-slate-900/80 backdrop-blur border-b border-slate-800 px-6 flex items-center justify-between sticky top-0 z-20">
            <div class="flex items-center space-x-3">
                <h2 class="text-base font-bold text-white">@yield('page-title', 'Dashboard')</h2>
                @yield('page-badge')
            </div>

            <div class="flex items-center space-x-3">
                <div class="flex items-center space-x-2 px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-medium font-mono">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>ONLINE</span>
                </div>
            </div>
        </header>

        <!-- Main Body -->
        <main class="flex-1 p-6 max-w-7xl w-full mx-auto">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="mb-5 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs font-medium flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 px-4 py-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-medium flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
