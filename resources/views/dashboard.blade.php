<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Smart Maket IoT - Miniatur Rumah 6 Ruangan (Desktop & Mobile)</title>

    <!-- PWA Primary Meta Tags -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#070b14">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SmartMaket">
    <link rel="icon" type="image/svg+xml" href="/icons/icon.svg">
    <link rel="apple-touch-icon" href="/icons/icon.svg">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Maket Dual Stylesheet -->
    <link rel="stylesheet" href="/css/maket.css">
</head>
<body>

    <div class="app-container" id="app-container">
        <!-- 1. Top Navbar (Header Dekstop & Mobile) -->
        <header class="app-navbar">
            <div class="brand-wrap">
                <div class="brand-icon">
                    <i class="fa-solid fa-house-chimney-window"></i>
                </div>
                <div class="brand-text">
                    <h1>SMART MAKET IoT</h1>
                    <p>Miniatur Rumah Pintar 6 Ruangan</p>
                </div>
            </div>

            <div class="nav-actions">
                <!-- Mode Switcher Pill (Bisa beralih manual Desktop vs Mobile) -->
                <div class="view-mode-pill">
                    <button class="mode-btn active" id="btn-mode-desktop" onclick="setViewMode('desktop')">
                        <i class="fa-solid fa-laptop"></i>
                        <span>Desktop</span>
                    </button>
                    <button class="mode-btn" id="btn-mode-mobile" onclick="setViewMode('mobile')">
                        <i class="fa-solid fa-mobile-screen"></i>
                        <span>Mobile</span>
                    </button>
                </div>

                <div class="status-pill desktop-only">
                    <span class="status-dot"></span>
                    <span>Maket Terhubung</span>
                </div>
            </div>
        </header>

        <!-- 2. Dual Grid System (Desktop 2-Kolom / Mobile 1-Kolom) -->
        <main class="dashboard-grid">
            
            <!-- KOLOM KIRI: Visualisasi Miniatur Rumah 2D & Saklar Master -->
            <section class="col-visualizer">
                <!-- Miniatur Rumah Arsitektural 2D (Interactive Live Maket SVG) -->
                <div class="dash-card">
                    <div class="card-title">
                        <span><i class="fa-solid fa-cubes-stacked"></i> MINIATUR RUMAH (MAKET INTERAKTIF LIVE)</span>
                        <span style="font-size:0.75rem; color:var(--text-muted);"><i class="fa-regular fa-hand-pointer"></i> Klik ruangan untuk saklar</span>
                    </div>

                    <div class="miniature-house-svg-wrap">
                        <svg viewBox="0 0 540 400" width="100%" height="auto" style="display:block;">
                            <defs>
                                <!-- Blueprint Blueprint Grid Background -->
                                <pattern id="maket-grid" width="20" height="20" patternUnits="userSpaceOnUse">
                                    <path d="M 20 0 L 0 0 0 20" fill="none" stroke="rgba(255,255,255,0.025)" stroke-width="1"/>
                                </pattern>

                                <!-- Wood Parquet Pattern for Bedrooms -->
                                <pattern id="parquet-pattern" width="16" height="16" patternUnits="userSpaceOnUse">
                                    <rect width="16" height="16" fill="#131d2e"/>
                                    <line x1="0" y1="0" x2="16" y2="0" stroke="rgba(255,255,255,0.04)" stroke-width="1"/>
                                    <line x1="8" y1="0" x2="8" y2="16" stroke="rgba(255,255,255,0.04)" stroke-width="1"/>
                                </pattern>

                                <!-- Tile Pattern for Kitchen & WC -->
                                <pattern id="tile-pattern" width="14" height="14" patternUnits="userSpaceOnUse">
                                    <rect width="14" height="14" fill="#0f172a"/>
                                    <path d="M 14 0 L 0 0 0 14" fill="none" stroke="rgba(56,189,248,0.06)" stroke-width="1"/>
                                </pattern>

                                <!-- Volumetric Radial Glow Gradients for Each Room -->
                                <radialGradient id="glow-kamar1" cx="48%" cy="50%" r="65%">
                                    <stop offset="0%" stop-color="#fef08a" stop-opacity="0.45" />
                                    <stop offset="45%" stop-color="#facc15" stop-opacity="0.22" />
                                    <stop offset="100%" stop-color="#ca8a04" stop-opacity="0" />
                                </radialGradient>

                                <radialGradient id="glow-kamar2" cx="50%" cy="50%" r="65%">
                                    <stop offset="0%" stop-color="#fef08a" stop-opacity="0.45" />
                                    <stop offset="45%" stop-color="#facc15" stop-opacity="0.22" />
                                    <stop offset="100%" stop-color="#ca8a04" stop-opacity="0" />
                                </radialGradient>

                                <radialGradient id="glow-keluarga" cx="50%" cy="50%" r="65%">
                                    <stop offset="0%" stop-color="#fef08a" stop-opacity="0.45" />
                                    <stop offset="50%" stop-color="#facc15" stop-opacity="0.20" />
                                    <stop offset="100%" stop-color="#ca8a04" stop-opacity="0" />
                                </radialGradient>

                                <radialGradient id="glow-dapur" cx="50%" cy="50%" r="65%">
                                    <stop offset="0%" stop-color="#fef08a" stop-opacity="0.45" />
                                    <stop offset="50%" stop-color="#facc15" stop-opacity="0.20" />
                                    <stop offset="100%" stop-color="#ca8a04" stop-opacity="0" />
                                </radialGradient>

                                <radialGradient id="glow-wc" cx="50%" cy="50%" r="65%">
                                    <stop offset="0%" stop-color="#fef08a" stop-opacity="0.5" />
                                    <stop offset="50%" stop-color="#facc15" stop-opacity="0.22" />
                                    <stop offset="100%" stop-color="#ca8a04" stop-opacity="0" />
                                </radialGradient>

                                <radialGradient id="glow-tamu" cx="45%" cy="45%" r="70%">
                                    <stop offset="0%" stop-color="#fef08a" stop-opacity="0.45" />
                                    <stop offset="55%" stop-color="#facc15" stop-opacity="0.20" />
                                    <stop offset="100%" stop-color="#ca8a04" stop-opacity="0" />
                                </radialGradient>
                            </defs>

                            <!-- Background Grid -->
                            <rect width="540" height="400" fill="url(#maket-grid)" />

                            <!-- HALAMAN LUAR & TAMAN RUMAH -->
                            <rect x="15" y="10" width="510" height="380" rx="16" fill="#090e18" stroke="#1e293b" stroke-width="1.5"/>

                            <!-- ======================================================== -->
                            <!-- RUANGAN 1: KAMAR 1 (Top-Left)                           -->
                            <!-- ======================================================== -->
                            @php $k1Id = $lampuKamar1 ? $lampuKamar1->id : 3; @endphp
                            <g class="room-interactive-group" onclick="toggleLampu({{ $k1Id }})">
                                <!-- Lantai Kamar 1 -->
                                <rect id="svg-box-kamar_1" x="25" y="20" width="235" height="120" rx="8" fill="url(#parquet-pattern)" stroke="#334155" stroke-width="2" class="room-floor-base {{ $lampuKamar1 && $lampuKamar1->is_on ? 'glow-active' : '' }}" />

                                <!-- Furniture Kamar 1: Double Bed -->
                                <rect x="36" y="32" width="75" height="88" rx="4" class="mak-bed" />
                                <rect x="36" y="65" width="75" height="55" rx="2" class="mak-blanket" />
                                <!-- Pillows -->
                                <rect x="42" y="36" width="28" height="16" rx="3" class="mak-pillow" />
                                <rect x="76" y="36" width="28" height="16" rx="3" class="mak-pillow" />
                                <!-- Meja Sudut & Lemari -->
                                <rect x="116" y="32" width="22" height="22" rx="3" class="mak-table" />
                                <circle cx="127" cy="43" r="3" fill="#f59e0b" opacity="0.6"/>
                                <rect x="36" y="125" width="70" height="12" rx="2" fill="#1e293b" stroke="#475569" stroke-width="1" />
                                <!-- Jendela Luar -->
                                <line x1="80" y1="20" x2="140" y2="20" class="mak-window" />
                                <!-- Pintu Kamar (Swing Arc) -->
                                <path d="M 225 140 A 25 25 0 0 1 200 115" class="mak-door-arc" />
                                <line x1="225" y1="140" x2="202" y2="117" class="mak-door-leaf" />

                                <!-- Volumetric Lighting Overlay -->
                                <rect id="svg-glow-kamar_1" x="25" y="20" width="235" height="120" rx="8" fill="url(#glow-kamar1)" class="room-light-glow {{ $lampuKamar1 && $lampuKamar1->is_on ? 'is-on' : '' }}" />

                                <!-- Plafon Downlight Fixture & Bulb -->
                                <g transform="translate(145, 80)">
                                    <circle cx="0" cy="0" r="14" fill="rgba(0,0,0,0.5)" stroke="#64748b" stroke-width="1.5" class="lamp-fixture-ring"/>
                                    <circle id="svg-bulb-kamar_1" cx="0" cy="0" r="7" class="lamp-bulb {{ $lampuKamar1 && $lampuKamar1->is_on ? 'is-on' : 'is-off' }}" />
                                </g>

                                <!-- Label Ruangan -->
                                <rect x="155" y="28" width="95" height="20" rx="4" fill="rgba(15,23,42,0.85)" stroke="rgba(255,255,255,0.08)" stroke-width="1"/>
                                <text x="162" y="42" fill="#94a3b8" font-size="10" font-weight="700">1. KAMAR 1</text>
                                <text x="162" y="62" fill="#cbd5e1" font-size="9" font-family="monospace">Lampu D5</text>
                            </g>

                            <!-- ======================================================== -->
                            <!-- RUANGAN 2: KAMAR 2 (Top-Right)                          -->
                            <!-- ======================================================== -->
                            @php $k2Id = $lampuKamar2 ? $lampuKamar2->id : 4; @endphp
                            <g class="room-interactive-group" onclick="toggleLampu({{ $k2Id }})">
                                <!-- Lantai Kamar 2 -->
                                <rect id="svg-box-kamar_2" x="270" y="20" width="245" height="120" rx="8" fill="url(#parquet-pattern)" stroke="#334155" stroke-width="2" class="room-floor-base {{ $lampuKamar2 && $lampuKamar2->is_on ? 'glow-active' : '' }}" />

                                <!-- Furniture Kamar 2: Queen Bed -->
                                <rect x="428" y="32" width="75" height="88" rx="4" class="mak-bed" />
                                <rect x="428" y="65" width="75" height="55" rx="2" class="mak-blanket-alt" />
                                <!-- Pillows -->
                                <rect x="434" y="36" width="28" height="16" rx="3" class="mak-pillow" />
                                <rect x="468" y="36" width="28" height="16" rx="3" class="mak-pillow" />
                                <!-- Meja Belajar & Kursi -->
                                <rect x="282" y="32" width="55" height="28" rx="3" class="mak-table" />
                                <rect x="295" y="36" width="18" height="12" rx="2" fill="#0ea5e9" opacity="0.8" />
                                <circle cx="304" cy="56" r="6" fill="#334155" stroke="#64748b" stroke-width="1"/>
                                <!-- Lemari Pakaian -->
                                <rect x="282" y="75" width="22" height="45" rx="2" fill="#1e293b" stroke="#475569" stroke-width="1" />
                                <!-- Jendela Luar -->
                                <line x1="390" y1="20" x2="450" y2="20" class="mak-window" />
                                <!-- Pintu Kamar 2 (Swing Arc) -->
                                <path d="M 315 140 A 25 25 0 0 0 340 115" class="mak-door-arc" />
                                <line x1="315" y1="140" x2="338" y2="117" class="mak-door-leaf" />

                                <!-- Volumetric Lighting Overlay -->
                                <rect id="svg-glow-kamar_2" x="270" y="20" width="245" height="120" rx="8" fill="url(#glow-kamar2)" class="room-light-glow {{ $lampuKamar2 && $lampuKamar2->is_on ? 'is-on' : '' }}" />

                                <!-- Plafon Downlight Fixture & Bulb -->
                                <g transform="translate(365, 80)">
                                    <circle cx="0" cy="0" r="14" fill="rgba(0,0,0,0.5)" stroke="#64748b" stroke-width="1.5" class="lamp-fixture-ring"/>
                                    <circle id="svg-bulb-kamar_2" cx="0" cy="0" r="7" class="lamp-bulb {{ $lampuKamar2 && $lampuKamar2->is_on ? 'is-on' : 'is-off' }}" />
                                </g>

                                <!-- Label Ruangan -->
                                <rect x="345" y="28" width="75" height="20" rx="4" fill="rgba(15,23,42,0.85)" stroke="rgba(255,255,255,0.08)" stroke-width="1"/>
                                <text x="353" y="42" fill="#94a3b8" font-size="10" font-weight="700">2. KAMAR 2</text>
                                <text x="353" y="62" fill="#cbd5e1" font-size="9" font-family="monospace">Lampu D6</text>
                            </g>

                            <!-- ======================================================== -->
                            <!-- RUANGAN 3: RUANG KELUARGA (Middle-Left)                 -->
                            <!-- ======================================================== -->
                            @php $rkId = $lampuRuangKeluarga ? $lampuRuangKeluarga->id : 2; @endphp
                            <g class="room-interactive-group" onclick="toggleLampu({{ $rkId }})">
                                <!-- Lantai Ruang Keluarga -->
                                <rect id="svg-box-ruang_keluarga" x="25" y="148" width="275" height="115" rx="8" fill="#111827" stroke="#334155" stroke-width="2" class="room-floor-base {{ $lampuRuangKeluarga && $lampuRuangKeluarga->is_on ? 'glow-active' : '' }}" />

                                <!-- Karpet Area Ruang Keluarga -->
                                <rect x="50" y="165" width="150" height="80" rx="6" fill="#1e293b" stroke="rgba(255,255,255,0.05)" stroke-width="1"/>
                                <!-- Sofa L-Shape Besar -->
                                <path d="M 40 160 L 115 160 L 115 180 L 65 180 L 65 235 L 40 235 Z" class="mak-sofa" />
                                <rect x="44" y="164" width="28" height="12" rx="2" class="mak-cushion" />
                                <rect x="76" y="164" width="28" height="12" rx="2" class="mak-cushion" />
                                <rect x="44" y="185" width="16" height="22" rx="2" class="mak-cushion" />
                                <rect x="44" y="210" width="16" height="22" rx="2" class="mak-cushion" />
                                <!-- Meja Kaca Kopi -->
                                <rect x="80" y="190" width="45" height="28" rx="4" class="mak-table" />
                                <circle cx="102" cy="204" r="5" fill="#38bdf8" opacity="0.3"/>
                                <!-- Rak TV & TV Flat -->
                                <rect x="180" y="180" width="10" height="55" rx="2" fill="#1e293b" stroke="#475569" stroke-width="1" />
                                <line x1="184" y1="185" x2="184" y2="230" stroke="#38bdf8" stroke-width="3" stroke-linecap="round"/>

                                <!-- Volumetric Lighting Overlay -->
                                <rect id="svg-glow-ruang_keluarga" x="25" y="148" width="275" height="115" rx="8" fill="url(#glow-keluarga)" class="room-light-glow {{ $lampuRuangKeluarga && $lampuRuangKeluarga->is_on ? 'is-on' : '' }}" />

                                <!-- Plafon Downlight Fixture & Bulb -->
                                <g transform="translate(145, 205)">
                                    <circle cx="0" cy="0" r="14" fill="rgba(0,0,0,0.5)" stroke="#64748b" stroke-width="1.5" class="lamp-fixture-ring"/>
                                    <circle id="svg-bulb-ruang_keluarga" cx="0" cy="0" r="7" class="lamp-bulb {{ $lampuRuangKeluarga && $lampuRuangKeluarga->is_on ? 'is-on' : 'is-off' }}" />
                                </g>

                                <!-- Label Ruangan -->
                                <rect x="165" y="156" width="125" height="18" rx="4" fill="rgba(15,23,42,0.85)" stroke="rgba(255,255,255,0.08)" stroke-width="1"/>
                                <text x="172" y="169" fill="#94a3b8" font-size="9" font-weight="700">3. RUANG KELUARGA</text>
                                <text x="210" y="248" fill="#cbd5e1" font-size="9" font-family="monospace">Lampu D2</text>
                            </g>

                            <!-- ======================================================== -->
                            <!-- RUANGAN 4: DAPUR (Middle-Center)                        -->
                            <!-- ======================================================== -->
                            @php $dpId = $lampuDapur ? $lampuDapur->id : 5; @endphp
                            <g class="room-interactive-group" onclick="toggleLampu({{ $dpId }})">
                                <!-- Lantai Dapur -->
                                <rect id="svg-box-dapur" x="308" y="148" width="125" height="115" rx="8" fill="url(#tile-pattern)" stroke="#334155" stroke-width="2" class="room-floor-base {{ $lampuDapur && $lampuDapur->is_on ? 'glow-active' : '' }}" />

                                <!-- Kitchen Countertop (Kitchen Set) -->
                                <path d="M 315 156 L 425 156 L 425 182 L 395 182 L 395 182 L 315 182 Z" class="mak-wood-counter" />
                                <!-- Wastafel Kitchen Sink -->
                                <rect x="325" y="160" width="28" height="18" rx="2" class="mak-sink" />
                                <circle cx="339" cy="169" r="2.5" fill="#94a3b8"/>
                                <circle cx="339" cy="163" r="1.5" fill="#38bdf8"/>
                                <!-- Kompor Gas 2 Tungku -->
                                <rect x="365" y="160" width="30" height="18" rx="2" fill="#1c1917" stroke="#44403c" stroke-width="1"/>
                                <circle cx="373" cy="169" r="4.5" fill="#44403c" stroke="#ef4444" stroke-width="1"/>
                                <circle cx="387" cy="169" r="4.5" fill="#44403c" stroke="#ef4444" stroke-width="1"/>
                                <!-- Meja Makan & Kursi -->
                                <rect x="330" y="205" width="55" height="35" rx="4" class="mak-table" />
                                <circle cx="342" cy="199" r="5" fill="#334155"/>
                                <circle cx="372" cy="199" r="5" fill="#334155"/>
                                <circle cx="342" cy="246" r="5" fill="#334155"/>
                                <circle cx="372" cy="246" r="5" fill="#334155"/>

                                <!-- Volumetric Lighting Overlay -->
                                <rect id="svg-glow-dapur" x="308" y="148" width="125" height="115" rx="8" fill="url(#glow-dapur)" class="room-light-glow {{ $lampuDapur && $lampuDapur->is_on ? 'is-on' : '' }}" />

                                <!-- Plafon Downlight Fixture & Bulb -->
                                <g transform="translate(390, 215)">
                                    <circle cx="0" cy="0" r="13" fill="rgba(0,0,0,0.5)" stroke="#64748b" stroke-width="1.5" class="lamp-fixture-ring"/>
                                    <circle id="svg-bulb-dapur" cx="0" cy="0" r="6" class="lamp-bulb {{ $lampuDapur && $lampuDapur->is_on ? 'is-on' : 'is-off' }}" />
                                </g>

                                <!-- Label Ruangan -->
                                <rect x="315" y="188" width="55" height="14" rx="3" fill="rgba(15,23,42,0.85)" stroke="rgba(255,255,255,0.08)" stroke-width="1"/>
                                <text x="320" y="199" fill="#94a3b8" font-size="8.5" font-weight="700">4. DAPUR</text>
                                <text x="395" y="240" fill="#cbd5e1" font-size="8" font-family="monospace">D7</text>
                            </g>

                            <!-- ======================================================== -->
                            <!-- RUANGAN 5: WC / KAMAR MANDI (Middle-Right)               -->
                            <!-- ======================================================== -->
                            @php $wcId = $lampuWc ? $lampuWc->id : 6; @endphp
                            <g class="room-interactive-group" onclick="toggleLampu({{ $wcId }})">
                                <!-- Lantai WC -->
                                <rect id="svg-box-wc" x="440" y="148" width="75" height="115" rx="8" fill="url(#tile-pattern)" stroke="#334155" stroke-width="2" class="room-floor-base {{ $lampuWc && $lampuWc->is_on ? 'glow-active' : '' }}" />

                                <!-- Toilet Kloset Duduk -->
                                <rect x="450" y="156" width="22" height="10" rx="2" class="mak-toilet" />
                                <ellipse cx="461" cy="174" rx="9" ry="11" class="mak-toilet" />
                                <ellipse cx="461" cy="174" rx="5" ry="7" fill="#cbd5e1" />
                                <!-- Area Shower & Drain Tile -->
                                <rect x="448" y="210" width="30" height="30" rx="2" fill="none" stroke="#38bdf8" stroke-width="0.8" stroke-dasharray="2,2"/>
                                <circle cx="463" cy="225" r="4" fill="#64748b" />
                                <!-- Pintu WC (Swing Arc) -->
                                <path d="M 440 205 A 20 20 0 0 1 460 185" class="mak-door-arc" />
                                <line x1="440" y1="205" x2="458" y2="187" class="mak-door-leaf" />

                                <!-- Volumetric Lighting Overlay -->
                                <rect id="svg-glow-wc" x="440" y="148" width="75" height="115" rx="8" fill="url(#glow-wc)" class="room-light-glow {{ $lampuWc && $lampuWc->is_on ? 'is-on' : '' }}" />

                                <!-- Plafon Downlight Fixture & Bulb -->
                                <g transform="translate(485, 185)">
                                    <circle cx="0" cy="0" r="12" fill="rgba(0,0,0,0.5)" stroke="#64748b" stroke-width="1.5" class="lamp-fixture-ring"/>
                                    <circle id="svg-bulb-wc" cx="0" cy="0" r="5.5" class="lamp-bulb {{ $lampuWc && $lampuWc->is_on ? 'is-on' : 'is-off' }}" />
                                </g>

                                <!-- Label Ruangan -->
                                <rect x="480" y="210" width="30" height="14" rx="3" fill="rgba(15,23,42,0.85)" stroke="rgba(255,255,255,0.08)" stroke-width="1"/>
                                <text x="484" y="221" fill="#94a3b8" font-size="8.5" font-weight="700">5. WC</text>
                                <text x="488" y="240" fill="#cbd5e1" font-size="8" font-family="monospace">D8</text>
                            </g>

                            <!-- ======================================================== -->
                            <!-- RUANGAN 6: RUANG TAMU & TERAS DEPAN (Bottom)            -->
                            <!-- ======================================================== -->
                            @php $rtId = $lampuRuangTamu ? $lampuRuangTamu->id : 1; @endphp
                            <g class="room-interactive-group" onclick="toggleLampu({{ $rtId }})">
                                <!-- Lantai Ruang Tamu -->
                                <rect id="svg-box-ruang_tamu" x="25" y="270" width="490" height="110" rx="8" fill="#111827" stroke="#334155" stroke-width="2" class="room-floor-base {{ $lampuRuangTamu && $lampuRuangTamu->is_on ? 'glow-active' : '' }}" />

                                <!-- Karpet Area Ruang Tamu -->
                                <rect x="55" y="285" width="180" height="75" rx="6" fill="#1e293b" stroke="rgba(255,255,255,0.04)" stroke-width="1"/>

                                <!-- Sofa Set Tamu: 3-Seater Sofa -->
                                <rect x="75" y="280" width="85" height="24" rx="4" class="mak-sofa" />
                                <rect x="80" y="284" width="22" height="16" rx="2" class="mak-cushion" />
                                <rect x="106" y="284" width="22" height="16" rx="2" class="mak-cushion" />
                                <rect x="132" y="284" width="22" height="16" rx="2" class="mak-cushion" />

                                <!-- Single Armchair Kiri & Kanan -->
                                <rect x="42" y="300" width="20" height="35" rx="3" class="mak-sofa" />
                                <rect x="175" y="300" width="20" height="35" rx="3" class="mak-sofa" />

                                <!-- Meja Tamu Tengah Kaca -->
                                <rect x="85" y="315" width="65" height="26" rx="4" class="mak-table" />
                                <circle cx="117" cy="328" r="6" fill="#f59e0b" opacity="0.3"/>

                                <!-- Pintu Masuk Utama Rumah (Main Entrance Swing Arc) -->
                                <path d="M 270 380 A 35 35 0 0 1 235 345" class="mak-door-arc" stroke="#10b981" />
                                <line x1="270" y1="380" x2="238" y2="348" stroke="#f8fafc" stroke-width="3" stroke-linecap="round" />
                                <!-- Keset Selamat Datang -->
                                <rect x="250" y="365" width="30" height="12" rx="2" fill="#78350f" opacity="0.8" />

                                <!-- Teras Depan / Porch Flooring (Dekorasi Maket) -->
                                <rect x="305" y="282" width="195" height="85" rx="6" fill="#0b1329" stroke="rgba(255,255,255,0.06)" stroke-width="1"/>
                                <!-- Kursi Santai Teras & Meja Kecil -->
                                <circle cx="340" cy="320" r="10" fill="#1e293b" stroke="#64748b" stroke-width="1"/>
                                <circle cx="390" cy="320" r="10" fill="#1e293b" stroke="#64748b" stroke-width="1"/>
                                <circle cx="365" cy="320" r="7" fill="#334155" stroke="#f59e0b" stroke-width="1"/>
                                <!-- Tanaman Hias Pot Teras -->
                                <circle cx="475" cy="300" r="8" fill="#15803d" opacity="0.9" stroke="#22c55e" stroke-width="1"/>
                                <circle cx="475" cy="350" r="8" fill="#15803d" opacity="0.9" stroke="#22c55e" stroke-width="1"/>

                                <!-- Volumetric Lighting Overlay -->
                                <rect id="svg-glow-ruang_tamu" x="25" y="270" width="490" height="110" rx="8" fill="url(#glow-tamu)" class="room-light-glow {{ $lampuRuangTamu && $lampuRuangTamu->is_on ? 'is-on' : '' }}" />

                                <!-- Plafon Downlight Fixture & Bulb (Teras/Ruang Tamu) -->
                                <g transform="translate(180, 345)">
                                    <circle cx="0" cy="0" r="14" fill="rgba(0,0,0,0.5)" stroke="#64748b" stroke-width="1.5" class="lamp-fixture-ring"/>
                                    <circle id="svg-bulb-ruang_tamu" cx="0" cy="0" r="7" class="lamp-bulb {{ $lampuRuangTamu && $lampuRuangTamu->is_on ? 'is-on' : 'is-off' }}" />
                                </g>

                                <!-- Label Ruangan -->
                                <rect x="32" y="352" width="138" height="20" rx="4" fill="rgba(15,23,42,0.85)" stroke="rgba(255,255,255,0.08)" stroke-width="1"/>
                                <text x="40" y="366" fill="#94a3b8" font-size="9.5" font-weight="700">6. RUANG TAMU & TERAS</text>
                                <text x="200" y="350" fill="#cbd5e1" font-size="9" font-family="monospace">Lampu D1</text>
                            </g>
                        </svg>
                    </div>

                    <!-- Saklar Cepat Master -->
                    <div class="master-switch-row">
                        <button class="btn-master btn-master-on" onclick="toggleAllLampu('on')">
                            <i class="fa-solid fa-sun"></i>
                            <span>Nyalakan Semua (6 Lampu)</span>
                        </button>
                        <button class="btn-master btn-master-off" onclick="toggleAllLampu('off')">
                            <i class="fa-solid fa-moon"></i>
                            <span>Matikan Semua</span>
                        </button>
                    </div>
                </div>

                <!-- Tabel Status Pin Hardware Maket (Desktop View Card) -->
                <div class="dash-card desktop-only">
                    <div class="card-title">
                        <span><i class="fa-solid fa-microchip"></i> PEMETAAN PIN HARDWARE (ESP8266)</span>
                        <button class="mode-btn" onclick="openArduinoModal()" style="padding:4px 10px; font-size:0.72rem; background:rgba(56,189,248,0.15); color:var(--accent-cyan);">
                            <i class="fa-solid fa-code"></i> Lihat Kodingan
                        </button>
                    </div>
                    <table class="pin-table">
                        <thead>
                            <tr>
                                <th>Ruangan</th>
                                <th>Lampu</th>
                                <th>Pin Mikrokontroler</th>
                                <th>Status Sinyal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Ruang Tamu</td>
                                <td>LED 1</td>
                                <td><span class="pin-badge">Pin D1 (GPIO5)</span></td>
                                <td id="pin-status-ruang_tamu">{!! $lampuRuangTamu && $lampuRuangTamu->is_on ? '<span style="color:#10b981; font-weight:700;">● HIGH</span>' : '<span style="color:#64748b;">○ LOW</span>' !!}</td>
                            </tr>
                            <tr>
                                <td>Ruang Keluarga</td>
                                <td>LED 2</td>
                                <td><span class="pin-badge">Pin D2 (GPIO4)</span></td>
                                <td id="pin-status-ruang_keluarga">{!! $lampuRuangKeluarga && $lampuRuangKeluarga->is_on ? '<span style="color:#10b981; font-weight:700;">● HIGH</span>' : '<span style="color:#64748b;">○ LOW</span>' !!}</td>
                            </tr>
                            <tr>
                                <td>Kamar 1</td>
                                <td>LED 3</td>
                                <td><span class="pin-badge">Pin D5 (GPIO14)</span></td>
                                <td id="pin-status-kamar_1">{!! $lampuKamar1 && $lampuKamar1->is_on ? '<span style="color:#10b981; font-weight:700;">● HIGH</span>' : '<span style="color:#64748b;">○ LOW</span>' !!}</td>
                            </tr>
                            <tr>
                                <td>Kamar 2</td>
                                <td>LED 4</td>
                                <td><span class="pin-badge">Pin D6 (GPIO12)</span></td>
                                <td id="pin-status-kamar_2">{!! $lampuKamar2 && $lampuKamar2->is_on ? '<span style="color:#10b981; font-weight:700;">● HIGH</span>' : '<span style="color:#64748b;">○ LOW</span>' !!}</td>
                            </tr>
                            <tr>
                                <td>Dapur</td>
                                <td>LED 5</td>
                                <td><span class="pin-badge">Pin D7 (GPIO13)</span></td>
                                <td id="pin-status-dapur">{!! $lampuDapur && $lampuDapur->is_on ? '<span style="color:#10b981; font-weight:700;">● HIGH</span>' : '<span style="color:#64748b;">○ LOW</span>' !!}</td>
                            </tr>
                            <tr>
                                <td>WC / Mandi</td>
                                <td>LED 6</td>
                                <td><span class="pin-badge">Pin D8 (GPIO15)</span></td>
                                <td id="pin-status-wc">{!! $lampuWc && $lampuWc->is_on ? '<span style="color:#10b981; font-weight:700;">● HIGH</span>' : '<span style="color:#64748b;">○ LOW</span>' !!}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- KOLOM KANAN: Panel Saklar 6 Lampu & Log Riwayat -->
            <section class="col-controls">
                <!-- Bar Status Lampu & Tombol Buka di HP -->
                <div class="stat-summary-bar">
                    <div class="stat-chip">
                        <i class="fa-solid fa-lightbulb"></i>
                        <div>
                            <div class="stat-val" id="stat-active-count">{{ $activeCount }} / {{ $totalCount }}</div>
                            <div class="stat-lbl">Lampu Menyala</div>
                        </div>
                    </div>
                    <div class="btn-open-hp" onclick="openPhoneModal()">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <i class="fa-solid fa-mobile-screen-button"></i>
                            <span>Buka di HP (PWA)</span>
                        </div>
                        <i class="fa-solid fa-chevron-right" style="font-size:0.75rem;"></i>
                    </div>
                </div>

                <!-- Grid Saklar 6 Lampu Ruangan -->
                <div class="section-title">Saklar Lampu Setiap Ruangan</div>
                <div class="control-grid">
                    <!-- 1. Ruang Tamu -->
                    @if($lampuRuangTamu)
                    <div class="device-card {{ $lampuRuangTamu->is_on ? 'is-active' : '' }}" id="device-card-{{ $lampuRuangTamu->id }}" onclick="toggleLampu({{ $lampuRuangTamu->id }})">
                        <div class="device-top">
                            <div class="device-icon-wrap"><i class="fa-solid fa-couch"></i></div>
                            <label class="toggle-switch" onclick="event.stopPropagation();">
                                <input type="checkbox" id="toggle-chk-{{ $lampuRuangTamu->id }}" {{ $lampuRuangTamu->is_on ? 'checked' : '' }} onchange="toggleLampu({{ $lampuRuangTamu->id }})">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="device-info">
                            <div class="device-name">{{ $lampuRuangTamu->name }}</div>
                            <div class="device-pin">Pin: {{ $lampuRuangTamu->settings['pin'] ?? 'D1' }}</div>
                            <div class="switch-wrap">
                                <span class="switch-label">{{ $lampuRuangTamu->is_on ? 'MENYALA' : 'MATI' }}</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- 2. Ruang Keluarga -->
                    @if($lampuRuangKeluarga)
                    <div class="device-card {{ $lampuRuangKeluarga->is_on ? 'is-active' : '' }}" id="device-card-{{ $lampuRuangKeluarga->id }}" onclick="toggleLampu({{ $lampuRuangKeluarga->id }})">
                        <div class="device-top">
                            <div class="device-icon-wrap"><i class="fa-solid fa-tv"></i></div>
                            <label class="toggle-switch" onclick="event.stopPropagation();">
                                <input type="checkbox" id="toggle-chk-{{ $lampuRuangKeluarga->id }}" {{ $lampuRuangKeluarga->is_on ? 'checked' : '' }} onchange="toggleLampu({{ $lampuRuangKeluarga->id }})">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="device-info">
                            <div class="device-name">{{ $lampuRuangKeluarga->name }}</div>
                            <div class="device-pin">Pin: {{ $lampuRuangKeluarga->settings['pin'] ?? 'D2' }}</div>
                            <div class="switch-wrap">
                                <span class="switch-label">{{ $lampuRuangKeluarga->is_on ? 'MENYALA' : 'MATI' }}</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- 3. Kamar 1 -->
                    @if($lampuKamar1)
                    <div class="device-card {{ $lampuKamar1->is_on ? 'is-active' : '' }}" id="device-card-{{ $lampuKamar1->id }}" onclick="toggleLampu({{ $lampuKamar1->id }})">
                        <div class="device-top">
                            <div class="device-icon-wrap"><i class="fa-solid fa-bed"></i></div>
                            <label class="toggle-switch" onclick="event.stopPropagation();">
                                <input type="checkbox" id="toggle-chk-{{ $lampuKamar1->id }}" {{ $lampuKamar1->is_on ? 'checked' : '' }} onchange="toggleLampu({{ $lampuKamar1->id }})">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="device-info">
                            <div class="device-name">{{ $lampuKamar1->name }}</div>
                            <div class="device-pin">Pin: {{ $lampuKamar1->settings['pin'] ?? 'D5' }}</div>
                            <div class="switch-wrap">
                                <span class="switch-label">{{ $lampuKamar1->is_on ? 'MENYALA' : 'MATI' }}</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- 4. Kamar 2 -->
                    @if($lampuKamar2)
                    <div class="device-card {{ $lampuKamar2->is_on ? 'is-active' : '' }}" id="device-card-{{ $lampuKamar2->id }}" onclick="toggleLampu({{ $lampuKamar2->id }})">
                        <div class="device-top">
                            <div class="device-icon-wrap"><i class="fa-solid fa-bed"></i></div>
                            <label class="toggle-switch" onclick="event.stopPropagation();">
                                <input type="checkbox" id="toggle-chk-{{ $lampuKamar2->id }}" {{ $lampuKamar2->is_on ? 'checked' : '' }} onchange="toggleLampu({{ $lampuKamar2->id }})">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="device-info">
                            <div class="device-name">{{ $lampuKamar2->name }}</div>
                            <div class="device-pin">Pin: {{ $lampuKamar2->settings['pin'] ?? 'D6' }}</div>
                            <div class="switch-wrap">
                                <span class="switch-label">{{ $lampuKamar2->is_on ? 'MENYALA' : 'MATI' }}</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- 5. Dapur -->
                    @if($lampuDapur)
                    <div class="device-card {{ $lampuDapur->is_on ? 'is-active' : '' }}" id="device-card-{{ $lampuDapur->id }}" onclick="toggleLampu({{ $lampuDapur->id }})">
                        <div class="device-top">
                            <div class="device-icon-wrap"><i class="fa-solid fa-utensils"></i></div>
                            <label class="toggle-switch" onclick="event.stopPropagation();">
                                <input type="checkbox" id="toggle-chk-{{ $lampuDapur->id }}" {{ $lampuDapur->is_on ? 'checked' : '' }} onchange="toggleLampu({{ $lampuDapur->id }})">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="device-info">
                            <div class="device-name">{{ $lampuDapur->name }}</div>
                            <div class="device-pin">Pin: {{ $lampuDapur->settings['pin'] ?? 'D7' }}</div>
                            <div class="switch-wrap">
                                <span class="switch-label">{{ $lampuDapur->is_on ? 'MENYALA' : 'MATI' }}</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- 6. WC -->
                    @if($lampuWc)
                    <div class="device-card {{ $lampuWc->is_on ? 'is-active' : '' }}" id="device-card-{{ $lampuWc->id }}" onclick="toggleLampu({{ $lampuWc->id }})">
                        <div class="device-top">
                            <div class="device-icon-wrap"><i class="fa-solid fa-bath"></i></div>
                            <label class="toggle-switch" onclick="event.stopPropagation();">
                                <input type="checkbox" id="toggle-chk-{{ $lampuWc->id }}" {{ $lampuWc->is_on ? 'checked' : '' }} onchange="toggleLampu({{ $lampuWc->id }})">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="device-info">
                            <div class="device-name">{{ $lampuWc->name }}</div>
                            <div class="device-pin">Pin: {{ $lampuWc->settings['pin'] ?? 'D8' }}</div>
                            <div class="switch-wrap">
                                <span class="switch-label">{{ $lampuWc->is_on ? 'MENYALA' : 'MATI' }}</span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Log Riwayat Saklar Real-Time -->
                <div class="section-title">Riwayat Saklar</div>
                <div class="log-card" id="activity-logs-list">
                    @forelse($recentLogs as $log)
                    <div class="log-item">
                        <div class="log-dot"></div>
                        <div style="flex:1;">
                            <div style="font-weight:600;">{{ $log->action }}</div>
                            <div style="font-size:0.68rem; color:var(--text-dim);">{{ $log->created_at->diffForHumans() }} • {{ $log->user_or_source }}</div>
                        </div>
                    </div>
                    @empty
                    <div style="font-size:0.75rem; color:var(--text-dim); text-align:center; padding:10px;">Belum ada aktivitas saklar.</div>
                    @endforelse
                </div>
            </section>
        </main>
    </div>

    <!-- Mobile Bottom Navigation Bar (Muncul hanya di HP atau saat mode mobile aktif) -->
    <nav class="bottom-nav">
        <a class="nav-tab active" onclick="window.scrollTo({top: 0, behavior: 'smooth'});">
            <i class="fa-solid fa-house"></i>
            <span>Maket</span>
        </a>
        <a class="nav-tab" onclick="openPhoneModal()">
            <i class="fa-solid fa-mobile-screen"></i>
            <span>Buka di HP</span>
        </a>
        <a class="nav-tab" onclick="openArduinoModal()">
            <i class="fa-solid fa-code"></i>
            <span>Kode Arduino</span>
        </a>
    </nav>

    <!-- Modal 1: Panduan Konek ke HP (PWA) -->
    <div class="modal-backdrop" id="phone-connect-modal" onclick="if(event.target===this) closePhoneModal();">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title"><i class="fa-solid fa-mobile-screen-button" style="color:var(--accent-cyan);"></i> Buka Aplikasi di HP Anda</div>
                <button class="modal-close" onclick="closePhoneModal()">&times;</button>
            </div>
            <div style="font-size:0.82rem; color:var(--text-muted); line-height:1.5;">
                1. Pastikan HP dan Laptop Anda terhubung ke <b>jaringan Wi-Fi / Hotspot yang sama</b>.<br>
                2. Buka browser di HP (Chrome / Safari), lalu ketik alamat berikut:
            </div>
            <div style="background:#090e1a; padding:12px; border-radius:10px; border:1px solid rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:space-between;">
                <code id="phone-url-display" style="color:var(--accent-cyan); font-weight:700; font-size:0.9rem;">http://localhost:8000</code>
                <button onclick="copyPhoneUrl()" style="background:var(--primary); color:#fff; border:none; padding:6px 12px; border-radius:6px; font-size:0.75rem; cursor:pointer;">
                    <i class="fa-solid fa-copy"></i> Salin
                </button>
            </div>
            <div style="font-size:0.8rem; color:var(--text-dim); line-height:1.4;">
                💡 <b>Jadikan Aplikasi di HP (PWA):</b> Di browser HP, tap tombol menu titik tiga atau ikon share, lalu pilih <b>"Tambahkan ke Layar Utama" (Add to Home Screen)</b>. Aplikasi akan terpasang di HP Anda seperti aplikasi native tanpa address bar!
            </div>
            <button id="btn-pwa-install" onclick="installPwa()" style="display:none; background:var(--accent-emerald); color:#fff; border:none; padding:10px; border-radius:8px; font-weight:600; cursor:pointer;">
                <i class="fa-solid fa-download"></i> Pasang Aplikasi di HP Sekarang
            </button>
        </div>
    </div>

    <!-- Modal 2: Contoh Kodingan Arduino / ESP32 untuk 6 Lampu LED -->
    <div class="modal-backdrop" id="arduino-code-modal" onclick="if(event.target===this) closeArduinoModal();">
        <div class="modal-box" style="max-width:520px;">
            <div class="modal-header">
                <div class="modal-title"><i class="fa-solid fa-code" style="color:var(--accent-cyan);"></i> Program Arduino IDE (ESP8266 / ESP32)</div>
                <button class="modal-close" onclick="closeArduinoModal()">&times;</button>
            </div>
            <div style="font-size:0.8rem; color:var(--text-muted);">
                Gunakan kodingan berikut di Arduino IDE untuk modul ESP8266 NodeMCU yang mengendalikan 6 LED pada maket rumah:
            </div>
            <pre style="background:#090e1a; padding:12px; border-radius:10px; border:1px solid rgba(255,255,255,0.1); color:#38bdf8; font-size:0.72rem; overflow-x:auto; max-height:220px;">
