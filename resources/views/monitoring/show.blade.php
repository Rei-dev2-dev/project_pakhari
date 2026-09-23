@extends('layouts.app')

@section('title', 'Monitoring 3D - ' . $tank->name)
@section('page-title', $tank->name)

@section('page-badge')
    <div class="flex items-center space-x-2">
        <span class="text-xs px-2.5 py-1 rounded-lg bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 font-mono font-semibold">
            {{ $tank->code }}
        </span>
        <span class="text-xs px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 font-mono">
            Kapasitas: {{ number_format($tank->capacity_liters, 0) }} L
        </span>
    </div>
@endsection

@push('styles')
    <!-- Three.js Import Map (Local Assets) -->
    <script type="importmap">
    {
      "imports": {
        "three": "{{ asset('vendor/three/three.module.js') }}",
        "three/addons/controls/OrbitControls.js": "{{ asset('vendor/three/controls/OrbitControls.js') }}",
        "three/addons/loaders/GLTFLoader.js": "{{ asset('vendor/three/loaders/GLTFLoader.js') }}",
        "three/addons/utils/BufferGeometryUtils.js": "{{ asset('vendor/three/utils/BufferGeometryUtils.js') }}",
        "three/addons/": "{{ asset('vendor/three') }}/"
      }
    }
    </script>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <a href="{{ route('monitoring.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white border border-slate-800 text-xs font-semibold transition w-fit">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Kembali ke Semua Tangki</span>
        </a>

        
    </div>

    <!-- Main Grid: 3D Viewport (Left 7 Cols) + Control Panel (Right 5 Cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- LEFT: 3D CANVAS VIEWPORT -->
        <div class="lg:col-span-7 flex flex-col space-y-4">
            <div class="relative bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl flex flex-col h-[520px] sm:h-[580px]">
                
                <!-- Viewport Overlay Controls -->
                <div class="absolute top-4 left-4 right-4 z-10 flex items-center justify-between pointer-events-none">
                    <div class="pointer-events-auto bg-slate-950/80 backdrop-blur-md px-3 py-1.5 rounded-xl border border-slate-800 flex items-center space-x-2 text-xs">
                        <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                        <span class="font-medium text-slate-200" id="modelLoadStatus">Model 3D</span>
                    </div>

                    <div class="pointer-events-auto flex items-center space-x-2">
                        <button id="btnResetCamera" title="Reset Kamera" class="p-2 rounded-xl bg-slate-950/80 hover:bg-slate-800 text-slate-300 hover:text-white border border-slate-800 backdrop-blur-md transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                        <button id="btnToggleLid" class="px-3 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 backdrop-blur-md text-xs font-medium transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span id="lidLabel">Tutup: Terbuka</span>
                        </button>
                        <button id="btnToggleGlass" class="px-3 py-1.5 rounded-xl bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 backdrop-blur-md text-xs font-medium transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span id="glassLabel">Transparan</span>
                        </button>
                    </div>
                </div>

                <!-- Three.js Canvas Container -->
                <div id="threejs-container" class="w-full h-full flex-1 cursor-grab active:cursor-grabbing"></div>

                <!-- Floating HUD BBM Solar Overlay -->
                <div class="absolute bottom-4 left-4 z-10 pointer-events-none">
                    <div class="bg-slate-950/85 backdrop-blur-md px-4 py-3 rounded-2xl border border-slate-800 shadow-2xl flex items-center space-x-4">
                        <div class="w-3 h-12 bg-slate-800 rounded-full overflow-hidden p-0.5 flex flex-col justify-end">
                            <div id="floatingLevelBar" class="w-full bg-gradient-to-t from-amber-500 to-yellow-400 rounded-full transition-all duration-300" style="height: {{ $latest->percentage ?? 0 }}%;"></div>
                        </div>
                        <div>
                            <div class="flex items-baseline space-x-1">
                                <span class="text-2xl font-extrabold font-mono text-white" id="floatingLiters">{{ number_format($latest->volume_liters ?? 0.0, 1) }}</span>
                                <span class="text-xs font-mono text-slate-400">/ {{ number_format($maxCapacity, 0) }} Liter</span>
                            </div>
                            <div class="flex items-center space-x-2 text-[11px] text-slate-400">
                                <span id="floatingHeight">Tinggi: {{ number_format($latest->height_cm ?? 0.0, 1) }} cm</span>
                                <span>&bull;</span>
                                <span class="text-amber-400 font-bold" id="floatingPercentage">{{ number_format($latest->percentage ?? 0.0, 1) }}%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Help -->
                <div class="absolute bottom-4 right-4 z-10 pointer-events-none text-right hidden sm:block">
                    <span class="text-[11px] text-slate-400 bg-slate-950/70 backdrop-blur px-2.5 py-1 rounded-lg border border-slate-800/80">
                        Klik & Drag untuk Rotasi &bull; Scroll untuk Zoom
                    </span>
                </div>
            </div>

            <!-- 4 Metric Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-3.5 shadow">
                    <span class="text-[11px] text-slate-400 block font-medium">Volume BBM (Solar)</span>
                    <div class="flex items-baseline gap-1 mt-1">
                        <span class="text-lg font-bold font-mono text-white" id="cardVolume">{{ number_format($latest->volume_liters ?? 0, 1) }}</span>
                        <span class="text-xs text-slate-400">L</span>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-xl p-3.5 shadow">
                    <span class="text-[11px] text-slate-400 block font-medium">Persentase</span>
                    <div class="flex items-baseline gap-1 mt-1">
                        <span class="text-lg font-bold font-mono text-amber-400" id="cardPercentage">{{ number_format($latest->percentage ?? 0, 1) }}</span>
                        <span class="text-xs text-amber-400">%</span>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-xl p-3.5 shadow">
                    <span class="text-[11px] text-slate-400 block font-medium">Ketinggian BBM (Solar)</span>
                    <div class="flex items-baseline gap-1 mt-1">
                        <span class="text-lg font-bold font-mono text-white" id="cardHeight">{{ number_format($latest->height_cm ?? 0, 1) }}</span>
                        <span class="text-xs text-slate-400">cm</span>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-xl p-3.5 shadow">
                    <span class="text-[11px] text-slate-400 block font-medium">Status Tangki</span>
                    <div class="mt-1">
                        <span id="cardStatus" class="inline-block text-[10px] font-mono font-bold px-2 py-0.5 rounded-full bg-slate-500/10 text-slate-400 border border-slate-500/30">
                            {{ strtoupper(str_replace('_', ' ', $latest->status ?? 'EMPTY')) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT: TANK SPECS & RECENT LOGS (Right 5 Cols) -->
        <div class="lg:col-span-5 flex flex-col space-y-5">
            
            <!-- Detail & Spesifikasi Tangki Card -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div class="flex items-center space-x-2">
                        <div class="w-7 h-7 rounded-lg bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center text-cyan-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider">Informasi & Spesifikasi Tangki</h3>
                    </div>
                    <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-slate-950 text-cyan-400 border border-slate-800">
                        {{ $tank->code }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-2.5 text-xs">
                    <div class="bg-slate-950/70 border border-slate-800/80 rounded-xl p-3">
                        <span class="text-[10px] text-slate-400 block font-medium">Kapasitas Maksimal</span>
                        <span class="text-sm font-bold text-cyan-400 font-mono">{{ number_format($maxCapacity, 0) }} Liter</span>
                    </div>
                    <div class="bg-slate-950/70 border border-slate-800/80 rounded-xl p-3">
                        <span class="text-[10px] text-slate-400 block font-medium">Tinggi Maksimal</span>
                        <span class="text-sm font-bold text-slate-200 font-mono">{{ number_format($maxHeight, 1) }} cm</span>
                    </div>
                    <div class="bg-slate-950/70 border border-slate-800/80 rounded-xl p-3">
                        <span class="text-[10px] text-slate-400 block font-medium">Dimensi Fisik</span>
                        <span class="text-xs font-bold text-slate-200 font-mono">{{ number_format($tank->length_cm ?? 200, 0) }} &times; {{ number_format($tank->width_cm, 0) }} cm</span>
                        <span class="text-[9px] text-slate-500 block">Panjang &times; Lebar</span>
                    </div>
                    <div class="bg-slate-950/70 border border-slate-800/80 rounded-xl p-3">
                        <span class="text-[10px] text-slate-400 block font-medium">Diameter Tangki</span>
                        <span class="text-xs font-bold text-slate-200 font-mono">{{ number_format($tank->diameter_cm, 0) }} cm</span>
                        <span class="text-[9px] text-slate-500 block">Silinder</span>
                    </div>
                </div>

                @if($tank->description)
                    <div class="p-3 rounded-xl bg-slate-950/50 border border-slate-800 text-xs text-slate-300">
                        <span class="text-[10px] text-slate-500 uppercase font-bold tracking-wider block mb-0.5">Catatan Lokasi / Penempatan</span>
                        {{ $tank->description }}
                    </div>
                @endif
            </div>

            <!-- Recent Telemetry Logs Table -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-3 flex-1 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider">Riwayat Telemetri Tangki</h3>
                        <p class="text-[11px] text-slate-400">10 data log transaksi/sensor terakhir</p>
                    </div>
                    @if(auth()->user()->hasRole(['admin', 'superadmin']))
                        <a href="{{ route('laporan.index', ['tank_id' => $tank->id]) }}" class="text-[11px] font-semibold text-cyan-400 hover:text-cyan-300 flex items-center gap-1">
                            <span>Lihat Semua</span>
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-semibold text-[10px] uppercase tracking-wider">
                                <th class="pb-2">Waktu</th>
                                <th class="pb-2">Volume</th>
                                <th class="pb-2">Tinggi</th>
                                <th class="pb-2">Username</th>
                                <th class="pb-2 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody id="logsTableBody" class="divide-y divide-slate-800/60 font-mono text-[11px]">
                            @forelse($recentLogs as $log)
                                <tr class="hover:bg-slate-800/30 transition">
                                    <td class="py-2.5 text-slate-400">{{ $log->created_at->format('H:i:s') }}</td>
                                    <td class="py-2.5 font-bold text-slate-200">{{ number_format($log->volume_liters, 1) }} L</td>
                                    <td class="py-2.5 text-slate-400">{{ number_format($log->height_cm, 1) }} cm</td>
                                    <td class="py-2.5">
                                        @php
                                            $username = $log->user?->username
                                                ?? (str_starts_with($log->device_id ?? '', 'OPERATOR-')
                                                    ? str_replace('OPERATOR-', '', $log->device_id)
                                                    : ($log->user?->name ?? 'Operator'));
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-sans font-semibold bg-cyan-500/10 text-cyan-300 border border-cyan-500/30">
                                            {{ $username }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 text-right">
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold
                                            @if($log->status === 'warning_full') bg-rose-500/10 text-rose-400
                                            @elseif($log->status === 'low') bg-amber-500/10 text-amber-400
                                            @else bg-emerald-500/10 text-emerald-400 @endif">
                                            {{ strtoupper(str_replace('_', ' ', $log->status)) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-slate-500 font-sans">Belum ada riwayat log sensor</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="module">
    import * as THREE from 'three';
    import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
    import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';

    // --- DYNAMIC TANK DATA FROM BACKEND ---
    const TANK_ID = {{ $tank->id }};
    const MAX_LITERS = {{ (float) $maxCapacity }};
    const MAX_HEIGHT_CM = {{ (float) $maxHeight }};
    const UPDATE_URL = "{{ route('monitoring.update', $tank->id) }}";

    let currentLiters = {{ (float) ($latest->volume_liters ?? 0.0) }};
    let targetLiters = currentLiters;
    let isGlass = true;
    let isLidOpen = true;
    let targetLidAngle = (2 * Math.PI / 3);
    let currentLidAngle = (2 * Math.PI / 3);
    let lidHinges = [];
    let simInterval = null;

    // --- WATER GEOMETRY CONSTANTS FOR tangki.glb ---
    const WATER_RADIUS      = 0.450;
    const WATER_LENGTH      = 2.000;
    const WATER_CENTER_Y    = 1.000;
    const WATER_MIN_Y       = WATER_CENTER_Y - WATER_RADIUS; // 0.550
    const WATER_MAX_Y       = WATER_CENTER_Y + WATER_RADIUS; // 1.450
    const WATER_FILL_RANGE  = WATER_MAX_Y - WATER_MIN_Y;    // 0.900 m
    const TANK_Z            = 0.000;

    // --- THREE.JS SCENE SETUP ---
    const container = document.getElementById('threejs-container');
    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0x0a0f1d);

    // Camera
    const camera = new THREE.PerspectiveCamera(45, container.clientWidth / container.clientHeight, 0.1, 100);
    camera.position.set(2.5, 1.8, 2.5);

    // Renderer
    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(container.clientWidth, container.clientHeight);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.15;
    renderer.localClippingEnabled = true;
    container.appendChild(renderer.domElement);

    // OrbitControls
    const controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.05;
    controls.maxDistance = 8.0;
    controls.minDistance = 0.8;
    controls.target.set(0, 1.0, 0);
    controls.update();

    // Lighting
    const ambientLight = new THREE.AmbientLight(0xffffff, 1.4);
    scene.add(ambientLight);

    const dirLight1 = new THREE.DirectionalLight(0xffffff, 2.2);
    dirLight1.position.set(4, 6, 4);
    dirLight1.castShadow = true;
    scene.add(dirLight1);

    const dirLight2 = new THREE.DirectionalLight(0xfcd34d, 1.4);
    dirLight2.position.set(-4, 4, -4);
    scene.add(dirLight2);

    const pointLightBottom = new THREE.PointLight(0xf59e0b, 1.4, 4);
    pointLightBottom.position.set(0, 0.3, 0);
    scene.add(pointLightBottom);

    // Floor Pedestal
    const gridHelper = new THREE.GridHelper(5, 20, 0x1e293b, 0x0f172a);
    gridHelper.position.y = -0.01;
    scene.add(gridHelper);

    const pedestalGeo = new THREE.BoxGeometry(2.6, 0.03, 2.6);
    const pedestalMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.7, metalness: 0.2 });
    const pedestal = new THREE.Mesh(pedestalGeo, pedestalMat);
    pedestal.position.y = -0.015;
    scene.add(pedestal);

    // --- SOLAR / DIESEL FUEL MESH (Single Cylinder + Dynamic Clipping Plane) ---
    const waterClipPlane = new THREE.Plane(new THREE.Vector3(0, -1, 0), WATER_MIN_Y);
    const waterMaterial = new THREE.MeshPhysicalMaterial({
        color: 0xf59e0b,
        emissive: 0xd97706,
        emissiveIntensity: 0.25,
        roughness: 0.08,
        metalness: 0.05,
        transmission: 0.60,
        ior: 1.45,
        transparent: true,
        opacity: 0.88,
        clippingPlanes: [waterClipPlane],
        clipShadows: true,
        side: THREE.DoubleSide,
        depthWrite: false
    });

    const waterCylinderGeo = new THREE.CylinderGeometry(WATER_RADIUS, WATER_RADIUS, WATER_LENGTH, 48, 1, false);
    waterCylinderGeo.rotateZ(Math.PI / 2);

    const waterGroup = new THREE.Group();
    const waterMesh = new THREE.Mesh(waterCylinderGeo, waterMaterial);
    waterMesh.position.set(0, WATER_CENTER_Y, TANK_Z);
    waterGroup.add(waterMesh);

    // Top Flat Surface Cap (Solar Meniscus)
    const surfaceMaterial = new THREE.MeshPhysicalMaterial({
        color: 0xfde047,
        emissive: 0xf59e0b,
        emissiveIntensity: 0.30,
        roughness: 0.05,
        metalness: 0.08,
        transparent: true,
        opacity: 0.95,
        side: THREE.DoubleSide,
        depthWrite: false
    });

    const surfaceGeo = new THREE.PlaneGeometry(WATER_LENGTH, 1.0, 1, 1);
    surfaceGeo.rotateX(-Math.PI / 2);

    const surfaceMesh = new THREE.Mesh(surfaceGeo, surfaceMaterial);
    surfaceMesh.position.set(0, WATER_MIN_Y, TANK_Z);
    waterGroup.add(surfaceMesh);
    scene.add(waterGroup);

    // Inflow stream (Solar filling stream)
    const inflowGeo = new THREE.CylinderGeometry(0.016, 0.016, 0.85, 16);
    inflowGeo.translate(0, -0.425, 0);
    const inflowMat = new THREE.MeshBasicMaterial({ color: 0xfde047, transparent: true, opacity: 0.0 });
    const inflow = new THREE.Mesh(inflowGeo, inflowMat);
    inflow.position.set(-0.160, 1.53, TANK_Z);
    scene.add(inflow);

    // --- VERTICAL CENTIMETER (CM) RULER GAUGE (RED, CENTERED, HIGH-RES CANVAS) ---
    function createRulerTexture(maxHeightCm) {
        const canvas = document.createElement('canvas');
        canvas.width = 800;
        canvas.height = 2048;
        const ctx = canvas.getContext('2d');

        // Completely transparent background
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        const padTop = 90;
        const padBottom = 90;
        const drawHeight = canvas.height - padTop - padBottom;
        const lineX = 400; // EXACT CENTER of the 800px width!

        // Center vertical scale axis line (BRIGHT RED)
        ctx.strokeStyle = '#ef4444';
        ctx.lineWidth = 10;
        ctx.beginPath();
        ctx.moveTo(lineX, padTop);
        ctx.lineTo(lineX, canvas.height - padBottom);
        ctx.stroke();

        // Dynamic tick calculation depending on maxHeightCm
        const stepMajor = maxHeightCm <= 30 ? 5 : (maxHeightCm <= 80 ? 10 : 20);
        const stepMinor = stepMajor === 5 ? 1 : (stepMajor === 10 ? 2 : 5);

        for (let cm = 0; cm <= maxHeightCm; cm += stepMinor) {
            const ratio = cm / maxHeightCm;
            const y = (canvas.height - padBottom) - (ratio * drawHeight);
            const isMajor = (cm % stepMajor === 0) || (Math.abs(cm - maxHeightCm) < 0.01);
            const isMedium = (!isMajor) && (cm % (stepMajor / 2) === 0);

            ctx.beginPath();
            if (isMajor) {
                // Major tick line (Bright Red)
                ctx.strokeStyle = '#ef4444';
                ctx.lineWidth = 9;
                ctx.moveTo(lineX - 50, y);
                ctx.lineTo(lineX + 50, y);
                ctx.stroke();

                // Extra Large Bold Numbers with Dark Outline
                const labelText = `${cm} cm`;
                ctx.font = '900 66px "JetBrains Mono", monospace';
                ctx.textAlign = 'left';
                ctx.textBaseline = 'middle';
                
                // Dark stroke for extreme crispness
                ctx.strokeStyle = '#020617';
                ctx.lineWidth = 12;
                ctx.lineJoin = 'round';
                ctx.miterLimit = 2;
                ctx.strokeText(labelText, lineX + 65, y);

                // Bright text fill
                ctx.fillStyle = (cm === 0 || Math.abs(cm - maxHeightCm) < 0.01) ? '#fde047' : '#ffffff';
                ctx.fillText(labelText, lineX + 65, y);
            } else if (isMedium) {
                // Medium tick line (Red accent)
                ctx.strokeStyle = '#f87171';
                ctx.lineWidth = 5;
                ctx.moveTo(lineX - 28, y);
                ctx.lineTo(lineX + 28, y);
                ctx.stroke();
            } else {
                // Minor tick line
                ctx.strokeStyle = 'rgba(239, 68, 68, 0.70)';
                ctx.lineWidth = 3;
                ctx.moveTo(lineX - 15, y);
                ctx.lineTo(lineX + 15, y);
                ctx.stroke();
            }
        }

        const texture = new THREE.CanvasTexture(canvas);
        texture.minFilter = THREE.LinearFilter;
        texture.magFilter = THREE.LinearFilter;
        return texture;
    }

    const rulerTexture = createRulerTexture(MAX_HEIGHT_CM);
    const rulerMaterial = new THREE.MeshBasicMaterial({
        map: rulerTexture,
        transparent: true,
        opacity: 0.98,
        side: THREE.DoubleSide,
        depthWrite: false
    });

    const rulerGeo = new THREE.PlaneGeometry(0.42, WATER_FILL_RANGE);

    // 1. Front circular cap ruler (facing outward -X, exactly centered on circle Z=0)
    const frontRuler = new THREE.Mesh(rulerGeo, rulerMaterial);
    frontRuler.position.set(-1.008, WATER_CENTER_Y, 0.000);
    frontRuler.rotation.y = -Math.PI / 2;
    scene.add(frontRuler);

    // 2. Back circular cap ruler (facing outward +X, exactly centered on circle Z=0)
    const backRuler = new THREE.Mesh(rulerGeo, rulerMaterial);
    backRuler.position.set(1.008, WATER_CENTER_Y, 0.000);
    backRuler.rotation.y = Math.PI / 2;
    scene.add(backRuler);

    // --- DYNAMIC CURRENT LEVEL INDICATOR (DEPAN & BELAKANG, TRANSPARENT, NO BOX) ---
    const levelBadgeCanvas = document.createElement('canvas');
    levelBadgeCanvas.width = 640;
    levelBadgeCanvas.height = 120;
    const levelBadgeCtx = levelBadgeCanvas.getContext('2d');
    const levelBadgeTexture = new THREE.CanvasTexture(levelBadgeCanvas);
    levelBadgeTexture.minFilter = THREE.LinearFilter;
    levelBadgeTexture.magFilter = THREE.LinearFilter;

    function updateLevelBadge(heightCm, pct) {
        // Completely transparent background (no background box)
        levelBadgeCtx.clearRect(0, 0, levelBadgeCanvas.width, levelBadgeCanvas.height);

        // Text format placed on the left side: "[height] cm ([pct]%) —"
        const labelText = `${heightCm} cm (${pct}%) —`;
        levelBadgeCtx.font = '900 52px "JetBrains Mono", monospace';
        levelBadgeCtx.textAlign = 'right';
        levelBadgeCtx.textBaseline = 'middle';
        
        // High contrast dark stroke outline for extreme clarity
        levelBadgeCtx.strokeStyle = '#020617';
        levelBadgeCtx.lineWidth = 12;
        levelBadgeCtx.lineJoin = 'round';
        levelBadgeCtx.miterLimit = 2;
        levelBadgeCtx.strokeText(labelText, 620, 60);

        // Bright solar yellow text fill
        levelBadgeCtx.fillStyle = '#fde047';
        levelBadgeCtx.fillText(labelText, 620, 60);

        levelBadgeTexture.needsUpdate = true;
    }

    const levelBadgeMat = new THREE.MeshBasicMaterial({
        map: levelBadgeTexture,
        transparent: true,
        opacity: 0.98,
        side: THREE.DoubleSide,
        depthWrite: false
    });

    // 1. Front Badge (facing outward -X, placed on the left side -Z)
    const levelBadgePlaneFront = new THREE.Mesh(new THREE.PlaneGeometry(0.38, 0.072), levelBadgeMat);
    levelBadgePlaneFront.position.set(-1.011, WATER_MIN_Y, -0.19);
    levelBadgePlaneFront.rotation.y = -Math.PI / 2;
    scene.add(levelBadgePlaneFront);

    // 2. Back Badge (facing outward +X, placed on the left side when viewed from back +Z)
    const levelBadgePlaneBack = new THREE.Mesh(new THREE.PlaneGeometry(0.38, 0.072), levelBadgeMat);
    levelBadgePlaneBack.position.set(1.011, WATER_MIN_Y, 0.19);
    levelBadgePlaneBack.rotation.y = Math.PI / 2;
    scene.add(levelBadgePlaneBack);

    // --- MATERIALS FOR TANK GLASS/SOLID ---
    const glassTankMaterial = new THREE.MeshPhysicalMaterial({
        color: 0x94a3b8,
        roughness: 0.12,
        metalness: 0.05,
        transmission: 0.85,
        transparent: true,
        opacity: 0.38,
        ior: 1.45,
        depthWrite: false,
        side: THREE.DoubleSide
    });

    const solidTankMaterial = new THREE.MeshStandardMaterial({
        color: 0x0284c7,
        roughness: 0.35,
        metalness: 0.2
    });

    let tankGroup = null;
    let tankBodyMeshes = [];

    // --- LOAD 3D MODEL (assets/tangki.glb) ---
    const gltfLoader = new GLTFLoader();
    gltfLoader.load(
        "{{ asset('assets/tangki.glb') }}",
        (gltf) => {
            tankGroup = gltf.scene;
            // Center tank at origin
            tankGroup.position.set(-1.0, 0, -0.9);

            tankBodyMeshes = [];
            lidHinges = [];
            tankGroup.traverse((child) => {
                if (child.isMesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                    if (child.name.includes('Body') || child.name.includes('TankKiri_Body')) {
                        tankBodyMeshes.push(child);
                        if (isGlass) {
                            child.material = glassTankMaterial;
                        }
                    }
                }

                if (child.name && child.name.includes('Manhole_Lid_Hinge')) {
                    lidHinges.push(child);
                }
            });

            scene.add(tankGroup);
            document.getElementById('modelLoadStatus').textContent = 'Model 3D Aktif';
            updateWaterVisual(currentLiters);
        },
        () => {},
        (err) => {
            console.warn('Error loading GLTF model:', err);
            document.getElementById('modelLoadStatus').textContent = 'Mode Simulasi';
        }
    );

    // --- ANIMATION LOOP ---
    const clock = new THREE.Clock();

    function animate() {
        requestAnimationFrame(animate);

        const delta = clock.getDelta();
        const lerpSpeed = 5.0 * delta;
        currentLiters += (targetLiters - currentLiters) * lerpSpeed;

        updateWaterVisual(currentLiters);

        // Smooth lid lerp
        const lidLerp = 6.0 * delta;
        currentLidAngle += (targetLidAngle - currentLidAngle) * lidLerp;
        lidHinges.forEach(h => {
            h.rotation.x = currentLidAngle;
        });

        // Inflow animation
        if (targetLiters > currentLiters + 0.3) {
            inflowMat.opacity = THREE.MathUtils.lerp(inflowMat.opacity, 0.75, 0.1);
            const fillRatio = Math.max(0, Math.min(1, currentLiters / MAX_LITERS));
            const currentHeight = WATER_MIN_Y + fillRatio * WATER_FILL_RANGE;
            const streamLength = Math.max(0.04, 1.53 - currentHeight);
            inflow.scale.set(1, streamLength / 0.85, 1);
        } else {
            inflowMat.opacity = THREE.MathUtils.lerp(inflowMat.opacity, 0.0, 0.1);
        }

        controls.update();
        renderer.render(scene, camera);
    }
    animate();

    // --- WATER / SOLAR VISUAL UPDATE ---
    function updateWaterVisual(liters) {
        const clamped = Math.max(0, Math.min(MAX_LITERS, liters));
        const ratio   = clamped / MAX_LITERS;
        const currentHeight = WATER_MIN_Y + ratio * WATER_FILL_RANGE;

        waterClipPlane.constant = currentHeight;

        // Update live position of dynamic level text badges (front & back)
        levelBadgePlaneFront.position.y = currentHeight;
        levelBadgePlaneBack.position.y = currentHeight;

        const estHeightCm = (ratio * MAX_HEIGHT_CM).toFixed(1);
        const estPct = (ratio * 100).toFixed(1);
        updateLevelBadge(estHeightCm, estPct);

        if (clamped <= 0.05) {
            waterMesh.visible = false;
            surfaceMesh.visible = false;
            levelBadgePlaneFront.visible = false;
            levelBadgePlaneBack.visible = false;
        } else {
            waterMesh.visible = true;
            surfaceMesh.visible = true;
            levelBadgePlaneFront.visible = true;
            levelBadgePlaneBack.visible = true;
            surfaceMesh.position.y = currentHeight;

            const dy = currentHeight - WATER_CENTER_Y;
            const rSq = Math.max(0, WATER_RADIUS * WATER_RADIUS - dy * dy);
            const surfaceWidthZ = 2 * Math.sqrt(rSq);
            const scaledWidthZ = Math.max(0.005, surfaceWidthZ - 0.006);

            surfaceMesh.scale.set(1.0, 1.0, scaledWidthZ);
        }

        // Solar / Diesel color adjustments based on tank capacity states
        if (clamped >= MAX_LITERS * 0.95) {
            waterMaterial.color.setHex(0xf43f5e);
            waterMaterial.emissive.setHex(0xbe123c);
            surfaceMaterial.color.setHex(0xfb7185);
            surfaceMaterial.emissive.setHex(0xbe123c);
        } else if (clamped <= MAX_LITERS * 0.20) {
            waterMaterial.color.setHex(0xf97316);
            waterMaterial.emissive.setHex(0xc2410c);
            surfaceMaterial.color.setHex(0xfb923c);
            surfaceMaterial.emissive.setHex(0xc2410c);
        } else {
            waterMaterial.color.setHex(0xf59e0b);
            waterMaterial.emissive.setHex(0xd97706);
            surfaceMaterial.color.setHex(0xfde047);
            surfaceMaterial.emissive.setHex(0xf59e0b);
        }

        // Floating HUD updates
        document.getElementById('floatingLiters').textContent = clamped.toFixed(1);
        document.getElementById('floatingHeight').textContent = `Tinggi: ${estHeightCm} cm`;
        document.getElementById('floatingPercentage').textContent = `${estPct}%`;
        document.getElementById('floatingLevelBar').style.height = `${estPct}%`;
    }

    // --- CONTROLS & LISTENERS ---
    document.getElementById('btnResetCamera').addEventListener('click', () => {
        camera.position.set(2.5, 1.8, 2.5);
        controls.target.set(0, 1.0, 0);
        controls.update();
    });

    const btnToggleLid = document.getElementById('btnToggleLid');
    const lidLabel = document.getElementById('lidLabel');

    btnToggleLid.addEventListener('click', () => {
        isLidOpen = !isLidOpen;
        targetLidAngle = isLidOpen ? (2 * Math.PI / 3) : 0;
        lidLabel.textContent = isLidOpen ? 'Tutup: Terbuka' : 'Tutup: Tertutup';
        btnToggleLid.className = isLidOpen
            ? 'px-3 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 backdrop-blur-md text-xs font-medium transition flex items-center gap-1.5'
            : 'px-3 py-1.5 rounded-xl bg-slate-950/80 hover:bg-slate-800 text-slate-300 hover:text-white border border-slate-800 backdrop-blur-md text-xs font-medium transition flex items-center gap-1.5';
    });

    document.getElementById('btnToggleGlass').addEventListener('click', () => {
        if (!tankBodyMeshes || tankBodyMeshes.length === 0) return;
        isGlass = !isGlass;
        tankBodyMeshes.forEach(mesh => {
            mesh.material = isGlass ? glassTankMaterial : solidTankMaterial;
        });
        document.getElementById('glassLabel').textContent = isGlass ? 'Transparan' : 'Solid';
    });

    window.addEventListener('resize', () => {
        if (!container) return;
        camera.aspect = container.clientWidth / container.clientHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(container.clientWidth, container.clientHeight);
    });
</script>
@endpush

