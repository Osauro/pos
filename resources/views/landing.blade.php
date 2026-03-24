<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="MiSocio POS — Sistema de punto de venta para negocios de comida. Fácil, rápido y solo 50 Bs/año.">
    <title>MiSocio POS — Sistema POS para tu negocio de comida</title>

    <link rel="icon" href="{{ asset('assets/images/favicon.png') }}" type="image/x-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:opsz,wght@6..12,400;6..12,600;6..12,700;6..12,800;6..12,900&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('assets/css/fontawesome-min.css') }}">

    <style>
        :root {
            --brand: #29adb2;
            --brand-dark: #1e8b8f;
            --brand-light: #e8f8f8;
            --accent: #f99d2a;
            --dark: #1a2535;
            --text-muted: #6c757d;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Nunito Sans', sans-serif;
            color: var(--dark);
            overflow-x: hidden;
            background: #fff;
        }

        /* ─── NAVBAR ─────────────────────────────────────────────── */
        .nav-landing {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            background: rgba(255,255,255,0.97);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(0,0,0,.06);
            padding: 10px 0;
            transition: box-shadow .3s;
        }
        .nav-landing.scrolled { box-shadow: 0 2px 20px rgba(0,0,0,.1); }
        .nav-logo { height: 48px; }
        .nav-cta {
            background: var(--brand);
            color: #fff !important;
            border-radius: 50px;
            padding: 8px 22px !important;
            font-weight: 700;
            transition: background .2s, transform .2s;
        }
        .nav-cta:hover { background: var(--brand-dark); transform: translateY(-1px); }

        /* ─── HERO ───────────────────────────────────────────────── */
        .hero {
            min-height: 100vh;
            background: linear-gradient(135deg, #0d2137 0%, #1a3a4a 50%, #0d6e72 100%);
            display: flex;
            align-items: center;
            padding: 100px 0 60px;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 20% 80%, rgba(41,173,178,.25) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(249,157,42,.15) 0%, transparent 50%);
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(41,173,178,.2);
            border: 1px solid rgba(41,173,178,.4);
            color: #7de8eb;
            border-radius: 50px;
            padding: 5px 14px;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        .hero h1 {
            font-size: clamp(2.2rem, 5vw, 3.6rem);
            font-weight: 900;
            line-height: 1.15;
            color: #fff;
            margin-bottom: 20px;
        }
        .hero h1 span { color: var(--accent); }
        .hero p.lead {
            font-size: 1.15rem;
            color: rgba(255,255,255,.8);
            max-width: 500px;
            line-height: 1.7;
            margin-bottom: 36px;
        }
        .btn-hero-primary {
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 50px;
            padding: 14px 32px;
            font-size: 1rem;
            font-weight: 800;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all .2s;
            box-shadow: 0 8px 24px rgba(249,157,42,.4);
        }
        .btn-hero-primary:hover {
            background: #e08a1a;
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(249,157,42,.5);
            color: #fff;
        }
        .btn-hero-secondary {
            background: transparent;
            color: rgba(255,255,255,.85);
            border: 2px solid rgba(255,255,255,.3);
            border-radius: 50px;
            padding: 14px 32px;
            font-size: 1rem;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all .2s;
        }
        .btn-hero-secondary:hover {
            border-color: rgba(255,255,255,.7);
            color: #fff;
            transform: translateY(-2px);
        }
        .precio-hero {
            display: inline-flex;
            align-items: baseline;
            gap: 4px;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 12px;
            padding: 8px 16px;
            color: #fff;
            margin-top: 24px;
        }
        .precio-hero .monto { font-size: 2rem; font-weight: 900; color: var(--accent); }
        .precio-hero .periodo { font-size: .85rem; color: rgba(255,255,255,.6); }
        .hero-mascota {
            max-width: 420px;
            width: 100%;
            filter: drop-shadow(0 20px 60px rgba(0,0,0,.4));
            animation: float 4s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-14px); }
        }
        .hero-stats {
            display: flex;
            gap: 28px;
            flex-wrap: wrap;
            margin-top: 32px;
        }
        .hero-stat {
            text-align: center;
        }
        .hero-stat .num { font-size: 1.6rem; font-weight: 900; color: var(--accent); }
        .hero-stat .lbl { font-size: .75rem; color: rgba(255,255,255,.6); text-transform: uppercase; letter-spacing: .5px; }

        /* ─── SECCIÓN GENÉRICA ───────────────────────────────────── */
        section { padding: 80px 0; }
        .section-label {
            font-size: .75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--brand);
            margin-bottom: 8px;
        }
        .section-title {
            font-size: clamp(1.7rem, 3.5vw, 2.4rem);
            font-weight: 900;
            line-height: 1.2;
            margin-bottom: 16px;
        }
        .section-sub {
            font-size: 1.05rem;
            color: var(--text-muted);
            max-width: 560px;
        }

        /* ─── BENEFICIOS ─────────────────────────────────────────── */
        .benefits { background: #f8fbfb; }
        .benefit-card {
            background: #fff;
            border-radius: 16px;
            padding: 28px 24px;
            height: 100%;
            border: 1px solid rgba(0,0,0,.06);
            transition: transform .25s, box-shadow .25s;
        }
        .benefit-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 40px rgba(41,173,178,.12);
        }
        .benefit-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: var(--brand-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: var(--brand);
            margin-bottom: 16px;
        }
        .benefit-card h5 {
            font-size: 1rem;
            font-weight: 800;
            margin-bottom: 8px;
        }
        .benefit-card p {
            font-size: .9rem;
            color: var(--text-muted);
            margin: 0;
            line-height: 1.6;
        }

        /* ─── FUNCIONES ──────────────────────────────────────────── */
        .feature-row { margin-bottom: 80px; }
        .feature-row:last-child { margin-bottom: 0; }
        .feature-img-wrap {
            border-radius: 20px;
            overflow: hidden;
            background: linear-gradient(135deg, var(--brand-light), #fff);
            padding: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 280px;
            border: 1px solid rgba(41,173,178,.15);
        }
        .feature-img-wrap img { max-width: 260px; width: 100%; }
        .feature-badge {
            display: inline-block;
            background: var(--brand-light);
            color: var(--brand);
            border-radius: 50px;
            padding: 4px 12px;
            font-size: .73rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 12px;
        }
        .feature-title {
            font-size: 1.6rem;
            font-weight: 900;
            margin-bottom: 14px;
            line-height: 1.3;
        }
        .feature-desc {
            color: var(--text-muted);
            font-size: .95rem;
            line-height: 1.7;
            margin-bottom: 20px;
        }
        .check-list { list-style: none; padding: 0; margin: 0; }
        .check-list li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 10px;
            font-size: .9rem;
            color: #333;
        }
        .check-list li i { color: var(--brand); margin-top: 2px; flex-shrink: 0; }

        /* ─── PRECIO ─────────────────────────────────────────────── */
        .pricing { background: linear-gradient(135deg, #0d2137, #1a3a4a); }
        .pricing .section-title { color: #fff; }
        .pricing .section-sub { color: rgba(255,255,255,.65); }
        .pricing .section-label { color: var(--accent); }
        .price-card {
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 24px;
            padding: 40px 36px;
            position: relative;
            backdrop-filter: blur(8px);
            max-width: 440px;
            margin: 0 auto;
        }
        .price-card .badge-top {
            position: absolute;
            top: -14px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--accent);
            color: #fff;
            border-radius: 50px;
            padding: 4px 18px;
            font-size: .75rem;
            font-weight: 800;
            white-space: nowrap;
        }
        .price-amount {
            font-size: 3.8rem;
            font-weight: 900;
            color: #fff;
            line-height: 1;
        }
        .price-amount sup { font-size: 1.4rem; vertical-align: top; margin-top: 12px; }
        .price-amount .per { font-size: .9rem; font-weight: 600; color: rgba(255,255,255,.5); }
        .price-feature {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,.07);
            color: rgba(255,255,255,.85);
            font-size: .9rem;
        }
        .price-feature:last-of-type { border-bottom: none; }
        .price-feature i { color: var(--brand); width: 18px; text-align: center; }
        .price-per-day {
            text-align: center;
            color: rgba(255,255,255,.5);
            font-size: .8rem;
            margin-top: 6px;
        }
        .btn-price {
            background: var(--brand);
            color: #fff;
            border: none;
            border-radius: 50px;
            padding: 14px 32px;
            font-size: 1rem;
            font-weight: 800;
            text-decoration: none;
            display: block;
            text-align: center;
            transition: all .2s;
            margin-top: 28px;
            box-shadow: 0 6px 20px rgba(41,173,178,.4);
        }
        .btn-price:hover {
            background: var(--brand-dark);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(41,173,178,.5);
        }
        .trial-note {
            text-align: center;
            color: rgba(255,255,255,.45);
            font-size: .78rem;
            margin-top: 12px;
        }
        .compare-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255,255,255,.7);
            font-size: .88rem;
            padding: 8px 0;
        }
        .compare-item .bad { color: #fc8181; }
        .compare-item .good { color: #68d391; }

        /* ─── CTA FINAL ──────────────────────────────────────────── */
        .cta-section {
            background: var(--brand-light);
            text-align: center;
        }
        .cta-section .section-title { color: var(--dark); }
        .mascota-cta {
            max-width: 200px;
            margin: 0 auto 24px;
            display: block;
            animation: float 3.5s ease-in-out infinite;
        }

        /* ─── FOOTER ─────────────────────────────────────────────── */
        footer {
            background: var(--dark);
            color: rgba(255,255,255,.55);
            padding: 32px 0;
            font-size: .83rem;
            text-align: center;
        }
        footer a { color: var(--brand); text-decoration: none; }

        /* ─── RESPONSIVE ─────────────────────────────────────────── */
        @media (max-width: 768px) {
            .hero { padding: 90px 0 50px; text-align: center; }
            .hero p.lead { margin: 0 auto 28px; }
            .hero-stats { justify-content: center; }
            .hero-mascota { max-width: 260px; margin: 40px auto 0; }
            .hero-buttons { justify-content: center !important; }
            .feature-row .order-md-1 { order: 1; }
            .feature-row .order-md-2 { order: 2; }
            section { padding: 60px 0; }
        }
    </style>
</head>
<body>

<!-- ═══ NAVBAR ═══════════════════════════════════════════════════════════ -->
<nav class="nav-landing" id="navbar">
    <div class="container d-flex align-items-center justify-content-between">
        <a href="/">
            <img src="{{ asset('assets/images/logo.png') }}" alt="MiSocio POS" class="nav-logo">
        </a>
        <div class="d-flex align-items-center gap-3">
            <a href="#precios" class="text-decoration-none text-dark fw-600 d-none d-md-inline" style="font-size:.9rem;">Precios</a>
            <a href="#funciones" class="text-decoration-none text-dark fw-600 d-none d-md-inline" style="font-size:.9rem;">Funciones</a>
            <a href="{{ route('login') }}" class="nav-cta">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> Ingresar
            </a>
        </div>
    </div>
</nav>

<!-- ═══ HERO ══════════════════════════════════════════════════════════════ -->
<section class="hero">
    <div class="container position-relative">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="hero-badge">
                    <i class="fa-solid fa-bolt"></i> Sistema POS 100% online
                </div>
                <h1>El POS que tu <span>negocio de comida</span> necesitaba</h1>
                <p class="lead">
                    Controla tus ventas, turnos, productos y equipo desde cualquier
                    dispositivo. Sin instalaciones, sin complicaciones.
                    <strong style="color:#fff;">Solo 50 Bs al año.</strong>
                </p>
                <div class="d-flex gap-3 flex-wrap hero-buttons">
                    <a href="{{ route('login') }}" class="btn-hero-primary">
                        <i class="fa-solid fa-store"></i> Activar mi tienda gratis
                    </a>
                    <a href="#funciones" class="btn-hero-secondary">
                        <i class="fa-solid fa-play-circle"></i> Ver cómo funciona
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="hero-stat">
                        <div class="num">30</div>
                        <div class="lbl">Días gratis</div>
                    </div>
                    <div class="hero-stat">
                        <div class="num">50 Bs</div>
                        <div class="lbl">Al año</div>
                    </div>
                    <div class="hero-stat">
                        <div class="num">∞</div>
                        <div class="lbl">Ventas/día</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <img src="{{ asset('assets/images/mascota-bienvenida.png') }}"
                     alt="MiSocio POS" class="hero-mascota">
            </div>
        </div>
    </div>
</section>

<!-- ═══ BENEFICIOS ════════════════════════════════════════════════════════ -->
<section class="benefits" id="beneficios">
    <div class="container">
        <div class="text-center mb-56" style="margin-bottom:52px;">
            <div class="section-label">¿Por qué MiSocio?</div>
            <h2 class="section-title">Todo lo que tu negocio necesita,<br>sin pagar de más</h2>
            <p class="section-sub mx-auto text-center">
                Diseñado específicamente para restaurantes, cafeterías y negocios de comida en Bolivia.
            </p>
        </div>
        <div class="row g-4">
            <div class="col-sm-6 col-lg-3">
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fa-solid fa-mobile-screen-button"></i></div>
                    <h5>Funciona en tu celular</h5>
                    <p>Sin necesidad de computadora. Toma pedidos desde cualquier teléfono o tablet con conexión a internet.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fa-solid fa-bolt"></i></div>
                    <h5>Súper rápido</h5>
                    <p>Agrega items al carrito en segundos. El POS está optimizado para el ritmo de trabajo en hora pico.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fa-solid fa-users"></i></div>
                    <h5>Equipo ilimitado</h5>
                    <p>Agrega cajeros, meseros y administradores. Cada uno con sus propios accesos y permisos.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fa-solid fa-chart-line"></i></div>
                    <h5>Reportes en tiempo real</h5>
                    <p>Ve tus ventas del día, movimientos de caja y el historial completo desde cualquier lugar.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fa-solid fa-print"></i></div>
                    <h5>Impresión de tickets</h5>
                    <p>Compatible con impresoras térmicas de 58mm y 80mm. Con el agente de escritorio imprime en segundos.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fa-solid fa-calendar-check"></i></div>
                    <h5>Control de turnos</h5>
                    <p>Apertura y cierre de caja por semana. Sabe exactamente quién trabajó y cuánto se vendió en cada turno.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fa-solid fa-image"></i></div>
                    <h5>Galería de imágenes</h5>
                    <p>Sube fotos a tus productos para que tu equipo identifique rápidamente cada plato del menú.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fa-solid fa-shield-halved"></i></div>
                    <h5>Sin datos perdidos</h5>
                    <p>Todo en la nube. Si se va la luz o se cae el celular, tus ventas y datos siguen seguros.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══ FUNCIONES ═════════════════════════════════════════════════════════ -->
<section id="funciones">
    <div class="container">

        <div class="text-center mb-5">
            <div class="section-label">Funciones</div>
            <h2 class="section-title">Pensado para negocios<br>de comida reales</h2>
        </div>

        <!-- Feature 1: POS -->
        <div class="row align-items-center g-5 feature-row">
            <div class="col-lg-5">
                <div class="feature-img-wrap">
                    <img src="{{ asset('assets/images/mascota-senalando.png') }}" alt="Punto de venta rápido">
                </div>
            </div>
            <div class="col-lg-7">
                <span class="feature-badge">Punto de venta</span>
                <h3 class="feature-title">Registra ventas en<br>segundos, no en minutos</h3>
                <p class="feature-desc">
                    El POS está diseñado para velocidad. Navega por tu menú, agrega
                    platos con acompañamientos personalizados y cobra, todo sin fricciones.
                </p>
                <ul class="check-list">
                    <li><i class="fa-solid fa-check-circle"></i> Selección de plato + acompañamiento (arroz, fideo, mixto)</li>
                    <li><i class="fa-solid fa-check-circle"></i> Carrito múltiple con contador visual</li>
                    <li><i class="fa-solid fa-check-circle"></i> Categorías: platos, refrescos, porciones</li>
                    <li><i class="fa-solid fa-check-circle"></i> Ticket impreso automáticamente al cobrar</li>
                </ul>
            </div>
        </div>

        <!-- Feature 2: Turnos -->
        <div class="row align-items-center g-5 feature-row flex-lg-row-reverse">
            <div class="col-lg-5">
                <div class="feature-img-wrap" style="background:linear-gradient(135deg,#fff8ee,#fff);">
                    <img src="{{ asset('assets/images/mascota-pulgar.png') }}" alt="Control de turnos">
                </div>
            </div>
            <div class="col-lg-7">
                <span class="feature-badge" style="background:#fff8ee;color:var(--accent);">Turnos y caja</span>
                <h3 class="feature-title">Abre y cierra caja<br>con total control</h3>
                <p class="feature-desc">
                    Cada semana abre un turno, registra el monto inicial y al final del día
                    tienes el saldo exacto. Cero sorpresas, cero descuadres.
                </p>
                <ul class="check-list">
                    <li><i class="fa-solid fa-check-circle"></i> Apertura de caja con monto inicial</li>
                    <li><i class="fa-solid fa-check-circle"></i> Registro automático de ingresos por ventas</li>
                    <li><i class="fa-solid fa-check-circle"></i> Egresos manuales (gastos del día)</li>
                    <li><i class="fa-solid fa-check-circle"></i> Asignación de turnos por colaborador</li>
                </ul>
            </div>
        </div>

        <!-- Feature 3: Usuarios -->
        <div class="row align-items-center g-5 feature-row">
            <div class="col-lg-5">
                <div class="feature-img-wrap" style="background:linear-gradient(135deg,#f0fffe,#fff);">
                    <img src="{{ asset('assets/images/mascota-sonrisa.png') }}" alt="Gestión de usuarios">
                </div>
            </div>
            <div class="col-lg-7">
                <span class="feature-badge">Tu equipo</span>
                <h3 class="feature-title">Administra a tu equipo<br>sin complicaciones</h3>
                <p class="feature-desc">
                    Agrega cajeros y administradores con un PIN de 4 dígitos. Cada rol
                    tiene acceso solo a lo que necesita, sin que nadie pueda meterse
                    donde no debe.
                </p>
                <ul class="check-list">
                    <li><i class="fa-solid fa-check-circle"></i> Rol Admin: acceso total al sistema</li>
                    <li><i class="fa-solid fa-check-circle"></i> Rol Operador: solo POS y ventas</li>
                    <li><i class="fa-solid fa-check-circle"></i> Login por celular + PIN seguro</li>
                    <li><i class="fa-solid fa-check-circle"></i> Activación y desactivación de usuarios</li>
                </ul>
            </div>
        </div>

    </div>
</section>

<!-- ═══ PRECIO ════════════════════════════════════════════════════════════ -->
<section class="pricing" id="precios">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="section-label">Precio</div>
                <h2 class="section-title">Un precio tan bajo<br>que da risa</h2>
                <p class="section-sub">
                    Muchos sistemas POS cobran cientos de dólares al mes. Nosotros
                    creemos que la tecnología debe ser accesible para todos los negocios.
                </p>

                <div class="mt-4">
                    <div class="compare-item">
                        <span class="bad"><i class="fa-solid fa-xmark"></i></span>
                        Otros sistemas POS: <strong style="color:rgba(255,255,255,.7);">$30–$80 USD/mes</strong>
                    </div>
                    <div class="compare-item">
                        <span class="bad"><i class="fa-solid fa-xmark"></i></span>
                        Hojas de cálculo: desorden y tiempo perdido
                    </div>
                    <div class="compare-item">
                        <span class="bad"><i class="fa-solid fa-xmark"></i></span>
                        Papel y lápiz: sin reportes, sin control
                    </div>
                    <div class="compare-item" style="margin-top:10px;">
                        <span class="good" style="font-size:1.2rem;"><i class="fa-solid fa-check-circle"></i></span>
                        <strong style="color:#68d391; font-size:1rem;">MiSocio POS: solo 50 Bs/año ✓</strong>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="price-card">
                    <div class="badge-top">🎉 30 días gratis, sin tarjeta</div>
                    <div class="text-center mb-4">
                        <div class="price-amount">
                            <sup>Bs</sup>50<span class="per">/año</span>
                        </div>
                        <div class="price-per-day">Menos de 14 céntimos por día</div>
                    </div>

                    <div class="price-feature"><i class="fa-solid fa-check"></i> Ventas y POS ilimitadas</div>
                    <div class="price-feature"><i class="fa-solid fa-check"></i> Productos y categorías ilimitados</div>
                    <div class="price-feature"><i class="fa-solid fa-check"></i> Usuarios y roles ilimitados</div>
                    <div class="price-feature"><i class="fa-solid fa-check"></i> Turnos y control de caja</div>
                    <div class="price-feature"><i class="fa-solid fa-check"></i> Reportes e historial completo</div>
                    <div class="price-feature"><i class="fa-solid fa-check"></i> Impresión de tickets (con agente)</div>
                    <div class="price-feature"><i class="fa-solid fa-check"></i> Soporte por WhatsApp</div>
                    <div class="price-feature"><i class="fa-solid fa-check"></i> Actualizaciones incluidas</div>

                    <a href="{{ route('login') }}" class="btn-price">
                        <i class="fa-solid fa-store me-2"></i>Empezar gratis ahora
                    </a>
                    <p class="trial-note">30 días de prueba gratuita · Sin tarjeta de crédito</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══ CTA FINAL ══════════════════════════════════════════════════════════ -->
<section class="cta-section">
    <div class="container">
        <img src="{{ asset('assets/images/mascota-saludo.png') }}"
             alt="¡Únete!" class="mascota-cta">
        <div class="section-label text-center">¿Listo para empezar?</div>
        <h2 class="section-title text-center">Tu negocio lo merece.<br>Actívalo hoy gratis.</h2>
        <p class="section-sub text-center mx-auto mt-2 mb-4">
            Configura tu tienda en menos de 2 minutos y empieza a vender desde hoy.
            Sin costos ocultos. Sin letra pequeña.
        </p>
        <div class="text-center">
            <a href="{{ route('login') }}" class="btn-hero-primary" style="font-size:1.05rem; padding:16px 40px;">
                <i class="fa-solid fa-rocket"></i> Activar mi tienda gratis
            </a>
            <div class="mt-3" style="font-size:.82rem; color:var(--text-muted);">
                ¿Tienes preguntas?
                <a href="https://wa.me/59173010688?text=Hola, me interesa MiSocio POS"
                   target="_blank" style="color:var(--brand); font-weight:700; text-decoration:none;">
                    <i class="fa-brands fa-whatsapp"></i> Escríbenos por WhatsApp
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ═══ FOOTER ════════════════════════════════════════════════════════════ -->
<footer>
    <div class="container">
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
            <div>
                <img src="{{ asset('assets/images/logo.png') }}" alt="MiSocio POS" style="height:36px; filter:brightness(0) invert(1) opacity(.7);">
            </div>
            <p class="mb-0">
                &copy; {{ date('Y') }} MiSocio POS &mdash; Hecho con <i class="fa-solid fa-heart" style="color:#fc8181;"></i> por
                <a href="https://wa.me/59173010688" target="_blank">DieguitoSoft</a>
            </p>
            <a href="{{ route('login') }}" style="color:var(--brand); font-weight:700; text-decoration:none;">
                Ingresar al sistema →
            </a>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Navbar scroll effect
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 30);
    });

    // Smooth scroll para anclas
    document.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener('click', e => {
            const target = document.querySelector(link.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
</script>
</body>
</html>
