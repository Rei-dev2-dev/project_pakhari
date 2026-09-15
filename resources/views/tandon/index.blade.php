<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Monitoring & Pengujian IoT Tandon Air 3D</title>

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
        .canvas-container { touch-action: none; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>

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
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col antialiased selection:bg-cyan-500 selection:text-white">

    <!-- HEADER -->
    <header class="border-b border-slate-800 bg-slate-900/90 backdrop-blur sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl bg-cyan-600 flex items-center justify-center text-white shadow-md shadow-cyan-600/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-base font-bold tracking-tight text-white">Monitoring Tandon Air</h1>
                    <p class="text-xs text-slate-400">Kapasitas Maksimal 100 Liter</p>
                </div>
            </div>

            <div class="flex items-center space-x-2 px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-medium font-mono">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>ONLINE</span>
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- LEFT COLUMN: 3D VIEWPORT & METRIC CARDS (7 Cols) -->
        <div class="lg:col-span-7 flex flex-col space-y-4">
            
            <!-- 3D Canvas Container -->
            <div class="relative bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-xl flex flex-col h-[520px] sm:h-[580px]">
                
                <!-- Viewport Top Controls -->
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
                <div id="threejs-container" class="w-full h-full canvas-container flex-1 cursor-grab active:cursor-grabbing"></div>

                <!-- Floating Water Level Overlay -->
                <div class="absolute bottom-4 left-4 z-10 pointer-events-none">
                    <div class="bg-slate-950/80 backdrop-blur-md border border-slate-800 px-4 py-3 rounded-xl shadow-lg space-y-1">
                        <div class="text-[11px] font-mono text-slate-400">Volume Air</div>
                        <div class="flex items-baseline space-x-2">
                            <span class="text-2xl font-bold font-mono text-cyan-400" id="floatingLiters">
                                {{ number_format($latest->volume_liters, 1) }}
                            </span>
                            <span class="text-sm font-semibold text-slate-300">L</span>
                            <span class="text-xs font-mono text-slate-500">/ 100 L</span>
                        </div>
                        <div class="flex items-center space-x-2 pt-0.5 text-xs text-slate-300">
                            <span id="floatingHeight">{{ number_format($latest->height_cm, 1) }} cm</span>
                            <span class="text-slate-600">&bull;</span>
                            <span id="floatingPercentage">{{ number_format($latest->percentage, 1) }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Instruction Hint -->
                <div class="absolute bottom-4 right-4 z-10 pointer-events-none hidden sm:block">
                    <div class="bg-slate-950/70 backdrop-blur-sm border border-slate-800 px-3 py-1.5 rounded-lg text-xs text-slate-400 font-mono">
                        Rotasi: Klik Kiri &bull; Zoom: Scroll &bull; Geser: Klik Kanan
                    </div>
                </div>

                <!-- Loading Spinner -->
                <div id="loaderOverlay" class="absolute inset-0 bg-slate-950 flex flex-col items-center justify-center z-20 transition-opacity duration-300">
                    <div class="w-10 h-10 border-3 border-cyan-500/20 border-t-cyan-500 rounded-full animate-spin"></div>
                    <p class="mt-3 text-xs text-slate-400">Memuat tampilan 3D...</p>
                </div>
            </div>

            <!-- Metric Cards Row -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-3.5">
                    <div class="text-xs text-slate-400">Volume</div>
                    <div class="text-xl font-bold font-mono text-white mt-1">
                        <span id="cardVolume">{{ number_format($latest->volume_liters, 1) }}</span>
                        <span class="text-xs font-normal text-slate-400">L</span>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-xl p-3.5">
                    <div class="text-xs text-slate-400">Persentase</div>
                    <div class="text-xl font-bold font-mono text-cyan-400 mt-1">
                        <span id="cardPercentage">{{ number_format($latest->percentage, 1) }}</span>
                        <span class="text-xs font-normal text-slate-400">%</span>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-xl p-3.5">
                    <div class="text-xs text-slate-400">Tinggi Air</div>
                    <div class="text-xl font-bold font-mono text-blue-400 mt-1">
                        <span id="cardHeight">{{ number_format($latest->height_cm, 1) }}</span>
                        <span class="text-xs font-normal text-slate-400">cm</span>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-xl p-3.5">
                    <div class="text-xs text-slate-400">Status</div>
                    <div class="mt-1">
                        <span id="statusBadge" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold font-mono uppercase bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            {{ $latest->status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: CONTROL PANEL (5 Cols) -->
        <div class="lg:col-span-5 flex flex-col space-y-4">
            
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl space-y-5">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h2 class="text-sm font-bold text-slate-200">Kontrol Volume Air</h2>
                    <span class="text-xs font-mono text-slate-400">0 - 100 Liter</span>
                </div>

                <!-- Input Volume -->
                <form id="levelForm" class="space-y-2">
                    <label for="numberInput" class="block text-xs font-medium text-slate-300">
                        Input Nilai Volume (Liter)
                    </label>
                    <div class="flex items-center space-x-2">
                        <div class="relative flex-1">
                            <input 
                                type="number" 
                                id="numberInput" 
                                min="0" 
                                max="100" 
                                step="0.5" 
                                value="{{ $latest->volume_liters }}"
                                class="w-full bg-slate-950 border border-slate-700 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500 rounded-xl px-4 py-2 text-base font-mono font-bold text-white placeholder-slate-500 outline-none transition"
                                placeholder="0 - 100"
                            >
                            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-mono text-slate-400">Liter</span>
                        </div>
                        <button 
                            type="submit" 
                            id="btnApply" 
                            class="px-5 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-xs rounded-xl transition shadow-md shadow-cyan-600/20 active:scale-95"
                        >
                            Terapkan
                        </button>
                    </div>
                </form>

                <!-- Slider -->
                <div class="space-y-2 pt-1">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-300 font-medium">Slider Volume</span>
                        <span class="font-mono text-cyan-400 font-bold" id="sliderValueDisplay">{{ $latest->volume_liters }} L</span>
                    </div>
                    <input 
                        type="range" 
                        id="levelSlider" 
                        min="0" 
                        max="100" 
                        step="0.5" 
                        value="{{ $latest->volume_liters }}" 
                        class="w-full h-2 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-cyan-400 hover:accent-cyan-300 transition"
                    >
                    <div class="flex justify-between text-[11px] font-mono text-slate-500 px-0.5">
                        <span>0 L</span>
                        <span>25 L</span>
                        <span>50 L</span>
                        <span>75 L</span>
                        <span>100 L</span>
                    </div>
                </div>

                <!-- Quick Presets -->
                <div class="space-y-2 pt-1">
                    <span class="text-xs font-medium text-slate-400 block">Preset Cepat:</span>
                    <div class="grid grid-cols-5 gap-1.5">
                        <button type="button" data-preset="0" class="btnPreset py-1.5 px-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg text-xs font-mono font-medium transition border border-slate-700 active:scale-95 text-center">
                            0 L
                        </button>
                        <button type="button" data-preset="25" class="btnPreset py-1.5 px-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg text-xs font-mono font-medium transition border border-slate-700 active:scale-95 text-center">
                            25 L
                        </button>
                        <button type="button" data-preset="50" class="btnPreset py-1.5 px-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg text-xs font-mono font-medium transition border border-slate-700 active:scale-95 text-center">
                            50 L
                        </button>
                        <button type="button" data-preset="75" class="btnPreset py-1.5 px-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg text-xs font-mono font-medium transition border border-slate-700 active:scale-95 text-center">
                            75 L
                        </button>
                        <button type="button" data-preset="100" class="btnPreset py-1.5 px-2 bg-cyan-950 hover:bg-cyan-900 text-cyan-300 hover:text-white rounded-lg text-xs font-mono font-semibold transition border border-cyan-700 active:scale-95 text-center">
                            100 L
                        </button>
                    </div>
                </div>

                <!-- Pump / Drain Simulation Buttons -->
                <div class="pt-3 border-t border-slate-800 space-y-2">
                    <span class="text-xs font-medium text-slate-400 block">Simulasi Aliran Air:</span>
                    <div class="grid grid-cols-2 gap-2">
                        <button 
                            type="button" 
                            id="btnSimulateFill" 
                            class="py-2.5 px-3 bg-emerald-950/60 hover:bg-emerald-900/80 border border-emerald-500/30 text-emerald-400 hover:text-emerald-300 text-xs font-semibold rounded-xl transition flex items-center justify-center gap-1.5 active:scale-95"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                            <span>Isi Air</span>
                        </button>
                        <button 
                            type="button" 
                            id="btnSimulateDrain" 
                            class="py-2.5 px-3 bg-amber-950/60 hover:bg-amber-900/80 border border-amber-500/30 text-amber-400 hover:text-amber-300 text-xs font-semibold rounded-xl transition flex items-center justify-center gap-1.5 active:scale-95"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                            <span>Kuras Air</span>
                        </button>
                    </div>
                    <button 
                        type="button" 
                        id="btnStopSimulation" 
                        class="w-full hidden py-2 px-3 bg-rose-950/60 border border-rose-500/30 text-rose-400 text-xs font-semibold rounded-xl transition hover:bg-rose-900/70"
                    >
                        Hentikan Simulasi
                    </button>
                </div>
            </div>

        </div>
    </main>

    <!-- FOOTER -->
    <footer class="border-t border-slate-900 bg-slate-950 py-4 mt-auto">
        <div class="max-w-7xl mx-auto px-4 text-center text-xs text-slate-500">
            Monitoring Tandon Air &copy; {{ date('Y') }}
        </div>
    </footer>

    <!-- JAVASCRIPT & THREE.JS LOGIC -->
    <script type="module">
        import * as THREE from 'three';
        import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
        import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';

        // --- STATE MANAGEMENT ---
        const MAX_LITERS = 100.0;
        const MAX_HEIGHT_CM = 70.0;
        let currentLiters = {{ $latest->volume_liters ?? 35.0 }};
        let targetLiters = currentLiters;
        let isGlass = true;
        let isLidOpen = true;
        let targetLidAngle = (2 * Math.PI / 3); // 120 degrees open
        let currentLidAngle = (2 * Math.PI / 3);
        let lidHinges = [];
        let simInterval = null;

        // --- TANK HORIZONTAL CYLINDER DIMENSIONS (tangki.glb) ---
        const WATER_RADIUS      = 0.450;  // inner radius of tank cylinder
        const WATER_LENGTH      = 2.000;  // inner length of tank cylinder along X
        const WATER_CENTER_Y    = 1.000;  // center Y of horizontal cylinder
        const WATER_MIN_Y       = WATER_CENTER_Y - WATER_RADIUS; // 0.550
        const WATER_MAX_Y       = WATER_CENTER_Y + WATER_RADIUS; // 1.450
        const WATER_FILL_RANGE  = WATER_MAX_Y - WATER_MIN_Y;    // 0.900 m
        const TANK_Z            = 0.000;

        // --- THREE.JS SCENE SETUP ---
        const container = document.getElementById('threejs-container');
        const scene = new THREE.Scene();
        scene.background = new THREE.Color(0x0a0f1d);

        // Camera
        const camera = new THREE.PerspectiveCamera(
            45, 
            container.clientWidth / container.clientHeight, 
            0.1, 
            100
        );
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

        // --- LIGHTING ---
        const ambientLight = new THREE.AmbientLight(0xffffff, 1.4);
        scene.add(ambientLight);

        const dirLight1 = new THREE.DirectionalLight(0xffffff, 2.2);
        dirLight1.position.set(4, 6, 4);
        dirLight1.castShadow = true;
        dirLight1.shadow.mapSize.width = 1024;
        dirLight1.shadow.mapSize.height = 1024;
        scene.add(dirLight1);

        const dirLight2 = new THREE.DirectionalLight(0x38bdf8, 1.4);
        dirLight2.position.set(-4, 4, -4);
        scene.add(dirLight2);

        const pointLightBottom = new THREE.PointLight(0x0ea5e9, 1.2, 4);
        pointLightBottom.position.set(0, 0.3, 0);
        scene.add(pointLightBottom);

        // Floor Grid & Pedestal
        const gridHelper = new THREE.GridHelper(5, 20, 0x1e293b, 0x0f172a);
        gridHelper.position.y = -0.01;
        scene.add(gridHelper);

        const pedestalGeo = new THREE.BoxGeometry(2.6, 0.03, 2.6);
        const pedestalMat = new THREE.MeshStandardMaterial({
            color: 0x1e293b,
            roughness: 0.7,
            metalness: 0.2
        });
        const pedestal = new THREE.Mesh(pedestalGeo, pedestalMat);
        pedestal.position.y = -0.015;
        pedestal.receiveShadow = true;
        scene.add(pedestal);

        // --- WATER MESH (Single Horizontal Cylinder + Dynamic Height Clipping Plane) ---
        const waterClipPlane = new THREE.Plane(new THREE.Vector3(0, -1, 0), WATER_MIN_Y);

        const waterMaterial = new THREE.MeshPhysicalMaterial({
            color: 0x0284c7,
            emissive: 0x0369a1,
            emissiveIntensity: 0.2,
            roughness: 0.08,
            metalness: 0.05,
            transmission: 0.55,
            ior: 1.333,
            transparent: true,
            opacity: 0.85,
            clippingPlanes: [waterClipPlane],
            clipShadows: true,
            side: THREE.DoubleSide,
            depthWrite: false
        });

        // Horizontal cylinder geometry lying along X-axis
        const waterCylinderGeo = new THREE.CylinderGeometry(WATER_RADIUS, WATER_RADIUS, WATER_LENGTH, 48, 1, false);
        waterCylinderGeo.rotateZ(Math.PI / 2);

        const waterGroup = new THREE.Group();

        // Single Tank Water Body
        const waterMesh = new THREE.Mesh(waterCylinderGeo, waterMaterial);
        waterMesh.position.set(0, WATER_CENTER_Y, TANK_Z);
        waterGroup.add(waterMesh);

        // Top Flat Surface Cap (Plane aligned to X-Z plane, dynamically scaled in Z)
        const surfaceMaterial = new THREE.MeshPhysicalMaterial({
            color: 0x38bdf8,
            emissive: 0x0284c7,
            emissiveIntensity: 0.25,
            roughness: 0.05,
            metalness: 0.1,
            transparent: true,
            opacity: 0.92,
            side: THREE.DoubleSide,
            depthWrite: false
        });

        const surfaceGeo = new THREE.PlaneGeometry(WATER_LENGTH, 1.0, 1, 1);
        surfaceGeo.rotateX(-Math.PI / 2);

        const surfaceMesh = new THREE.Mesh(surfaceGeo, surfaceMaterial);
        surfaceMesh.position.set(0, WATER_MIN_Y, TANK_Z);
        waterGroup.add(surfaceMesh);

        scene.add(waterGroup);

        // Inflow water stream pouring into top manhole
        const inflowGeo = new THREE.CylinderGeometry(0.016, 0.016, 0.85, 16);
        inflowGeo.translate(0, -0.425, 0);
        const inflowMat = new THREE.MeshBasicMaterial({
            color: 0x7dd3fc,
            transparent: true,
            opacity: 0.0
        });

        const inflow = new THREE.Mesh(inflowGeo, inflowMat);
        inflow.position.set(-0.160, 1.53, TANK_Z);
        scene.add(inflow);

        // --- PROCEDURAL FALLBACK / PLACEHOLDER (SINGLE HORIZONTAL CYLINDER) ---
        const placeholderTankGroup = new THREE.Group();
        const pBodyGeo = new THREE.CylinderGeometry(WATER_RADIUS + 0.008, WATER_RADIUS + 0.008, WATER_LENGTH + 0.02, 36, 1, true);
        pBodyGeo.rotateZ(Math.PI / 2);

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

        const placeholderMesh = new THREE.Mesh(pBodyGeo, glassTankMaterial);
        placeholderMesh.position.set(0, WATER_CENTER_Y, TANK_Z);
        placeholderTankGroup.add(placeholderMesh);

        // Stand legs for placeholder
        const standMat = new THREE.MeshStandardMaterial({ color: 0x0f766e, metalness: 0.6, roughness: 0.3 });
        [-0.7, 0.7].forEach(x => {
            const legGeo = new THREE.BoxGeometry(0.04, 0.55, 0.8);
            const legMesh = new THREE.Mesh(legGeo, standMat);
            legMesh.position.set(x, 0.275, TANK_Z);
            placeholderTankGroup.add(legMesh);
        });

        scene.add(placeholderTankGroup);

        // Function to dismiss loader overlay quickly
        const dismissLoader = () => {
            const overlay = document.getElementById('loaderOverlay');
            if (overlay && overlay.style.display !== 'none') {
                overlay.style.transition = 'opacity 0.3s ease';
                overlay.style.opacity = '0';
                setTimeout(() => { overlay.style.display = 'none'; }, 300);
            }
        };

        // Instant dismiss timer as soon as WebGL is initialized
        setTimeout(dismissLoader, 350);

        // --- TANK MODEL LOADING ---
        let tankGroup = null;
        let tankBodyMeshes = [placeholderMesh];

        const gltfLoader = new GLTFLoader();
        gltfLoader.load(
            "{{ asset('assets/tangki.glb') }}",
            (gltf) => {
                tankGroup = gltf.scene;
                // Center the tank at origin (model spans X: [0.0, 2.0])
                tankGroup.position.set(-1.0, 0, -0.9);

                tankBodyMeshes = [];
                lidHinges = [];
                tankGroup.traverse((child) => {
                    if (child.isMesh) {
                        child.castShadow = true;
                        child.receiveShadow = true;

                        if (child.name.includes('Body') || child.name.includes('TankKiri_Body') || child.name.includes('TankKanan_Body')) {
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

                // Smoothly replace placeholder with 3D model
                scene.remove(placeholderTankGroup);
                scene.add(tankGroup);

                dismissLoader();
                document.getElementById('modelLoadStatus').textContent = 'Model 3D';
                updateWaterVisual(currentLiters);
            },
            () => {},
            (error) => {
                console.warn('Fallback procedural model active:', error);
                dismissLoader();
                document.getElementById('modelLoadStatus').textContent = 'Model 3D';
            }
        );

        // --- ANIMATION & RENDER LOOP ---
        const clock = new THREE.Clock();

        function animate() {
            requestAnimationFrame(animate);

            const delta = clock.getDelta();
            const elapsedTime = clock.getElapsedTime();

            const lerpSpeed = 5.0 * delta;
            currentLiters += (targetLiters - currentLiters) * lerpSpeed;

            updateWaterVisual(currentLiters);

            // Smooth lid open/close animation
            const lidLerp = 6.0 * delta;
            currentLidAngle += (targetLidAngle - currentLidAngle) * lidLerp;
            lidHinges.forEach(h => {
                h.rotation.x = currentLidAngle;
            });

            // Inflow animation when filling
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

        // --- WATER VISUAL SCALING (Clipping Plane + Dynamic Width Surface Cap) ---
        function updateWaterVisual(liters) {
            const clamped = Math.max(0, Math.min(MAX_LITERS, liters));
            const ratio   = clamped / MAX_LITERS;

            // Current water height along Y
            const currentHeight = WATER_MIN_Y + ratio * WATER_FILL_RANGE;

            // Plane clipping: cut off any part of the water cylinder above currentHeight
            waterClipPlane.constant = currentHeight;

            if (clamped <= 0.05) {
                waterMesh.visible = false;
                surfaceMesh.visible = false;
            } else {
                waterMesh.visible = true;
                surfaceMesh.visible = true;

                surfaceMesh.position.y = currentHeight;

                // Calculate exact horizontal width along Z inside the cylinder:
                // Circle eq: (Y - WATER_CENTER_Y)^2 + (Z - Z_tank)^2 = R^2
                // Delta Z = 2 * sqrt(R^2 - (Y - WATER_CENTER_Y)^2)
                const dy = currentHeight - WATER_CENTER_Y;
                const rSq = Math.max(0, WATER_RADIUS * WATER_RADIUS - dy * dy);
                const surfaceWidthZ = 2 * Math.sqrt(rSq);
                const scaledWidthZ = Math.max(0.005, surfaceWidthZ - 0.006);

                // Note: surfaceGeo has plane vertices with width in X and height rotated into Z.
                // scale.z controls width along Z, scale.x controls length along X.
                surfaceMesh.scale.set(1.0, 1.0, scaledWidthZ);
            }

            // Water color warning
            if (clamped >= 95) {
                waterMaterial.color.setHex(0xf43f5e);
                waterMaterial.emissive.setHex(0xbe123c);
                surfaceMaterial.color.setHex(0xfb7185);
                surfaceMaterial.emissive.setHex(0xbe123c);
            } else {
                waterMaterial.color.setHex(0x0284c7);
                waterMaterial.emissive.setHex(0x0369a1);
                surfaceMaterial.color.setHex(0x38bdf8);
                surfaceMaterial.emissive.setHex(0x0284c7);
            }

            // HUD overlay
            document.getElementById('floatingLiters').textContent = clamped.toFixed(1);
            const estHeightCm = (ratio * MAX_HEIGHT_CM).toFixed(1);
            document.getElementById('floatingHeight').textContent = `Tinggi: ${estHeightCm} cm`;
            document.getElementById('floatingPercentage').textContent = `${(ratio * 100).toFixed(1)}%`;
        }

        // --- DASHBOARD UI UPDATES ---
        function updateDashboardUI(liters, source = 'web_manual') {
            const volume = parseFloat(liters);
            const percentage = ((volume / MAX_LITERS) * 100).toFixed(1);
            const heightCm = ((volume / MAX_LITERS) * MAX_HEIGHT_CM).toFixed(1);

            document.getElementById('cardVolume').textContent = volume.toFixed(1);
            document.getElementById('cardPercentage').textContent = percentage;
            document.getElementById('cardHeight').textContent = heightCm;

            let status = 'NORMAL';
            let statusClass = 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';

            if (volume <= 0) {
                status = 'KOSONG';
                statusClass = 'bg-slate-800 text-slate-400 border-slate-700';
            } else if (volume < 20) {
                status = 'LEVEL RENDAH';
                statusClass = 'bg-amber-500/10 text-amber-400 border-amber-500/20';
            } else if (volume >= 95) {
                status = 'SIAGA PENUH';
                statusClass = 'bg-rose-500/10 text-rose-400 border-rose-500/20';
            }

            const badge = document.getElementById('statusBadge');
            badge.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold font-mono uppercase border ${statusClass}`;
            badge.textContent = status;
        }

        // --- SEND UPDATE TO BACKEND (AJAX) ---
        async function sendLevelUpdate(liters, source = 'web_manual') {
            targetLiters = Math.max(0, Math.min(MAX_LITERS, parseFloat(liters)));
            updateDashboardUI(targetLiters, source);

            try {
                await fetch("{{ route('tandon.update') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        volume_liters: targetLiters,
                        source: source,
                        device_id: 'ESP32-TND-01'
                    })
                });
            } catch (err) {
                console.error('Sync level error:', err);
            }
        }

        // --- EVENT HANDLERS ---
        const form = document.getElementById('levelForm');
        const numInput = document.getElementById('numberInput');
        const slider = document.getElementById('levelSlider');
        const sliderDisplay = document.getElementById('sliderValueDisplay');

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const val = parseFloat(numInput.value);
            if (!isNaN(val)) {
                slider.value = val;
                sliderDisplay.textContent = `${val.toFixed(1)} L`;
                sendLevelUpdate(val, 'web_input');
            }
        });

        let debounceTimer = null;
        slider.addEventListener('input', (e) => {
            const val = parseFloat(e.target.value);
            sliderDisplay.textContent = `${val.toFixed(1)} L`;
            numInput.value = val;
            targetLiters = val;
            updateDashboardUI(val, 'web_slider');

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                sendLevelUpdate(val, 'web_slider');
            }, 300);
        });

        document.querySelectorAll('.btnPreset').forEach(btn => {
            btn.addEventListener('click', () => {
                const presetVal = parseFloat(btn.getAttribute('data-preset'));
                numInput.value = presetVal;
                slider.value = presetVal;
                sliderDisplay.textContent = `${presetVal} L`;
                stopSimulation();
                sendLevelUpdate(presetVal, 'web_preset');
            });
        });

        const btnFill = document.getElementById('btnSimulateFill');
        const btnDrain = document.getElementById('btnSimulateDrain');
        const btnStop = document.getElementById('btnStopSimulation');

        function stopSimulation() {
            if (simInterval) {
                clearInterval(simInterval);
                simInterval = null;
            }
            btnStop.classList.add('hidden');
        }

        btnStop.addEventListener('click', stopSimulation);

        btnFill.addEventListener('click', () => {
            stopSimulation();
            btnStop.classList.remove('hidden');
            simInterval = setInterval(() => {
                if (targetLiters >= MAX_LITERS) {
                    stopSimulation();
                    sendLevelUpdate(MAX_LITERS, 'simulation_pump');
                    return;
                }
                const next = Math.min(MAX_LITERS, targetLiters + 2.0);
                targetLiters = next;
                numInput.value = next.toFixed(1);
                slider.value = next;
                sliderDisplay.textContent = `${next.toFixed(1)} L`;
                updateDashboardUI(next, 'simulation_pump');
            }, 250);
        });

        btnDrain.addEventListener('click', () => {
            stopSimulation();
            btnStop.classList.remove('hidden');
            simInterval = setInterval(() => {
                if (targetLiters <= 0) {
                    stopSimulation();
                    sendLevelUpdate(0, 'simulation_drain');
                    return;
                }
                const next = Math.max(0, targetLiters - 2.0);
                targetLiters = next;
                numInput.value = next.toFixed(1);
                slider.value = next;
                sliderDisplay.textContent = `${next.toFixed(1)} L`;
                updateDashboardUI(next, 'simulation_drain');
            }, 250);
        });

        document.getElementById('btnResetCamera').addEventListener('click', () => {
            camera.position.set(2.5, 1.8, 2.5);
            controls.target.set(0, 1.0, 0);
            controls.update();
        });

        document.getElementById('btnAutoRotate').addEventListener('click', () => {
            autoRotate = !autoRotate;
            const btn = document.getElementById('btnAutoRotate');
            if (autoRotate) {
                btn.classList.add('bg-cyan-500/20', 'border-cyan-500/50');
            } else {
                btn.classList.remove('bg-cyan-500/20', 'border-cyan-500/50');
            }
        });

        const btnToggleLid = document.getElementById('btnToggleLid');
        const lidLabel = document.getElementById('lidLabel');

        btnToggleLid.addEventListener('click', () => {
            isLidOpen = !isLidOpen;
            targetLidAngle = isLidOpen ? (2 * Math.PI / 3) : 0;
            lidLabel.textContent = isLidOpen ? 'Tutup: Terbuka' : 'Tutup: Tertutup';
            if (isLidOpen) {
                btnToggleLid.className = 'px-3 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 backdrop-blur-md text-xs font-medium transition flex items-center gap-1.5';
            } else {
                btnToggleLid.className = 'px-3 py-1.5 rounded-xl bg-slate-950/80 hover:bg-slate-800 text-slate-300 hover:text-white border border-slate-800 backdrop-blur-md text-xs font-medium transition flex items-center gap-1.5';
            }
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
</body>
</html>
