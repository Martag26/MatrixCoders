<?php
$tituloLeccion = htmlspecialchars($leccion['titulo'] ?? 'Sin título');
$tituloCurso   = htmlspecialchars($curso['titulo']  ?? 'Curso');
$videoUrl      = $leccion['video_url'] ?? null;
$cursoId       = $curso['id'] ?? 0;
$tareas        = $modeloCurso->getTareasByCurso($cursoId);

function ytId(?string $url): ?string
{
    if (!$url) return null;
    preg_match('/(?:v=|youtu\.be\/|embed\/)([a-zA-Z0-9_-]{11})/', $url, $m);
    return $m[1] ?? null;
}
function fmtDurL(?int $min): string
{
    if (!$min || $min <= 0) return '';
    return sprintf('%dh %02dm', intdiv($min, 60), $min % 60);
}
$ytId = ytId($videoUrl);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tituloLeccion ?> — <?= $tituloCurso ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Saira:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        :root {
            --mc-green: #6B8F71;
            --mc-green-d: #4a6b50;
            --mc-navy: #0f172a;
            --mc-dark: #1B2336;
            --mc-border: #e5e7eb;
            --mc-soft: #f8fafc;
            --mc-text: #374151;
            --mc-muted: #6b7280;
            --header-h: 66px;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* Evita el scroll de página — cada columna scrollea por su cuenta */
        html,
        body {
            height: 100%;
            overflow: hidden;
            font-family: 'Saira', sans-serif;
            background: #f6f6f6;
            color: var(--mc-dark);
        }

        /* ── LAYOUT PRINCIPAL ── */
        .leccion-wrap {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 320px;
            height: calc(100vh - var(--header-h));
            max-width: 1280px;
            margin: 0 auto;
            overflow: hidden;
            background: #fff;
        }

        @media(max-width: 900px) {
            .leccion-wrap {
                grid-template-columns: 1fr;
            }

            .temario-sidebar {
                display: none;
            }
        }

        /* ── COLUMNA IZQUIERDA ── */
        .leccion-main {
            overflow-y: auto;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            min-width: 0;
            background: #fff;
        }

        /* ── VIDEO (más estrecho, centrado) ── */
        .video-wrapper {
            background: linear-gradient(180deg, #111827 0%, #10151f 100%);
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 1.25rem 1.5rem;
            flex-shrink: 0;
        }

        .video-container {
            background: #000;
            width: 100%;
            max-width: 900px;
            aspect-ratio: 16/9;
            max-height: 52vh;
            overflow: hidden;
            position: relative;
            border-radius: 14px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, .28);
        }

        .video-container iframe {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }

        .video-placeholder {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .75rem;
            color: #4b5563;
            background: #111;
        }

        .video-placeholder .play-circle {
            width: 72px;
            height: 72px;
            background: rgba(107, 143, 113, .15);
            border: 2px solid rgba(107, 143, 113, .3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: var(--mc-green);
        }

        .video-placeholder p {
            font-size: .9rem;
            color: #6b7280;
        }

        /* ── BARRA NAVEGACIÓN ── */
        .leccion-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .9rem 1.5rem;
            background: var(--mc-soft);
            border-bottom: 1px solid var(--mc-border);
            gap: 1rem;
            flex-shrink: 0;
        }

        .leccion-nav .titulo-wrap {
            flex: 1;
            min-width: 0;
            text-align: center;
        }

        .leccion-nav h2 {
            font-size: .97rem;
            font-weight: 700;
            color: var(--mc-dark);
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .leccion-nav .curso-sub {
            font-size: .78rem;
            color: var(--mc-muted);
        }

        .nav-btn {
            display: flex;
            align-items: center;
            gap: .4rem;
            padding: .45rem .9rem;
            border-radius: 8px;
            font-size: .83rem;
            font-weight: 600;
            font-family: 'Saira', sans-serif;
            text-decoration: none;
            transition: all .15s;
            white-space: nowrap;
            border: 1px solid var(--mc-border);
            background: #fff;
            color: var(--mc-text);
            flex-shrink: 0;
        }

        .nav-btn:hover {
            background: var(--mc-green);
            color: #fff;
            border-color: var(--mc-green);
        }

        .nav-btn.disabled {
            opacity: .35;
            pointer-events: none;
        }

        .nav-btn-primary {
            background: var(--mc-green);
            color: #fff;
            border-color: var(--mc-green);
        }

        .nav-btn-primary:hover {
            background: var(--mc-green-d);
            border-color: var(--mc-green-d);
            color: #fff;
        }

        /* ── TABS ── */
        .leccion-tabs-wrap {
            padding: 0 1.5rem;
            border-bottom: 1px solid var(--mc-border);
            flex-shrink: 0;
            background: #fff;
        }

        .leccion-tabs .nav-link {
            font-family: 'Saira', sans-serif;
            font-weight: 600;
            color: var(--mc-muted);
            border: none;
            border-bottom: 2px solid transparent;
            border-radius: 0;
            padding: .7rem 1rem;
            font-size: .87rem;
            transition: color .15s;
        }

        .leccion-tabs .nav-link.active {
            color: var(--mc-green);
            border-bottom-color: var(--mc-green);
            background: transparent;
        }

        .tab-body {
            padding: 1.6rem 1.5rem 2rem;
            flex: 1;
            max-width: 980px;
            width: 100%;
            margin: 0 auto;
        }

        /* ── INFO TAB ── */
        .info-titulo {
            font-family: Georgia, serif;
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: .75rem;
            color: var(--mc-dark);
        }

        .info-texto {
            color: var(--mc-text);
            line-height: 1.75;
            font-size: .93rem;
        }

        .info-meta {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-top: 1.25rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--mc-border);
        }

        .info-tag {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-size: .8rem;
            background: var(--mc-soft);
            border: 1px solid var(--mc-border);
            border-radius: 20px;
            padding: 4px 12px;
            color: var(--mc-text);
        }

        /* ── NOTEBOOK (Apuntes IA) ── */
        .nb-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        .nb-title {
            font-weight: 700;
            font-size: .95rem;
            margin: 0 0 3px;
        }
        .nb-sub {
            font-size: .8rem;
            color: var(--mc-muted);
            margin: 0;
        }
        .nb-embed-wrap {
            border: 1px solid var(--mc-border);
            border-radius: 12px;
            overflow: hidden;
            background: #f8fafc;
            margin-bottom: 1.25rem;
            position: relative;
        }
        .nb-iframe {
            width: 100%;
            height: 420px;
            border: none;
            display: block;
        }
        .nb-placeholder {
            background: linear-gradient(135deg, #f8fafc 0%, #f0f4ff 100%);
            border: 1.5px dashed #c7d2fe;
            border-radius: 12px;
            padding: 2rem 1.5rem;
            text-align: center;
            margin-bottom: 1.25rem;
        }
        .nb-ph-icon { font-size: 2.4rem; margin-bottom: .75rem; }
        .nb-placeholder h6 { font-size: .93rem; font-weight: 700; margin: 0 0 .5rem; color: var(--mc-dark); }
        .nb-placeholder p  { font-size: .83rem; color: var(--mc-muted); line-height: 1.6; margin: 0; max-width: 420px; margin: 0 auto; }
        .nb-save-section {
            background: var(--mc-soft);
            border: 1px solid var(--mc-border);
            border-radius: 12px;
            padding: 1rem 1.1rem;
        }
        .nb-save-header { margin-bottom: .75rem; }
        .nb-save-title { font-size: .88rem; font-weight: 700; display: block; margin-bottom: 3px; }
        .nb-save-hint  { font-size: .78rem; color: var(--mc-muted); line-height: 1.5; }
        .nb-save-section .notas-area { background: #fff; }

        /* ── NOTAS TAB ── */
        .notas-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .notas-header h6 {
            font-weight: 700;
            font-size: .95rem;
            margin: 0;
        }

        .btn-notebook {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: var(--mc-navy);
            color: #fff;
            font-size: .8rem;
            font-weight: 600;
            font-family: 'Saira', sans-serif;
            padding: .4rem .9rem;
            border-radius: 7px;
            text-decoration: none;
            transition: background .15s;
            border: none;
            cursor: pointer;
        }

        .btn-notebook:hover {
            background: #1e2d4a;
            color: #fff;
        }

        .notas-area {
            width: 100%;
            min-height: 160px;
            border: 1px solid var(--mc-border);
            border-radius: 10px;
            padding: .9rem;
            font-family: 'Saira', sans-serif;
            font-size: .9rem;
            resize: vertical;
            color: var(--mc-dark);
            line-height: 1.6;
        }

        .notas-area:focus {
            outline: 2px solid var(--mc-green);
            border-color: transparent;
        }

        .notas-footer {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-top: .75rem;
        }

        .btn-guardar {
            background: var(--mc-green);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: .45rem 1.1rem;
            font-weight: 600;
            font-size: .85rem;
            font-family: 'Saira', sans-serif;
            cursor: pointer;
            transition: background .15s;
        }

        .btn-guardar:hover {
            background: var(--mc-green-d);
        }

        .notas-saved {
            font-size: .82rem;
            color: var(--mc-green);
            display: none;
        }

        .notas-hint {
            margin-top: .85rem;
            padding: .7rem .9rem;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            font-size: .82rem;
            color: #166534;
            display: flex;
            align-items: flex-start;
            gap: .5rem;
            line-height: 1.5;
        }

        /* ── RESOURCE CARDS ── */
        .res-section { margin-bottom: 1.6rem; }
        .res-section-title { font-size: .75rem; font-weight: 800; text-transform: uppercase; letter-spacing: .6px; color: var(--mc-muted); margin-bottom: .75rem; display: flex; align-items: center; gap: 8px; }
        .res-no-eval-badge { text-transform: none; font-size: .72rem; font-weight: 700; background: #eff6ff; color: #2563eb; padding: 2px 8px; border-radius: 99px; letter-spacing: 0; }
        .res-instructor-note { background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 10px; padding: .9rem 1rem; font-size: .9rem; color: var(--mc-text); line-height: 1.75; white-space: pre-wrap; }
        .res-doc-card { display: flex; align-items: center; gap: 14px; padding: 14px 16px; background: var(--bg, #f8fafc); border: 1.5px solid var(--bdr, #e5e7eb); border-radius: 12px; margin-bottom: 8px; }
        .res-doc-icon { width: 42px; height: 42px; border-radius: 10px; background: #fff; border: 1px solid var(--bdr, #e5e7eb); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .res-doc-info { flex: 1; min-width: 0; }
        .res-doc-name { font-size: .9rem; font-weight: 700; color: var(--mc-dark); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .res-doc-desc { font-size: .78rem; color: var(--mc-muted); margin-top: 2px; }
        .res-doc-type-tag { display: inline-block; font-size: .7rem; font-weight: 700; background: rgba(107,143,113,.12); color: var(--mc-green-d); border-radius: 99px; padding: 2px 8px; margin-top: 4px; text-transform: uppercase; letter-spacing: .3px; }
        .res-doc-actions { display: flex; flex-direction: column; gap: 6px; flex-shrink: 0; }
        @media(min-width: 640px) { .res-doc-actions { flex-direction: row; } }
        .res-btn { display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; border-radius: 7px; font-size: .78rem; font-weight: 700; font-family: 'Saira', sans-serif; cursor: pointer; text-decoration: none; white-space: nowrap; transition: all .15s; }
        .res-btn-cloud { background: #fff; border: 1.5px solid #bfdbfe; color: #1d4ed8; }
        .res-btn-cloud:hover { background: #eff6ff; border-color: #93c5fd; }
        .res-btn-download { background: var(--mc-green); border: 1.5px solid var(--mc-green); color: #fff; }
        .res-btn-download:hover { background: var(--mc-green-d); border-color: var(--mc-green-d); color: #fff; }

        /* ── RECURSO ITEMS (legacy) ── */
        .recurso-item {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .75rem 1rem;
            background: #fff;
            border: 1.5px solid var(--mc-border);
            border-radius: 10px;
            text-decoration: none;
            color: var(--mc-text);
            margin-bottom: .5rem;
            transition: border-color .15s, background .15s;
        }
        .recurso-item:hover { border-color: var(--mc-green); background: #f0fdf4; }
        .recurso-item.actividad:hover { border-color: #2563eb; background: #eff6ff; }
        .recurso-item__icon { font-size: 1.3rem; flex-shrink: 0; }
        .recurso-item__body { flex: 1; min-width: 0; }
        .recurso-item__name { font-size: .9rem; font-weight: 700; }
        .recurso-item__desc { font-size: .78rem; color: var(--mc-muted); margin-top: 1px; }
        .btn-rec-action {
            display: inline-flex; align-items: center; gap: .35rem;
            padding: .38rem .85rem; border: 1px solid var(--mc-border); border-radius: 8px;
            background: #fff; color: var(--mc-text); font-size: .78rem; font-weight: 600;
            font-family: 'Saira', sans-serif; cursor: pointer; transition: all .15s;
        }
        .btn-rec-action:hover { border-color: var(--mc-green); color: var(--mc-green); }
        .btn-rec-action.dark { background: var(--mc-navy); color: #fff; border-color: var(--mc-navy); }
        .btn-rec-action.dark:hover { background: #1e2d4a; border-color: #1e2d4a; }

        /* ── TOAST ── */
        .mc-toast-wrap { position: fixed; bottom: 24px; right: 24px; z-index: 9999; display: flex; flex-direction: column; gap: 8px; pointer-events: none; }
        .mc-toast { background: var(--mc-dark); color: #fff; border-radius: 10px; padding: 12px 18px; font-size: .88rem; font-weight: 600; font-family: 'Saira', sans-serif; box-shadow: 0 4px 20px rgba(0,0,0,.25); opacity: 0; transform: translateY(8px); transition: opacity .2s, transform .2s; pointer-events: none; max-width: 320px; }
        .mc-toast.show { opacity: 1; transform: translateY(0); }
        .mc-toast.success { background: #166534; }
        .mc-toast.error { background: #991b1b; }
        .mc-toast.info { background: #1e40af; }

        /* ── TAREAS TAB ── */
        .tarea-card {
            background: var(--mc-soft);
            border-left: 3px solid var(--mc-green);
            border-radius: 0 9px 9px 0;
            padding: .8rem 1rem;
            margin-bottom: .6rem;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
        }

        .tarea-card .tt {
            font-weight: 700;
            font-size: .9rem;
        }

        .tarea-card .td {
            font-size: .82rem;
            color: var(--mc-muted);
            margin-top: 2px;
        }

        .tfecha {
            white-space: nowrap;
            font-size: .78rem;
            font-weight: 700;
            background: #fff;
            border: 1px solid var(--mc-border);
            border-radius: 6px;
            padding: 3px 9px;
            color: var(--mc-green-d);
            flex-shrink: 0;
        }

        .tfecha.vencida {
            color: #dc2626;
            border-color: #fca5a5;
            background: #fff7f7;
        }

        /* ── SIDEBAR TEMARIO ── */
        .temario-sidebar {
            background: var(--mc-soft);
            border-left: 1px solid var(--mc-border);
            overflow-y: auto;
            overflow-x: hidden;
            min-width: 0;
            height: 100%;
            box-shadow: inset 1px 0 0 rgba(15, 23, 42, .03);
        }

        .temario-head {
            padding: .9rem 1.1rem;
            background: var(--mc-navy);
            color: #fff;
            font-weight: 700;
            font-size: .85rem;
            position: sticky;
            top: 0;
            z-index: 2;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .temario-head small {
            color: #94a3b8;
            font-weight: 400;
            font-size: .75rem;
        }

        /* ── UNIDAD COLAPSABLE ── */
        .t-unidad-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .6rem 1rem;
            font-size: .75rem;
            font-weight: 700;
            color: var(--mc-muted);
            text-transform: uppercase;
            letter-spacing: .5px;
            background: #eef2f7;
            border: none;
            border-top: 1px solid var(--mc-border);
            cursor: pointer;
            text-align: left;
            transition: background .15s;
            font-family: 'Saira', sans-serif;
        }

        .t-unidad-btn:hover {
            background: #e2e8f0;
        }

        .u-meta {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            min-width: 0;
        }

        .u-progress {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 38px;
            padding: 2px 7px;
            border-radius: 999px;
            background: rgba(107, 143, 113, .12);
            color: var(--mc-green-d);
            font-size: .68rem;
            font-weight: 700;
            text-transform: none;
            letter-spacing: 0;
        }

        .t-unidad-btn .u-chevron {
            font-size: .7rem;
            transition: transform .2s;
            flex-shrink: 0;
        }

        .t-unidad-btn.collapsed .u-chevron {
            transform: rotate(-90deg);
        }

        .t-lecciones-list {
            overflow: hidden;
            transition: max-height .25s ease;
        }

        .t-lecciones-list.cerrado {
            max-height: 0 !important;
        }

        /* ── LECCIÓN ITEM ── */
        .t-leccion {
            display: flex;
            align-items: flex-start;
            gap: .55rem;
            padding: .6rem 1rem .6rem 1.3rem;
            border-top: 1px solid var(--mc-border);
            text-decoration: none;
            color: var(--mc-text);
            font-size: .83rem;
            transition: background .15s;
            position: relative;
        }

        .t-leccion:hover {
            background: #e4ebe5;
            color: var(--mc-dark);
        }

        .t-leccion.activa {
            background: #d4e6d6;
            color: var(--mc-green-d);
            font-weight: 700;
            border-left: 3px solid var(--mc-green);
            padding-left: calc(1.3rem - 3px);
        }

        /* Checkbox del sidebar */
        .tl-check {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            border: 2px solid var(--mc-border);
            background: #fff;
            flex-shrink: 0;
            margin-top: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            transition: background .15s, border-color .15s;
        }

        /* Lección activa */
        .t-leccion.activa .tl-check {
            background: var(--mc-green);
            border-color: var(--mc-green);
            color: #fff;
        }

        /* Lección ya vista (pero no activa) */
        .t-leccion.vista .tl-check {
            background: #d1fae5;
            border-color: #6ee7b7;
            color: var(--mc-green-d);
        }

        .t-leccion.vista>span {
            opacity: .82;
        }

        .t-leccion.vista {
            color: var(--mc-text);
        }

        .t-leccion.activa>span {
            opacity: 1;
        }

        .t-leccion span:last-child {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.35;
        }

        /* Volver al curso */
        .volver-curso {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .75rem 1.1rem;
            font-size: .82rem;
            color: var(--mc-muted);
            text-decoration: none;
            border-bottom: 1px solid var(--mc-border);
            transition: color .15s, background .15s;
        }

        .volver-curso:hover {
            background: #eef2f7;
            color: var(--mc-dark);
        }

        @media(max-width: 1200px) {
            .leccion-wrap {
                max-width: 1180px;
                grid-template-columns: minmax(0, 1fr) 296px;
            }

            .video-container {
                max-width: 820px;
            }
        }
    </style>
</head>

<body>

    <?php require __DIR__ . '/../layout/header.php'; ?>

    <div class="leccion-wrap">

        <!-- ── COLUMNA PRINCIPAL ── -->
        <div class="leccion-main">

            <!-- VIDEO (un solo div, centrado, más estrecho) -->
            <div class="video-wrapper">
                <div class="video-container">
                    <?php if ($ytId): ?>
                        <iframe
                            id="ytPlayer"
                            src="https://www.youtube.com/embed/<?= htmlspecialchars($ytId) ?>?rel=0&modestbranding=1&color=white&enablejsapi=1"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                    <?php else: ?>
                        <div class="video-placeholder">
                            <div class="play-circle">▶</div>
                            <p>Sin vídeo disponible para esta lección</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- BARRA NAVEGACIÓN -->
            <div class="leccion-nav">
                <?php if ($leccionAnterior): ?>
                    <a class="nav-btn"
                        href="<?= BASE_URL ?>/index.php?url=leccion&id=<?= $leccionAnterior['id'] ?>">
                        ← Anterior
                    </a>
                <?php else: ?>
                    <span class="nav-btn disabled">← Anterior</span>
                <?php endif; ?>

                <div class="titulo-wrap">
                    <h2><?= $tituloLeccion ?></h2>
                    <div class="curso-sub">📚 <?= $tituloCurso ?></div>
                </div>

                <?php if ($leccionSiguiente): ?>
                    <a class="nav-btn nav-btn-primary"
                        href="<?= BASE_URL ?>/index.php?url=leccion&id=<?= $leccionSiguiente['id'] ?>">
                        Siguiente →
                    </a>
                <?php else: ?>
                    <?php if ($tieneExamen): ?>
                        <a class="nav-btn nav-btn-primary"
                            href="<?= BASE_URL ?>/index.php?url=examen&curso=<?= $cursoId ?>">
                            Ir al Examen →
                        </a>
                    <?php else: ?>
                        <a class="nav-btn nav-btn-primary"
                            href="<?= BASE_URL ?>/index.php?url=detallecurso&id=<?= $cursoId ?>">
                            Finalizar ✓
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- TABS -->
            <div class="leccion-tabs-wrap">
                <ul class="nav leccion-tabs">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-info">
                            Información
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-recursos">
                            Recursos
                            <?php
                            $totalRecursos = count($recursosInstructor ?? []);
                            if ($totalRecursos > 0): ?>
                            <span style="display:inline-block;background:#6B8F71;color:#fff;font-size:.65rem;font-weight:700;border-radius:99px;padding:0 5px;margin-left:4px;line-height:16px"><?= $totalRecursos ?></span>
                            <?php endif; ?>
                        </button>
                    </li>
                </ul>
            </div>

            <!-- TAB CONTENT -->
            <div class="tab-content tab-body">

                <!-- INFORMACIÓN -->
                <div class="tab-pane fade show active" id="tab-info">
                    <p class="info-titulo"><?= $tituloLeccion ?></p>
                    <p class="info-texto">
                        <?= $curso['descripcion']
                            ? nl2br(htmlspecialchars($curso['descripcion']))
                            : 'En esta lección aprenderás los conceptos fundamentales de este tema. Sigue el vídeo y toma notas de los puntos más importantes para consolidar tu aprendizaje.' ?>
                    </p>
                    <p class="info-texto" style="margin-top:.85rem;">
                        Recuerda que puedes pausar el vídeo en cualquier momento, tomar apuntes en la pestaña correspondiente
                        y retomar la lección cuando quieras. Tu progreso se registra al finalizar el vídeo.
                    </p>

                    <div class="info-meta">
                        <span class="info-tag">📚 <?= $tituloCurso ?></span>
                        <?php if (!empty($unidad['titulo'])): ?>
                            <span class="info-tag">📂 <?= htmlspecialchars($unidad['titulo']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($leccion['orden'])): ?>
                            <span class="info-tag">🔢 Lección <?= $leccion['orden'] ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if ($ytId): ?>
                    <div id="video-progress-bar" style="margin-top:1.2rem;padding:.75rem 1rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:9px;display:flex;align-items:center;gap:.75rem;font-size:.83rem;color:#166534;">
                        <span id="vp-icon">⏳</span>
                        <span id="vp-msg">Mira el vídeo hasta el final para marcar la lección como completada.</span>
                        <button id="btn-marcar-manual" onclick="marcarVistaManual()" style="margin-left:auto;padding:.3rem .8rem;border:1px solid #86efac;border-radius:6px;background:#fff;color:#166534;font-size:.78rem;font-weight:700;cursor:pointer;font-family:'Saira',sans-serif;">Marcar manualmente</button>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- RECURSOS — solo documentos del CRM -->
                <div class="tab-pane fade" id="tab-recursos">
                    <?php
                    $recursosInstructor = $recursosInstructor ?? [];
                    $apuntesInstructor  = $leccion['apuntes'] ?? '';
                    $tiposIconSvg = [
                        'pdf'      => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><path stroke-linecap="round" d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>',
                        'doc'      => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><path stroke-linecap="round" d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>',
                        'zip'      => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><path stroke-linecap="round" d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/></svg>',
                        'link'     => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6B8F71" stroke-width="2"><path stroke-linecap="round" d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path stroke-linecap="round" d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>',
                        'actividad'=> '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2"><path stroke-linecap="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 8h3m-3 4h3m-6-4h.01m0 4h.01"/></svg>',
                        'video'    => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>',
                    ];
                    $tipoLabel = ['pdf'=>'PDF','doc'=>'Documento','zip'=>'Archivo ZIP','link'=>'Enlace','actividad'=>'Actividad práctica','video'=>'Vídeo'];
                    $tipoColors = ['pdf'=>'#fef2f2','doc'=>'#eff6ff','zip'=>'#fffbeb','link'=>'#f0fdf4','actividad'=>'#faf5ff','video'=>'#f0f9ff'];
                    $tipoBorders = ['pdf'=>'#fecaca','doc'=>'#bfdbfe','zip'=>'#fde68a','link'=>'#bbf7d0','actividad'=>'#ddd6fe','video'=>'#bae6fd'];

                    $docRecursos = array_filter($recursosInstructor, fn($r) => $r['tipo'] !== 'actividad');
                    $actRecursos = array_filter($recursosInstructor, fn($r) => $r['tipo'] === 'actividad');
                    $hayContenido = $apuntesInstructor || !empty($recursosInstructor);
                    ?>

                    <?php if ($apuntesInstructor): ?>
                    <section class="res-section">
                        <div class="res-section-title">Notas del instructor</div>
                        <div class="res-instructor-note"><?= nl2br(htmlspecialchars($apuntesInstructor)) ?></div>
                    </section>
                    <?php endif; ?>

                    <?php if (!empty($docRecursos)): ?>
                    <section class="res-section">
                        <div class="res-section-title">Material de la lección</div>
                        <?php foreach ($docRecursos as $rec):
                            $svgIcon = $tiposIconSvg[$rec['tipo']] ?? $tiposIconSvg['link'];
                            $bg      = $tipoColors[$rec['tipo']] ?? '#f8fafc';
                            $border  = $tipoBorders[$rec['tipo']] ?? '#e5e7eb';
                            $label   = $tipoLabel[$rec['tipo']] ?? ucfirst($rec['tipo']);
                        ?>
                        <div class="res-doc-card" style="--bg:<?= $bg ?>;--bdr:<?= $border ?>">
                            <div class="res-doc-icon"><?= $svgIcon ?></div>
                            <div class="res-doc-info">
                                <div class="res-doc-name"><?= htmlspecialchars($rec['nombre']) ?></div>
                                <?php if ($rec['descripcion']): ?>
                                <div class="res-doc-desc"><?= htmlspecialchars($rec['descripcion']) ?></div>
                                <?php endif; ?>
                                <span class="res-doc-type-tag"><?= $label ?></span>
                            </div>
                            <div class="res-doc-actions">
                                <button class="res-btn res-btn-cloud" title="Guardar en mi nube"
                                        onclick="addToCloud(<?= $rec['id'] ?>, '<?= addslashes(htmlspecialchars($rec['nombre'])) ?>', '<?= addslashes(htmlspecialchars($rec['url_o_ruta'])) ?>')">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                    Añadir a mi nube
                                </button>
                                <a class="res-btn res-btn-download" href="<?= htmlspecialchars($rec['url_o_ruta']) ?>" target="_blank"
                                   <?= $rec['descargable'] ? 'download' : '' ?>>
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Descargar
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </section>
                    <?php endif; ?>

                    <?php if (!empty($actRecursos)): ?>
                    <section class="res-section">
                        <div class="res-section-title">Actividades de práctica <span class="res-no-eval-badge">No evaluables</span></div>
                        <?php foreach ($actRecursos as $rec): ?>
                        <div class="res-doc-card" style="--bg:#faf5ff;--bdr:#ddd6fe">
                            <div class="res-doc-icon"><?= $tiposIconSvg['actividad'] ?></div>
                            <div class="res-doc-info">
                                <div class="res-doc-name"><?= htmlspecialchars($rec['nombre']) ?></div>
                                <?php if ($rec['descripcion']): ?>
                                <div class="res-doc-desc"><?= htmlspecialchars($rec['descripcion']) ?></div>
                                <?php endif; ?>
                                <span class="res-doc-type-tag" style="background:#f3e8ff;color:#7c3aed">Actividad práctica</span>
                            </div>
                            <div class="res-doc-actions">
                                <button class="res-btn res-btn-cloud" onclick="addToCloud(<?= $rec['id'] ?>, '<?= addslashes(htmlspecialchars($rec['nombre'])) ?>', '<?= addslashes(htmlspecialchars($rec['url_o_ruta'])) ?>')">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                    Añadir a mi nube
                                </button>
                                <a class="res-btn res-btn-download" href="<?= htmlspecialchars($rec['url_o_ruta']) ?>" target="_blank">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Abrir
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </section>
                    <?php endif; ?>

                    <?php if (!$hayContenido): ?>
                    <div style="text-align:center;padding:3rem 1rem;color:var(--mc-muted)">
                        <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="display:block;margin:0 auto 14px;opacity:.35"><path stroke-linecap="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p style="font-size:.92rem;font-weight:600;margin:0 0 4px">Sin recursos para esta lección</p>
                        <p style="font-size:.82rem;margin:0">El instructor no ha añadido material todavía.</p>
                    </div>
                    <?php endif; ?>
                </div>

            </div><!-- /tab-content -->
        </div><!-- /leccion-main -->

        <!-- ── SIDEBAR TEMARIO ── -->
        <aside class="temario-sidebar">
            <div class="temario-head">
                <span>Contenido del curso</span>
                <small>
                    <?php
                    $totalLec = array_sum(array_map(fn($u) => count($u['lecciones'] ?? []), $unidades));
                    echo $totalLec . ' lecciones';
                    ?>
                </small>
            </div>

            <a href="<?= BASE_URL ?>/index.php?url=detallecurso&id=<?= $cursoId ?>" class="volver-curso">
                ← Volver al curso
            </a>

            <?php foreach ($unidades as $uIdx => $u):
                /* La unidad que contiene la lección activa empieza abierta; el resto, cerradas */
                $tieneActiva = false;
                $vistasUnidad = 0;
                foreach (($u['lecciones'] ?? []) as $lec) {
                    if (isset($leccionesVistas[$lec['id']])) {
                        $vistasUnidad++;
                    }
                    if ($lec['id'] == $leccion['id']) {
                        $tieneActiva = true;
                    }
                }
                $unidadId = 'unidad-' . $uIdx;
                $totalUnidad = count($u['lecciones'] ?? []);
            ?>
                <!-- Cabecera colapsable -->
                <button
                    class="t-unidad-btn <?= $tieneActiva ? '' : 'collapsed' ?>"
                    data-target="<?= $unidadId ?>"
                    aria-expanded="<?= $tieneActiva ? 'true' : 'false' ?>">
                    <span><?= htmlspecialchars($u['titulo'] ?? 'Unidad') ?></span>
                    <span class="u-meta">
                        <span class="u-progress"><?= $vistasUnidad ?>/<?= $totalUnidad ?></span>
                        <span class="u-chevron">▼</span>
                    </span>
                </button>

                <!-- Lista de lecciones -->
                <div class="t-lecciones-list <?= $tieneActiva ? '' : 'cerrado' ?>" id="<?= $unidadId ?>">
                    <?php foreach (($u['lecciones'] ?? []) as $lec):
                        $esActiva = $lec['id'] == $leccion['id'];
                        $esVista = isset($leccionesVistas[$lec['id']]);
                    ?>
                        <a href="<?= BASE_URL ?>/index.php?url=leccion&id=<?= $lec['id'] ?>"
                            class="t-leccion <?= $esActiva ? 'activa' : '' ?> <?= $esVista ? 'vista' : '' ?>">
                            <div class="tl-check"><?= ($esActiva || $esVista) ? '✓' : '' ?></div>
                            <span><?= htmlspecialchars($lec['titulo'] ?? 'Lección') ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>

            <?php endforeach; ?>

            <?php if ($tieneExamen): ?>
            <!-- Evaluación final -->
            <div style="border-top:1px solid var(--mc-border);padding:.85rem 1.1rem;background:var(--mc-soft);">
                <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--mc-muted);margin-bottom:.6rem;">Evaluación final</div>

                <?php
                $testAprobado = !empty($resultadoExamenTest['aprobado']);
                $testHecho    = $resultadoExamenTest !== null;
                ?>

                <a href="<?= BASE_URL ?>/index.php?url=examen&curso=<?= $cursoId ?>"
                   style="display:flex;align-items:center;gap:.6rem;padding:.55rem .75rem;border-radius:9px;text-decoration:none;font-size:.83rem;font-weight:600;margin-bottom:.4rem;
                          background:<?= $testAprobado ? '#f0fdf4' : ($testHecho ? '#fff7ed' : '#fff') ?>;
                          color:<?= $testAprobado ? '#166534' : ($testHecho ? '#7c2d12' : 'var(--mc-dark)') ?>;
                          border:1.5px solid <?= $testAprobado ? '#86efac' : ($testHecho ? '#fed7aa' : 'var(--mc-border)') ?>;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 8h3m-3 4h3m-6-4h.01m0 4h.01"/></svg>
                    Examen tipo test
                    <?php if ($testAprobado): ?>
                        <span style="margin-left:auto;font-size:.7rem;background:#dcfce7;color:#166534;border-radius:99px;padding:1px 7px"><?= number_format((float)$resultadoExamenTest['nota'],1) ?>/10</span>
                    <?php elseif ($testHecho): ?>
                        <span style="margin-left:auto;font-size:.7rem;background:#fef3c7;color:#92400e;border-radius:99px;padding:1px 7px">Repetir</span>
                    <?php endif; ?>
                </a>

                <?php if (!empty($tieneExamenPractico)): ?>
                <a href="<?= BASE_URL ?>/index.php?url=examen-practico&curso=<?= $cursoId ?>"
                   style="display:flex;align-items:center;gap:.6rem;padding:.55rem .75rem;border-radius:9px;text-decoration:none;font-size:.83rem;font-weight:600;background:#fff;color:var(--mc-dark);border:1.5px solid var(--mc-border);">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    Examen práctico
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        </aside>

    </div><!-- /leccion-wrap -->

    <!-- Toast container -->
    <div class="mc-toast-wrap" id="mcToastWrap"></div>

    <!-- SIN FOOTER — la vista de lección ocupa todo el viewport -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($ytId): ?>
    <script src="https://www.youtube.com/iframe_api"></script>
    <?php endif; ?>
    <script>
        const BASE_URL = '<?= BASE_URL ?>';
        const LECCION_ID = <?= (int)$leccion['id'] ?>;
        const TIENE_VIDEO = <?= $ytId ? 'true' : 'false' ?>;

        /* ── Acordeón del sidebar ── */
        document.querySelectorAll('.t-unidad-btn').forEach(btn => {
            const lista = document.getElementById(btn.dataset.target);
            if (!lista.classList.contains('cerrado')) {
                lista.style.maxHeight = lista.scrollHeight + 'px';
            }
            btn.addEventListener('click', () => {
                const abierto = !lista.classList.contains('cerrado');
                if (abierto) {
                    lista.style.maxHeight = lista.scrollHeight + 'px';
                    requestAnimationFrame(() => {
                        lista.style.maxHeight = '0';
                        lista.classList.add('cerrado');
                        btn.classList.add('collapsed');
                        btn.setAttribute('aria-expanded', 'false');
                    });
                } else {
                    lista.classList.remove('cerrado');
                    lista.style.maxHeight = lista.scrollHeight + 'px';
                    btn.classList.remove('collapsed');
                    btn.setAttribute('aria-expanded', 'true');
                    lista.addEventListener('transitionend', () => {
                        lista.style.maxHeight = 'none';
                    }, { once: true });
                }
            });
        });

        /* ── Toast profesional ── */
        function mcToast(msg, type = 'default', duration = 3000) {
            const wrap  = document.getElementById('mcToastWrap');
            const toast = document.createElement('div');
            toast.className = 'mc-toast' + (type !== 'default' ? ' ' + type : '');
            toast.textContent = msg;
            wrap.appendChild(toast);
            requestAnimationFrame(() => { requestAnimationFrame(() => toast.classList.add('show')); });
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 250);
            }, duration);
        }

        /* ── Añadir recurso a la nube ── */
        async function addToCloud(id, nombre, url) {
            const btn = event.currentTarget;
            const origTxt = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '…';
            try {
                const fd = new FormData();
                fd.append('accion', 'guardar_en_nube');
                fd.append('nombre', nombre);
                fd.append('url',    url);
                const res = await fetch(BASE_URL + '/index.php?url=leccion&id=' + LECCION_ID, { method: 'POST', body: fd }).then(r => r.json());
                if (res.ok) {
                    mcToast('Añadido a tu nube correctamente', 'success');
                    btn.innerHTML = '✓ En tu nube';
                    btn.style.background = '#f0fdf4';
                    btn.style.borderColor = '#86efac';
                    btn.style.color = '#166534';
                } else {
                    mcToast(res.error || 'No se pudo guardar en la nube', 'error');
                    btn.innerHTML = origTxt;
                    btn.disabled = false;
                }
            } catch(e) {
                mcToast('Error de conexión', 'error');
                btn.innerHTML = origTxt;
                btn.disabled = false;
            }
        }

        /* ── Contador de caracteres (compatibilidad) ── */
        const notasArea  = null; // textarea eliminado — recursos solo desde CRM
        const notasChars = null;
        if (notasArea) {
            notasArea.addEventListener('input', () => {
                const preview = document.getElementById('recursos-preview');
                if (preview) preview.textContent = notasArea.value || '';
            });
        }

        /* ── Guardar apuntes via AJAX ── */
        /* guardarNota / descargarTxt / imprimirPDF eliminados — recursos solo desde CRM */
        if (notasArea) {
            notasArea.addEventListener('input', () => {
                clearTimeout(saveTimer);
                saveTimer = setTimeout(() => guardarNota(false), 30000);
            });
        }

        /* ── YouTube IFrame API: marcar vista al terminar el vídeo ── */
        let leccionMarcada = false;
        function marcarVista() {
            if (leccionMarcada) return;
            leccionMarcada = true;
            fetch(BASE_URL + '/index.php?url=leccion&id=' + LECCION_ID, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'accion=marcar_vista'
            })
            .then(r => r.json())
            .then(() => {
                const bar = document.getElementById('video-progress-bar');
                const icon = document.getElementById('vp-icon');
                const msg = document.getElementById('vp-msg');
                const btn = document.getElementById('btn-marcar-manual');
                if (bar) { bar.style.background = '#dcfce7'; bar.style.borderColor = '#86efac'; }
                if (icon) icon.textContent = '✅';
                if (msg) msg.textContent = '¡Lección completada! El progreso se ha registrado.';
                if (btn) btn.style.display = 'none';
                document.querySelectorAll('.t-leccion.activa .tl-check').forEach(el => {
                    el.textContent = '✓';
                });
            });
        }
        function marcarVistaManual() { marcarVista(); }

        <?php if ($ytId): ?>
        var ytPlayer;
        function onYouTubeIframeAPIReady() {
            ytPlayer = new YT.Player('ytPlayer', {
                events: { 'onStateChange': onYTStateChange }
            });
        }
        function onYTStateChange(event) {
            if (event.data === YT.PlayerState.ENDED) {
                marcarVista();
            }
        }
        <?php endif; ?>

    </script>
    <style>
        @media print {
            body > *:not(#pdf-print-area) { display: none !important; }
            #pdf-print-area { display: block !important; }
        }
    </style>
</body>

</html>
