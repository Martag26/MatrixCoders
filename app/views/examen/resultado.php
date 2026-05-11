<?php
// Variables inyectadas por ExamenController / CursoCompletadoController vía require
$curso                = $curso                ?? [];
$examen               = $examen               ?? [];
$usuario              = $usuario              ?? [];
$resultadoPrevio      = $resultadoPrevio      ?? [];
$certificado          = $certificado          ?? null;
$primeraLeccionId     = $primeraLeccionId     ?? 0;
$tieneExamenPractico  = $tieneExamenPractico  ?? false;
$notaCertificado      = $notaCertificado      ?? null;
$practicoEntregado    = $practicoEntregado    ?? false;
$practicoRevisado     = $practicoRevisado     ?? false;
$notaMediaPractico    = $notaMediaPractico    ?? null;
$notasEntregables     = $notasEntregables     ?? [];
$mediaEntregables     = $mediaEntregables     ?? null;
$notaFinalPonderada   = $notaFinalPonderada   ?? null;

$nota              = (float)($resultadoPrevio['nota'] ?? 0);
$aprobado          = (bool)($resultadoPrevio['aprobado'] ?? false);
$notaMinima        = (float)($examen['nota_minima'] ?? 5.0);
$intentosUsados    = (int)($resultadoPrevio['intentos'] ?? 0);
$maxIntentos       = 2;
$intentosRestantes = max(0, $maxIntentos - $intentosUsados);
$tituloCurso  = htmlspecialchars($curso['titulo']  ?? 'Curso');
$tituloExamen = htmlspecialchars($examen['titulo'] ?? 'Examen Final');
$nombreUsuario= htmlspecialchars($usuario['nombre'] ?? 'Estudiante');
$fechaEmision = $certificado ? date('d \d\e F \d\e Y', strtotime($certificado['emitido_en'])) : date('d \d\e F \d\e Y');
$codigoCert   = $certificado['codigo'] ?? '';
$notaStr      = number_format($nota, 1);
$notaDisplay  = str_replace('.', ',', $notaStr);
// Nota a mostrar en el certificado: si hay práctico aprobado, usar su nota media
$notaCertStr     = number_format($notaCertificado ?? $nota, 1);
$notaCertDisplay = str_replace('.', ',', $notaCertStr);
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
                    <?php if ($intentosRestantes > 0): ?>
                    <div style="margin-top:16px;padding:12px 16px;background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;font-size:.85rem;color:#7c2d12;">
                        💡 No has alcanzado la nota mínima. Te queda <strong><?= $intentosRestantes ?> intento<?= $intentosRestantes > 1 ? 's' : '' ?></strong>. Repasa el material del curso antes de volver a intentarlo.
                    </div>
                    <?php else:
                        $planActual = $_SESSION['usuario_plan'] ?? 'gratuito';
                        $esPlanIndividual = !in_array($planActual, ['estudiantes', 'empresas']);
                        $esCursoDePago = (float)($curso['precio'] ?? 0) > 0;
                    ?>
                    <div style="margin-top:16px;padding:16px 18px;background:#fef2f2;border:1px solid #fecaca;border-radius:12px;font-size:.85rem;color:#7f1d1d">
                        <div style="font-weight:800;font-size:.9rem;margin-bottom:6px">❌ Intentos agotados</div>
                        <?php if ($esPlanIndividual && $esCursoDePago): ?>
                            Lo sentimos, has utilizado los <?= $maxIntentos ?> intentos disponibles sin superar la nota mínima. <strong>Para poder obtener el certificado deberás volver a adquirir el curso.</strong>
                            <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap">
                                <a href="<?= BASE_URL ?>/index.php?url=detallecurso&id=<?= (int)$curso['id'] ?>" style="display:inline-flex;align-items:center;gap:6px;background:#ef4444;color:#fff;border-radius:8px;padding:8px 16px;font-size:.82rem;font-weight:700;text-decoration:none">
                                    🛒 Volver a adquirir el curso
                                </a>
                            </div>
                        <?php else: ?>
                            Has utilizado los <?= $maxIntentos ?> intentos disponibles sin superar la nota mínima.
                            Para volver a intentarlo debes <strong>matricularte de nuevo en el curso</strong>. Contacta con un administrador para recuperar el acceso.
                            <div style="margin-top:10px">
                                <a href="<?= BASE_URL ?>/index.php?url=detallecurso&id=<?= (int)$curso['id'] ?>" style="display:inline-flex;align-items:center;gap:6px;background:#ef4444;color:#fff;border-radius:8px;padding:7px 14px;font-size:.82rem;font-weight:700;text-decoration:none">
                                    Ver ficha del curso →
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($aprobado && !empty($tieneExamenPractico)): ?>
                    <?php if ($practicoRevisado && $notaMediaPractico !== null): ?>
                        <?php /* Práctico corregido: mostrar desglose de notas */ ?>
                    <?php elseif ($practicoEntregado): ?>
                        <div style="margin-top:16px;padding:14px 18px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:12px;display:flex;align-items:center;gap:12px">
                            <span style="font-size:1.5rem">⏳</span>
                            <div>
                                <div style="font-size:.9rem;font-weight:700;color:#166534;margin-bottom:2px">Examen práctico entregado</div>
                                <div style="font-size:.8rem;color:#16a34a">Tu entrega está siendo revisada. Recibirás una notificación cuando esté corregida.</div>
                            </div>
                        </div>
                    <?php else: ?>
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
                <?php endif; ?>

                <?php
                $mostrarDesglose = $aprobado && (!empty($notasEntregables) || ($practicoRevisado && $notaMediaPractico !== null));
                $hayEntregables  = !empty($notasEntregables);
                // Pesos según si hay entregables calificados
                $pesoTest  = $hayEntregables ? 20 : 40;
                $pesoPrac  = $hayEntregables ? 50 : 60;
                ?>
                <?php if ($mostrarDesglose): ?>
                    <div style="margin-top:16px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
                        <div style="padding:12px 16px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;gap:8px;">
                            <span style="font-size:1rem">📊</span>
                            <span style="font-size:.85rem;font-weight:800;color:var(--mc-dark);text-transform:uppercase;letter-spacing:.4px;">Desglose de calificaciones</span>
                        </div>
                        <div style="padding:12px 16px;display:grid;gap:8px;">

                            <!-- Teórico -->
                            <div style="display:flex;justify-content:space-between;align-items:center;font-size:.85rem;">
                                <span style="color:var(--mc-muted);">
                                    📝 Examen teórico
                                    <span style="font-size:.72rem;background:#e5e7eb;border-radius:20px;padding:1px 7px;margin-left:4px;"><?= $pesoTest ?>%</span>
                                </span>
                                <span style="font-weight:700;color:var(--mc-dark);"><?= $notaDisplay ?> / 10</span>
                            </div>

                            <!-- Entregables -->
                            <?php if ($hayEntregables): ?>
                                <?php foreach ($notasEntregables as $ne): ?>
                                    <div style="display:flex;justify-content:space-between;align-items:center;font-size:.85rem;padding-left:12px;border-left:2px solid #e5e7eb;">
                                        <span style="color:var(--mc-muted);">📁 <?= htmlspecialchars($ne['titulo']) ?></span>
                                        <span style="font-weight:700;color:var(--mc-dark);"><?= number_format((float)$ne['nota'], 1) ?> / 10</span>
                                    </div>
                                <?php endforeach; ?>
                                <div style="display:flex;justify-content:space-between;align-items:center;font-size:.85rem;">
                                    <span style="color:var(--mc-muted);">
                                        📁 Media entregables
                                        <span style="font-size:.72rem;background:#e5e7eb;border-radius:20px;padding:1px 7px;margin-left:4px;">30%</span>
                                    </span>
                                    <span style="font-weight:700;color:var(--mc-dark);"><?= str_replace('.', ',', number_format((float)$mediaEntregables, 1)) ?> / 10</span>
                                </div>
                            <?php endif; ?>

                            <!-- Práctico -->
                            <?php if ($practicoRevisado && $notaMediaPractico !== null): ?>
                                <div style="display:flex;justify-content:space-between;align-items:center;font-size:.85rem;">
                                    <span style="color:var(--mc-muted);">
                                        🛠️ Examen práctico
                                        <span style="font-size:.72rem;background:#e5e7eb;border-radius:20px;padding:1px 7px;margin-left:4px;"><?= $pesoPrac ?>%</span>
                                    </span>
                                    <span style="font-weight:700;color:var(--mc-dark);"><?= str_replace('.', ',', number_format($notaMediaPractico, 1)) ?> / 10</span>
                                </div>
                                <hr style="border:none;border-top:1.5px solid #e5e7eb;margin:4px 0;">
                                <div style="display:flex;justify-content:space-between;align-items:center;">
                                    <span style="font-size:.9rem;font-weight:800;color:var(--mc-dark);">🏆 Nota final</span>
                                    <span style="font-size:1.15rem;font-weight:800;color:#166534;"><?= str_replace('.', ',', number_format($notaFinalPonderada, 1)) ?> / 10</span>
                                </div>
                                <p style="font-size:.72rem;color:var(--mc-muted);margin:0;">
                                    <?php if ($hayEntregables): ?>
                                        Test (20%) + Entregables (30%) + Práctico (50%)
                                    <?php else: ?>
                                        Test (40%) + Práctico (60%) — sin entregables calificados
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>

                        </div>
                    </div>
                <?php endif; ?>

                <div class="res-actions">
                    <?php
                    $volverUrl = !empty($primeraLeccionId)
                        ? BASE_URL . '/index.php?url=leccion&id=' . $primeraLeccionId
                        : BASE_URL . '/index.php?url=detallecurso&id=' . (int)$curso['id'];
                    ?>
                    <a href="<?= $volverUrl ?>" class="btn-outline-mc">
                        ← Volver al curso
                    </a>
                    <?php if (!$aprobado && $intentosRestantes > 0): ?>
                        <a href="<?= BASE_URL ?>/index.php?url=examen&curso=<?= (int)$curso['id'] ?>" class="btn-primary-mc">
                            Repetir examen →
                        </a>
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
                            <span class="cert-nota-pill">Calificación: <?= $notaCertDisplay ?> / 10</span>
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
                <button id="btn-pdf" onclick="descargarPDF()" class="btn-primary-mc">
                    ⬇️ Descargar PDF
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
        function descargarPDF() {
            const nombre   = <?= json_encode($nombreUsuario) ?>;
            const curso    = <?= json_encode($tituloCurso) ?>;
            const nota     = <?= json_encode($notaCertDisplay) ?>;
            const fecha    = <?= json_encode($fechaEmision) ?>;
            const codigo   = <?= json_encode($codigoCert) ?>;

            const html = `<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Certificado MatrixCoders</title>
<style>
@page { size: A4 landscape; margin: 0; }
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body {
  width: 297mm;
  height: 210mm;
  background: #fff;
  font-family: Georgia, 'Times New Roman', serif;
}
body { padding: 10mm; display: flex; }
.cert {
  flex: 1;
  border: 2.5px solid #d4af37;
  border-radius: 5px;
  position: relative;
  padding: 10mm 20mm;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  text-align: center;
}
.cc { position: absolute; width: 55px; height: 55px; border: 1.5px solid #d4af37; opacity: .3; }
.tl { top: 8px; left: 8px; border-right: none; border-bottom: none; }
.tr { top: 8px; right: 8px; border-left: none; border-bottom: none; }
.bl { bottom: 8px; left: 8px; border-right: none; border-top: none; }
.br { bottom: 8px; right: 8px; border-left: none; border-top: none; }
.cert-body {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
.logo { font-family: Arial, sans-serif; font-size: 15px; font-weight: 800; text-transform: uppercase; letter-spacing: 5px; color: #6B8F71; margin-bottom: 6px; }
.rule { border: none; border-top: 1px solid #d4af37; width: 200px; margin: 8px 0; }
.pre { font-family: Arial, sans-serif; font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: 2.5px; margin: 11px 0 4px; }
.se-certifica { font-family: Arial, sans-serif; font-size: 15px; color: #6b7280; margin: 6px 0 3px; }
.nombre { font-size: 54px; font-weight: 700; color: #1B2336; margin: 6px 0; line-height: 1.15; }
.desc { font-family: Arial, sans-serif; font-size: 15px; color: #6b7280; margin: 10px 0 3px; }
.curso { font-size: 22px; font-weight: 700; color: #0f172a; margin: 3px 0 12px; }
.nota-pill { display: inline-block; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 20px; padding: 5px 26px; font-family: Arial, sans-serif; font-size: 15px; font-weight: 700; color: #166534; }
.footer { display: flex; justify-content: space-between; align-items: flex-end; width: 100%; flex-shrink: 0; }
.firma { text-align: center; }
.firma-line { border-top: 1px solid #1B2336; width: 130px; margin: 0 auto 4px; }
.firma p { font-family: Arial, sans-serif; font-size: 11px; color: #6b7280; margin: 0; }
.centro { text-align: center; }
.star { font-size: 28px; color: #d4af37; line-height: 1; }
.centro p { font-family: Arial, sans-serif; font-size: 11px; color: #6b7280; margin-top: 3px; }
.codigo { text-align: center; }
.codigo p { font-family: Arial, sans-serif; font-size: 10px; color: #6b7280; letter-spacing: .5px; }
.codigo .code { font-size: 12px; font-weight: 700; color: #1B2336; letter-spacing: 2px; font-family: 'Courier New', monospace; }
</style>
</head>
<body>
<div class="cert">
  <div class="cc tl"></div><div class="cc tr"></div>
  <div class="cc bl"></div><div class="cc br"></div>

  <div class="cert-body">
    <p class="logo">MatrixCoders</p>
    <hr class="rule">
    <p class="pre">Certificado de finalización</p>
    <p class="se-certifica">Se certifica que</p>
    <p class="nombre">${nombre}</p>
    <p class="desc">ha completado satisfactoriamente el curso</p>
    <p class="curso">${curso}</p>
    <span class="nota-pill">Calificación: ${nota}&thinsp;/&thinsp;10</span>
    <hr class="rule" style="margin-top:10px;">
  </div>

  <div class="footer">
    <div class="firma">
      <div class="firma-line"></div>
      <p>Dirección académica</p>
      <p>MatrixCoders</p>
    </div>
    <div class="centro">
      <div class="star">★</div>
      <p>${fecha}</p>
    </div>
    <div class="codigo">
      <p>Código de verificación</p>
      <p class="code">${codigo}</p>
    </div>
  </div>
</div>
<script>window.addEventListener('load',function(){setTimeout(function(){window.print();},300);});<\/script>
</body>
</html>`;

            const win = window.open('', '_blank', 'width=960,height=680');
            if (!win) { alert('Permite las ventanas emergentes para descargar el certificado.'); return; }
            win.document.write(html);
            win.document.close();
        }
    </script>
</body>

</html>