#include &lt;ESP8266WiFi.h&gt;
#include &lt;ESP8266HTTPClient.h&gt;
#include &lt;ArduinoJson.h&gt;

const char* ssid = "NAMA_WIFI";
const char* pass = "PASSWORD_WIFI";
const char* serverUrl = "http://IP_KOMPUTER:8000/api/status";

// 6 Pin LED untuk 6 Ruangan
#define LED_RUANG_TAMU     D1 // GPIO5
#define LED_RUANG_KELUARGA D2 // GPIO4
#define LED_KAMAR_1        D5 // GPIO14
#define LED_KAMAR_2        D6 // GPIO12
#define LED_DAPUR          D7 // GPIO13
#define LED_WC             D8 // GPIO15

void setup() {
  Serial.begin(115200);
  pinMode(LED_RUANG_TAMU, OUTPUT);
  pinMode(LED_RUANG_KELUARGA, OUTPUT);
  pinMode(LED_KAMAR_1, OUTPUT);
  pinMode(LED_KAMAR_2, OUTPUT);
  pinMode(LED_DAPUR, OUTPUT);
  pinMode(LED_WC, OUTPUT);

  WiFi.begin(ssid, pass);
  while (WiFi.status() != WL_CONNECTED) { delay(500); Serial.print("."); }
  Serial.println("\nWiFi Terhubung!");
}

