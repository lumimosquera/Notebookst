<?php
session_start();
require 'model/conexion.php';

if (isset($_SESSION['user_id'])) {
    header('Location: view/home.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Notebookst — Gestión académica</title>
    <link rel="icon" href="asset/img/N.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Mono:wght@400;500&family=Geist:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css">
    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        --bg:        #080c14;
        --surface:   #0d1320;
        --elevated:  #111827;
        --border:    rgba(255,255,255,.08);
        --border-hi: rgba(74,240,200,.22);
        --accent:    #4af0c8;
        --accent-dim:rgba(74,240,200,.09);
        --blue:      #63b3ed;
        --purple:    #a78bfa;
        --text:      #eef0f5;
        --muted:     #8a9ab5;
        --mono:      'DM Mono', monospace;
        --sans:      'Geist', system-ui, sans-serif;
        --serif:     'Instrument Serif', Georgia, serif;
    }

    html { scroll-behavior: smooth; }

    body {
        font-family: var(--sans);
        background: var(--bg);
        color: var(--text);
        min-height: 100vh;
        overflow-x: hidden;
    }

    /* ── Background ── */
    body::before {
        content: '';
        position: fixed; inset: 0; z-index: 0; pointer-events: none;
        background:
            radial-gradient(ellipse 70% 50% at 10% -10%, rgba(74,240,200,.1) 0%, transparent 60%),
            radial-gradient(ellipse 50% 40% at 90% 10%, rgba(167,139,250,.1) 0%, transparent 55%),
            radial-gradient(ellipse 60% 60% at 50% 110%, rgba(99,179,237,.07) 0%, transparent 60%);
    }
    body::after {
        content: '';
        position: fixed; inset: 0; z-index: 0; pointer-events: none;
        background-image:
            linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,.02) 1px, transparent 1px);
        background-size: 100% 44px, 44px 100%;
    }

    /* ── Navbar ── */
    .navbar {
        position: sticky; top: 0; z-index: 100;
        background: rgba(8,12,20,.85);
        border-bottom: 1px solid var(--border);
        backdrop-filter: blur(16px);
        padding: 14px 0;
    }
    .navbar-inner {
        max-width: 1140px; margin: 0 auto; padding: 0 24px;
        display: flex; align-items: center; justify-content: space-between; gap: 16px;
    }
    .nav-logo {
        display: flex; align-items: center; gap: 9px;
        font-family: var(--sans); font-weight: 600; font-size: 15px;
        color: var(--text); text-decoration: none; letter-spacing: .02em;
    }
    .nav-logo-mark {
        width: 28px; height: 28px; border-radius: 8px;
        background: var(--accent); display: flex; align-items: center; justify-content: center;
        font-family: var(--mono); font-size: 13px; font-weight: 700; color: #080c14;
    }
    .nav-links { display: flex; align-items: center; gap: 6px; }
    .nav-link-ghost {
        font-size: 13px; color: var(--muted); padding: 7px 14px;
        border-radius: 8px; text-decoration: none; transition: color .15s, background .15s;
    }
    .nav-link-ghost:hover { color: var(--text); background: rgba(255,255,255,.05); }
    .nav-link-accent {
        font-size: 13px; font-weight: 500; color: #080c14;
        background: var(--accent); padding: 7px 16px; border-radius: 8px;
        text-decoration: none; transition: opacity .15s;
    }
    .nav-link-accent:hover { opacity: .88; }

    /* ── Sections ── */
    .section { position: relative; z-index: 1; }
    .container { max-width: 1140px; margin: 0 auto; padding: 0 24px; }

    /* ── Hero ── */
    .hero {
        padding: 96px 0 80px;
        display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center;
    }
    .hero-eyebrow {
        display: inline-flex; align-items: center; gap: 7px;
        font-family: var(--mono); font-size: 11px; letter-spacing: .12em;
        text-transform: uppercase; color: var(--accent);
        background: var(--accent-dim); border: 1px solid var(--border-hi);
        padding: 5px 12px; border-radius: 999px; margin-bottom: 22px;
    }
    .hero-eyebrow-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--accent); animation: pulse 2s infinite; }
    @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(.8)} }

    .hero h1 {
        font-family: var(--serif); font-size: clamp(2.4rem, 4.5vw, 4rem);
        line-height: 1.05; letter-spacing: -.02em; margin-bottom: 20px; color: var(--text);
    }
    .hero h1 em { font-style: italic; color: var(--accent); }
    .hero-sub {
        font-size: 16px; color: var(--muted); line-height: 1.7;
        max-width: 480px; margin-bottom: 36px;
    }
    .hero-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 40px; }
    .btn-primary-idx {
        display: inline-flex; align-items: center; gap: 7px;
        background: var(--accent); color: #080c14; font-weight: 600; font-size: 14px;
        padding: 11px 22px; border-radius: 10px; text-decoration: none;
        box-shadow: 0 8px 30px rgba(74,240,200,.25); transition: opacity .15s, transform .15s;
    }
    .btn-primary-idx:hover { opacity: .9; transform: translateY(-1px); }
    .btn-ghost-idx {
        display: inline-flex; align-items: center; gap: 7px;
        background: rgba(255,255,255,.05); color: var(--text); font-size: 14px;
        padding: 11px 22px; border-radius: 10px; border: 1px solid var(--border);
        text-decoration: none; transition: border-color .15s, background .15s;
    }
    .btn-ghost-idx:hover { border-color: var(--border-hi); background: rgba(74,240,200,.06); }

    .hero-stats {
        display: flex; flex-wrap: wrap; gap: 20px;
        border-top: 1px solid var(--border); padding-top: 28px;
    }
    .hero-stat { display: flex; flex-direction: column; gap: 3px; }
    .hero-stat-val { font-family: var(--serif); font-size: 22px; color: var(--text); }
    .hero-stat-lbl { font-family: var(--mono); font-size: 10px; color: var(--muted); text-transform: uppercase; letter-spacing: .1em; }

    /* ── Mock UI card ── */
    .hero-visual {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: 18px; overflow: hidden;
        box-shadow: 0 32px 80px rgba(0,0,0,.5), 0 0 0 1px rgba(74,240,200,.06);
    }
    .mock-topbar {
        display: flex; align-items: center; justify-content: space-between;
        padding: 12px 16px; background: var(--elevated);
        border-bottom: 1px solid var(--border);
    }
    .mock-dots { display: flex; gap: 6px; }
    .mock-dot { width: 9px; height: 9px; border-radius: 50%; }
    .mock-title { font-family: var(--mono); font-size: 11px; color: var(--muted); }
    .mock-body { padding: 16px; display: flex; flex-direction: column; gap: 8px; }
    .mock-card {
        background: var(--elevated); border: 1px solid var(--border);
        border-radius: 10px; padding: 11px 13px;
        display: flex; align-items: center; gap: 10px;
    }
    .mock-card-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .mock-card-body { flex: 1; min-width: 0; }
    .mock-card-name { font-size: 12px; font-weight: 500; color: var(--text); margin-bottom: 3px; }
    .mock-card-meta { font-family: var(--mono); font-size: 10px; color: var(--muted); }
    .mock-badge {
        font-family: var(--mono); font-size: 9px; padding: 2px 7px;
        border-radius: 4px; flex-shrink: 0;
    }
    .mock-progress { padding: 12px 16px; border-top: 1px solid var(--border); }
    .mock-progress-label { font-family: var(--mono); font-size: 10px; color: var(--muted); margin-bottom: 6px; display: flex; justify-content: space-between; }
    .mock-progress-bar { height: 4px; background: var(--border); border-radius: 2px; overflow: hidden; }
    .mock-progress-fill { height: 100%; border-radius: 2px; background: var(--accent); }

    /* ── Features ── */
    .features { padding: 80px 0; }
    .section-eyebrow {
        font-family: var(--mono); font-size: 11px; letter-spacing: .12em;
        text-transform: uppercase; color: var(--accent); margin-bottom: 10px;
    }
    .section-title {
        font-family: var(--serif); font-size: clamp(1.8rem, 3vw, 2.5rem);
        line-height: 1.1; color: var(--text); margin-bottom: 14px; letter-spacing: -.02em;
    }
    .section-sub { font-size: 15px; color: var(--muted); max-width: 520px; line-height: 1.7; margin-bottom: 48px; }

    .features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
    .feature-card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: 14px; padding: 22px;
        transition: border-color .2s, transform .2s, box-shadow .2s;
    }
    .feature-card:hover { border-color: var(--border-hi); transform: translateY(-3px); box-shadow: 0 16px 40px rgba(0,0,0,.25); }
    .feature-icon {
        width: 40px; height: 40px; border-radius: 10px;
        background: var(--accent-dim); border: 1px solid var(--border-hi);
        display: flex; align-items: center; justify-content: center;
        color: var(--accent); font-size: 17px; margin-bottom: 14px;
    }
    .feature-card h3 { font-size: 14px; font-weight: 600; color: var(--text); margin-bottom: 7px; }
    .feature-card p { font-size: 13px; color: var(--muted); line-height: 1.6; }

    /* ── CTA ── */
    .cta-section {
        padding: 80px 0;
        text-align: center;
    }
    .cta-box {
        background: var(--surface); border: 1px solid var(--border-hi);
        border-radius: 20px; padding: 56px 40px;
        max-width: 600px; margin: 0 auto;
        box-shadow: 0 0 80px rgba(74,240,200,.06);
    }
    .cta-box h2 { font-family: var(--serif); font-size: clamp(1.7rem, 3vw, 2.2rem); line-height: 1.15; margin-bottom: 14px; }
    .cta-box h2 em { font-style: italic; color: var(--accent); }
    .cta-box p { font-size: 14px; color: var(--muted); margin-bottom: 28px; line-height: 1.7; }

    /* ── Footer ── */
    footer {
        border-top: 1px solid var(--border);
        padding: 24px 0; text-align: center;
        font-family: var(--mono); font-size: 11px; color: var(--muted);
        position: relative; z-index: 1;
    }
    footer a { color: var(--muted); text-decoration: none; }
    footer a:hover { color: var(--accent); }

    /* ── Responsive ── */
    @media (max-width: 900px) {
        .hero { grid-template-columns: 1fr; gap: 40px; padding: 64px 0 56px; }
        .hero-visual { display: none; }
        .features-grid { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 600px) {
        .features-grid { grid-template-columns: 1fr; }
        .cta-box { padding: 36px 20px; }
        .hero { padding: 56px 0 48px; }
    }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar section">
    <div class="navbar-inner">
        <a href="#" class="nav-logo">
            <div class="nav-logo-mark">N</div>
            Notebookst
        </a>
        <div class="nav-links">
            <a href="view/login.php"    class="nav-link-ghost">Iniciar sesión</a>
            <a href="view/registro.php" class="nav-link-accent">Comenzar gratis</a>
        </div>
    </div>
</nav>

<!-- HERO -->
<div class="section">
<div class="container">
<div class="hero">
    <div>
        <div class="hero-eyebrow">
            <span class="hero-eyebrow-dot"></span>
            Gestión académica
        </div>
        <h1>Organiza tu semestre,<br><em>sin perder ninguna entrega.</em></h1>
        <p class="hero-sub">
            Notebookst centraliza tus materias, tareas y fechas límite en un dashboard limpio.
            Visualiza tu progreso, recibe alertas de vencimiento y lleva el control de cada actividad.
        </p>
        <div class="hero-actions">
            <a href="view/registro.php" class="btn-primary-idx">
                <i class="bi bi-arrow-right-circle-fill"></i> Crear cuenta gratis
            </a>
            <a href="view/login.php" class="btn-ghost-idx">
                <i class="bi bi-box-arrow-in-right"></i> Iniciar sesión
            </a>
        </div>
        <div class="hero-stats">
            <div class="hero-stat">
                <span class="hero-stat-val">Materias</span>
                <span class="hero-stat-lbl">por semestre</span>
            </div>
            <div class="hero-stat">
                <span class="hero-stat-val">Tareas</span>
                <span class="hero-stat-lbl">con fechas y enlaces</span>
            </div>
            <div class="hero-stat">
                <span class="hero-stat-val">Calendario</span>
                <span class="hero-stat-lbl">mes · semana · día</span>
            </div>
            <div class="hero-stat">
                <span class="hero-stat-val">Alertas</span>
                <span class="hero-stat-lbl">72 h antes del vencimiento</span>
            </div>
        </div>
    </div>

    <!-- Mock UI -->
    <div class="hero-visual">
        <div class="mock-topbar">
            <div class="mock-dots">
                <div class="mock-dot" style="background:#fc5c7d;"></div>
                <div class="mock-dot" style="background:#f5a623;"></div>
                <div class="mock-dot" style="background:#4af0c8;"></div>
            </div>
            <div class="mock-title">notebookst · dashboard</div>
            <div style="width:9px;"></div>
        </div>
        <div class="mock-body">
            <div class="mock-card">
                <div class="mock-card-dot" style="background:#63b3ed;"></div>
                <div class="mock-card-body">
                    <div class="mock-card-name">📝 Quiz Unidad 3</div>
                    <div class="mock-card-meta">Cálculo diferencial · 16/06/2026</div>
                </div>
                <div class="mock-badge" style="background:rgba(245,166,35,.18);color:#f5a623;">Pendiente</div>
            </div>
            <div class="mock-card">
                <div class="mock-card-dot" style="background:#a78bfa;"></div>
                <div class="mock-card-body">
                    <div class="mock-card-name">🗂 Proyecto final</div>
                    <div class="mock-card-meta">Ing. de software · 25/06/2026</div>
                </div>
                <div class="mock-badge" style="background:rgba(99,179,237,.18);color:#63b3ed;">En progreso</div>
            </div>
            <div class="mock-card">
                <div class="mock-card-dot" style="background:#4af0c8;"></div>
                <div class="mock-card-body">
                    <div class="mock-card-name">📋 Parcial 2</div>
                    <div class="mock-card-meta">Física mecánica · 10/06/2026</div>
                </div>
                <div class="mock-badge" style="background:rgba(74,240,200,.15);color:#4af0c8;">Completada</div>
            </div>
        </div>
        <div class="mock-progress">
            <div class="mock-progress-label">
                <span>Progreso del semestre</span>
                <span style="color:var(--accent);">67%</span>
            </div>
            <div class="mock-progress-bar">
                <div class="mock-progress-fill" style="width:67%;"></div>
            </div>
        </div>
    </div>
</div>
</div>
</div>

<!-- FEATURES -->
<div class="section features">
<div class="container">
    <div class="section-eyebrow">Funcionalidades</div>
    <h2 class="section-title">Todo lo que necesitas<br>para el semestre.</h2>
    <p class="section-sub">Desde crear una materia hasta ver qué entregas tienes esta semana — sin complicaciones.</p>

    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon"><i class="bi bi-journal-bookmark-fill"></i></div>
            <h3>Materias organizadas</h3>
            <p>Crea una materia por cada clase, asígnale un color y gestiona todas sus actividades desde un solo lugar.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon"><i class="bi bi-check2-square"></i></div>
            <h3>Actividades detalladas</h3>
            <p>Tareas, quizzes, parciales, proyectos. Cada actividad tiene nombre, descripción, fecha, hora y enlace directo.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon"><i class="bi bi-calendar3"></i></div>
            <h3>Calendario trimodal</h3>
            <p>Visualiza tus entregas en vista de mes, semana o día. Navega el tiempo y filtra por estado o materia.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon"><i class="bi bi-bell-fill"></i></div>
            <h3>Alertas de vencimiento</h3>
            <p>Recibe notificaciones en el topbar cuando una entrega vence en menos de 72 horas. Nunca más te sorprenda una fecha.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon"><i class="bi bi-bar-chart-fill"></i></div>
            <h3>Estadísticas por materia</h3>
            <p>Mira de un vistazo cuántas actividades tienes pendientes, en progreso o completadas en cada materia.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon"><i class="bi bi-search"></i></div>
            <h3>Búsqueda global</h3>
            <p>Encuentra cualquier tarea o materia desde el buscador del topbar. Resultados instantáneos con acceso directo.</p>
        </div>
    </div>
</div>
</div>

<!-- CTA -->
<div class="section cta-section">
<div class="container">
    <div class="cta-box">
        <h2>Empieza <em>ahora mismo</em><br>y organiza tu semestre.</h2>
        <p>Crear una cuenta es gratis y toma menos de un minuto.<br>Sin tarjeta, sin complicaciones.</p>
        <a href="view/registro.php" class="btn-primary-idx" style="display:inline-flex;">
            <i class="bi bi-arrow-right-circle-fill"></i> Crear cuenta gratis
        </a>
    </div>
</div>
</div>

<!-- FOOTER -->
<footer>
    <div class="container">
        <p>
            &copy; <?= date('Y') ?> <strong style="color:var(--text);">Notebookst</strong> by OwniTech &nbsp;·&nbsp;
            <a href="view/login.php">Iniciar sesión</a> &nbsp;·&nbsp;
            <a href="view/registro.php">Registrarse</a>
        </p>
    </div>
</footer>

</body>
</html>
