<?php
$nota         = (float)($resultadoPrevio['nota'] ?? 0);
$aprobado     = (bool)($resultadoPrevio['aprobado'] ?? false);
$notaMinima   = (float)($examen['nota_minima'] ?? 5.0);
$tituloCurso  = htmlspecialchars($curso['titulo']  ?? 'Curso');
$tituloExamen = htmlspecialchars($examen['titulo'] ?? 'Examen Final');
$nombreUsuario= htmlspecialchars($usuario['nombre'] ?? 'Estudiante');
$fechaEmision = $certificado ? date('d \d\e F \d\e Y', strtotime($certificado['emitido_en'])) : date('d \d\e F \d\e Y');
$codigoCert   = $certificado['codigo'] ?? '';
$notaStr      = number_format($nota, 1);
$notaDisplay  = str_replace('.', ',', $notaStr);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado — <?= $tituloExamen ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Saira:wght@400;500;600;700;800&display=swap&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
    <style>
        :root {
            --mc-green: #6B8F71;
            --mc-green-d: #4a6b50;
            --mc-dark: #1B2336;
            --mc-navy: #0f172a;
            --mc-border: #e5e7eb;
            --mc-soft: #f8fafc;
            --mc-muted: #6b7280;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Saira', sans-serif; background: #f6f6f6; color: var(--mc-dark); margin: 0; }

        .res-wrap {
            max-width: 700px;
            margin: 0 auto;
            padding: 32px 20px 60px;
        }

        /* Resultado card */
        .res-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 20px rgba(0,0,0,.08);
            overflow: hidden;
            margin-bottom: 24px;
        }
        .res-header-ok   { background: linear-gradient(135deg, #166534 0%, #15803d 100%); }
        .res-header-fail { background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 100%); }
        .res-header {
            padding: 32px 36px;
            text-align: center;
            color: #fff;
        }
        .res-icon { font-size: 3rem; margin-bottom: 12px; }
        .res-estado { font-size: 1.4rem; font-weight: 800; margin: 0 0 6px; }
        .res-nota-wrap { display: flex; align-items: baseline; justify-content: center; gap: 4px; margin-top: 16px; }
        .res-nota-num { font-size: 3.5rem; font-weight: 800; line-height: 1; }
        .res-nota-den { font-size: 1.2rem; opacity: .7; }
        .res-body { padding: 24px 32px; }
        .res-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .res-info-box { background: var(--mc-soft); border-radius: 10px; padding: 12px 16px; }
        .res-info-box .label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; color: var(--mc-muted); margin-bottom: 3px; }
        .res-info-box .value { font-size: .95rem; font-weight: 700; color: var(--mc-dark); }
        .res-actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 20px; }
        .btn-primary-mc {
            flex: 1;
            background: var(--mc-green);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: .6rem 1.2rem;
            font-size: .9rem;
            font-weight: 700;
            font-family: 'Saira', sans-serif;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            transition: background .15s;
        }
        .btn-primary-mc:hover { background: var(--mc-green-d); color: #fff; }
        .btn-outline-mc {
            flex: 1;
            background: #fff;
            color: var(--mc-dark);
            border: 1.5px solid var(--mc-border);
            border-radius: 10px;
            padding: .6rem 1.2rem;
            font-size: .9rem;
            font-weight: 700;
            font-family: 'Saira', sans-serif;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            transition: all .15s;
        }
        .btn-outline-mc:hover { border-color: var(--mc-green); color: var(--mc-green); }

        /* ── CERTIFICADO ── */
        .cert-section { margin-bottom: 24px; }
        .cert-section h3 { font-size: .85rem; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; color: var(--mc-muted); margin: 0 0 12px; }

        .certificado {
            background: #fff;
            border: 2px solid #d4af37;
            border-radius: 8px;
            position: relative;
            overflow: hidden;
            font-family: 'Georgia', 'Playfair Display', serif;
        }
        .cert-corner {
            position: absolute;
            width: 80px;
            height: 80px;
            border: 3px solid #d4af37;
            border-radius: 4px;
            opacity: .25;
        }
        .cert-corner.tl { top: 8px; left: 8px; border-right: none; border-bottom: none; }
        .cert-corner.tr { top: 8px; right: 8px; border-left: none; border-bottom: none; }
        .cert-corner.bl { bottom: 8px; left: 8px; border-right: none; border-top: none; }
        .cert-corner.br { bottom: 8px; right: 8px; border-left: none; border-top: none; }

        .cert-inner { padding: 36px 48px; text-align: center; position: relative; z-index: 1; }
        .cert-logo { font-size: .8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 3px; color: var(--mc-green); margin-bottom: 4px; font-family: 'Saira', sans-serif; }
        .cert-rule { border: none; border-top: 1px solid #d4af37; margin: 10px auto; width: 60%; }
        .cert-titulo { font-size: .85rem; color: var(--mc-muted); text-transform: uppercase; letter-spacing: 2px; margin: 12px 0 4px; font-family: 'Saira', sans-serif; }
        .cert-nombre { font-size: 2rem; font-weight: 700; color: var(--mc-dark); margin: 8px 0; line-height: 1.2; }
        .cert-descripcion { font-size: .85rem; color: var(--mc-muted); margin: 10px 0; line-height: 1.6; }
        .cert-curso { font-size: 1.1rem; font-weight: 700; color: var(--mc-navy); margin: 4px 0 14px; }
        .cert-nota-row { display: inline-flex; align-items: center; gap: 16px; margin: 8px 0 16px; }
        .cert-nota-pill { background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 20px; padding: 4px 16px; font-size: .85rem; font-weight: 700; color: #166534; font-family: 'Saira', sans-serif; }
        .cert-footer { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 20px; flex-wrap: wrap; gap: 12px; }
        .cert-firma { text-align: center; }
        .cert-firma-line { border-top: 1px solid var(--mc-dark); width: 140px; margin: 0 auto 4px; }
        .cert-firma p { font-size: .72rem; color: var(--mc-muted); margin: 0; font-family: 'Saira', sans-serif; }
        .cert-codigo { text-align: center; }
        .cert-codigo p { font-size: .62rem; color: var(--mc-muted); margin: 0; font-family: 'Saira', monospace; letter-spacing: 1px; }
        .cert-codigo .code { font-size: .75rem; font-weight: 700; color: var(--mc-dark); letter-spacing: 2px; }

        .cert-actions { display: flex; gap: 10px; margin-top: 12px; flex-wrap: wrap; }

        @media print {
            body > *:not(.cert-print-wrap) { display: none !important; }
            .cert-print-wrap { display: block !important; }
            .certificado { border-color: #d4af37 !important; box-shadow: none !important; }
            @page { margin: 1.5cm; }
        }
        .cert-print-wrap { display: contents; }
    </style>
</head>

<body>
    <?php require __DIR__ . '/../layout/header.php'; ?>

    <div class="res-wrap">

        <!-- Resultado -->
        <div class="res-card">
            <div class="res-header <?= $aprobado ? 'res-header-ok' : 'res-header-fail' ?>">
                <div class="res-icon"><?= $aprobado ? '🏆' : '📝' ?></div>
                <p class="res-estado"><?= $aprobado ? '¡Aprobado!' : 'No aprobado' ?></p>
                <p style="margin:0;font-size:.88rem;opacity:.8;"><?= $tituloExamen ?></p>
                <div class="res-nota-wrap">
                    <span class="res-nota-num"><?= $notaDisplay ?></span>
                    <span class="res-nota-den">/ 10</span>
                </div>
            </div>
            <div class="res-body">
                <div class="res-info-grid">
                    <div class="res-info-box">
                        <div class="label">Calificación</div>
                        <div class="value"><?= $notaDisplay ?> / 10</div>
                    </div>
                    <div class="res-info-box">
                        <div class="label">Nota mínima</div>
                        <div class="value"><?= number_format($notaMinima, 1) ?> / 10</div>
                    </div>
                    <div class="res-info-box">
                        <div class="label">Curso</div>
                        <div class="value" style="font-size:.83rem;"><?= $tituloCurso ?></div>
                    </div>
                    <div class="res-info-box">
                        <div class="label">Estado</div>
                        <div class="value" style="color:<?= $aprobado ? '#166534' : '#991b1b' ?>">
                            <?= $aprobado ? '✅ Superado' : '❌ No superado' ?>
                        </div>
                    </div>
                </div>

                <?php if (!$aprobado): ?>
                    <div style="margin-top:16px;padding:12px 16px;background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;font-size:.85rem;color:#7c2d12;">
                        💡 No has alcanzado la nota mínima. Puedes repasar el material del curso y volver a intentarlo.
                    </div>
                <?php endif; ?>

                <?php if ($aprobado && !empty($tieneExamenPractico)): ?>
                    <div style="margin-top:16px;padding:14px 18px;background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:12px;display:flex;align-items:center;gap:12px">
                        <span style="font-size:1.5rem">🛠️</span>
                        <div style="flex:1">
                            <div style="font-size:.9rem;font-weight:700;color:#1e40af;margin-bottom:2px">Siguiente paso: Examen Práctico</div>
                            <div style="font-size:.8rem;color:#3b82f6">Has superado el test teórico. Ahora debes completar el examen práctico para obtener el certificado.</div>
                        </div>
                        <a href="<?= BASE_URL ?>/index.php?url=examen-practico&curso=<?= (int)$curso['id'] ?>" class="btn-primary-mc" style="flex:none;background:#2563eb;white-space:nowrap">
                            Ir al práctico →
                        </a>
                    </div>
                <?php endif; ?>

                <div class="res-actions">
                    <a href="<?= BASE_URL ?>/index.php?url=detallecurso&id=<?= (int)$curso['id'] ?>" class="btn-outline-mc">
                        ← Volver al curso
                    </a>
                    <?php if (!$aprobado): ?>
                        <a href="<?= BASE_URL ?>/index.php?url=examen&curso=<?= (int)$curso['id'] ?>" class="btn-primary-mc">
                            Repetir examen →
                        </a>
                    <?php elseif (empty($tieneExamenPractico)): ?>
                        <button onclick="imprimirCertificado()" class="btn-primary-mc">
                            🖨️ Imprimir certificado
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Certificado (solo si aprobó) -->
        <?php if ($aprobado && $certificado): ?>
        <div class="cert-section">
            <h3>🏅 Tu certificado</h3>

            <div class="cert-print-wrap" id="certArea">
                <div class="certificado" id="certificadoDoc">
                    <div class="cert-corner tl"></div>
                    <div class="cert-corner tr"></div>
                    <div class="cert-corner bl"></div>
                    <div class="cert-corner br"></div>

                    <div class="cert-inner">
                        <div class="cert-logo">MatrixCoders</div>
                        <hr class="cert-rule">

                        <p class="cert-titulo">Certificado de finalización</p>

                        <p style="font-size:.82rem;color:var(--mc-muted);margin:10px 0 2px;font-family:'Saira',sans-serif;">Se certifica que</p>
                        <p class="cert-nombre"><?= $nombreUsuario ?></p>

                        <p class="cert-descripcion">ha completado satisfactoriamente el curso</p>
                        <p class="cert-curso"><?= $tituloCurso ?></p>

                        <div class="cert-nota-row">
                            <span class="cert-nota-pill">Calificación: <?= $notaDisplay ?> / 10</span>
                        </div>

                        <hr class="cert-rule">

                        <div class="cert-footer">
                            <div class="cert-firma">
                                <div class="cert-firma-line"></div>
                                <p>Dirección académica</p>
                                <p>MatrixCoders</p>
                            </div>
                            <div style="text-align:center;">
                                <p style="font-size:2.2rem;margin:0;">🏆</p>
                                <p style="font-size:.65rem;color:var(--mc-muted);font-family:'Saira',sans-serif;margin:2px 0 0;"><?= $fechaEmision ?></p>
                            </div>
                            <div class="cert-codigo">
                                <p>Código de verificación</p>
                                <p class="code"><?= htmlspecialchars($codigoCert) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="cert-actions">
                <button onclick="imprimirCertificado()" class="btn-primary-mc">
                    🖨️ Guardar como PDF
                </button>
                <a href="<?= BASE_URL ?>/index.php?url=dashboard" class="btn-outline-mc">
                    Ir al Dashboard →
                </a>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <?php require __DIR__ . '/../layout/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function imprimirCertificado() {
            window.print();
        }
    </script>
    <style>
        @media print {
            header, footer, .res-card, .cert-actions, .cert-section > h3 { display: none !important; }
            body { background: #fff !important; }
            .res-wrap { padding: 0 !important; max-width: 100% !important; }
            .cert-print-wrap, #certArea, .certificado { display: block !important; }
            .certificado { border: 2px solid #d4af37 !important; }
        }
    </style>
</body>

</html>