void loop() {
  if (WiFi.status() == WL_CONNECTED) {
    WiFiClient client;
    HTTPClient http;
    http.begin(client, serverUrl);
    int httpCode = http.GET();

    if (httpCode == 200) {
      String payload = http.getString();
      StaticJsonDocument&lt;512&gt; doc;
      deserializeJson(doc, payload);

      digitalWrite(LED_RUANG_TAMU,     doc["ruang_tamu"]     == 1 ? HIGH : LOW);
      digitalWrite(LED_RUANG_KELUARGA, doc["ruang_keluarga"] == 1 ? HIGH : LOW);
      digitalWrite(LED_KAMAR_1,        doc["kamar_1"]        == 1 ? HIGH : LOW);
      digitalWrite(LED_KAMAR_2,        doc["kamar_2"]        == 1 ? HIGH : LOW);
      digitalWrite(LED_DAPUR,          doc["dapur"]          == 1 ? HIGH : LOW);
      digitalWrite(LED_WC,             doc["wc"]             == 1 ? HIGH : LOW);
    }
    http.end();
  }
  delay(1000); // Polling setiap 1 detik
}
            </pre>
            <button onclick="closeArduinoModal()" style="background:var(--primary); color:#fff; border:none; padding:9px; border-radius:8px; font-weight:600; cursor:pointer;">
                Tutup Panduan
            </button>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toast-container"></div>

    <!-- Core Maket Dual Script -->
    <script src="/js/maket.js"></script>
</body>
</html>
