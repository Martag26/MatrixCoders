<?php
require_once __DIR__ . '/../../helpers/curso_imagen.php';
$pageTitle       = 'Mi carrito';
$usuario_id      = $_SESSION['usuario_id']   ?? null;
$cursos_carrito  = $cursos_carrito           ?? [];
$ya_matriculados = $ya_matriculados          ?? [];
$discounts       = $discounts               ?? [];   // [curso_id => descuento_pct]
$subtotalOriginal = $subtotalOriginal        ?? 0;
$subtotalFinal   = $subtotalFinal            ?? 0;
$ahorro          = $ahorro                   ?? 0;
$iva             = $iva                      ?? 0;
$total           = $total                    ?? 0;
$flash           = $flash                    ?? null;

// Flash puede ser array ['mensaje','tipo'] o string legacy
$flashMsg  = is_array($flash) ? ($flash['mensaje'] ?? '') : (string)($flash ?? '');
$flashTipo = is_array($flash) ? ($flash['tipo']    ?? 'info') : 'info';

$cursosValidos = array_filter($cursos_carrito, fn($c) => !isset($ya_matriculados[$c['id']]));
$hayAhorro     = $ahorro > 0.001;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — MatrixCoders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Saira:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/carrito.css">
    <style>
        .nivel-tag{display:inline-flex;align-items:center;font-size:.65rem;font-weight:700;border-radius:20px;padding:2px 8px;border:1px solid transparent;white-space:nowrap}
        .nivel-principiante{color:#166534;background:#dcfce7;border-color:#86efac}
        .nivel-estudiante{color:#2563eb;background:#dbeafe;border-color:#93c5fd}
        .nivel-profesional{color:#7c2d12;background:#ffedd5;border-color:#fdba74}
        .nivel-default{color:#6b7280;background:#f3f4f6;border-color:#e5e7eb}
        .carrito-item{transition:all .35s ease;overflow:hidden;position:relative}
        .carrito-item.eliminando{opacity:0;max-height:0;padding:0;margin:0;border:0}
        .item-duplicado{opacity:.5;position:relative}
        .badge-duplicado{display:inline-flex;align-items:center;gap:4px;font-size:.68rem;font-weight:700;background:#fef9c3;border:1px solid #fde68a;color:#92400e;border-radius:20px;padding:2px 8px;margin-top:4px}

        /* Discount badge on item */
        .item-descuento-badge{position:absolute;top:10px;left:10px;background:#ef4444;color:#fff;font-size:.65rem;font-weight:800;border-radius:8px;padding:2px 7px;letter-spacing:.3px}
        .carrito-item-precio-wrap{display:flex;flex-direction:column;align-items:flex-end;gap:2px;min-width:72px}
        .precio-original-tachado{font-size:.78rem;color:#9ca3af;text-decoration:line-through;line-height:1.2}
        .precio-final{font-size:.97rem;font-weight:800;color:#1B2336;line-height:1.2}
        .precio-gratis{font-size:.97rem;font-weight:800;color:#6B8F71}

        /* Flash */
        .carrito-flash{max-width:760px;margin:0 auto 20px;padding:12px 16px;border-radius:14px;font-size:.9rem;font-weight:700}
        .carrito-flash.info{background:#eff6ff;border:1px solid #93c5fd;color:#1e40af}
        .carrito-flash.success{background:#f0fdf4;border:1px solid #86efac;color:#166534}
        .carrito-flash.error{background:#fff7ed;border:1px solid #fdba74;color:#9a3412}

        /* Empty state */
        .carrito-empty-state{text-align:center;padding:56px 32px}
        .empty-icon-wrap{width:88px;height:88px;border-radius:24px;background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1px solid #bbf7d0;display:flex;align-items:center;justify-content:center;margin:0 auto 22px}
        .empty-title{font-size:1.3rem;font-weight:900;color:#1B2336;margin:0 0 8px}
        .empty-sub{font-size:.9rem;color:#6b7280;margin:0 0 28px;line-height:1.6}
        .btn-explorar{display:inline-flex;align-items:center;gap:8px;background:#6B8F71;color:#fff;border:none;border-radius:12px;padding:13px 28px;font-size:.9rem;font-weight:700;font-family:'Saira',sans-serif;text-decoration:none;cursor:pointer;transition:background .15s,transform .15s;box-shadow:0 4px 14px rgba(107,143,113,.35)}
        .btn-explorar:hover{background:#4a6b50;color:#fff;transform:translateY(-1px)}

        /* Resumen */
        .resumen-linea{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f3f4f6;font-size:.9rem}
        .resumen-linea:last-of-type{border-bottom:none}
        .resumen-linea.descuento-line{color:#16a34a;font-weight:700}
        .resumen-total-row{display:flex;justify-content:space-between;align-items:center;padding:14px 0 0;border-top:2px solid #1B2336;margin-top:8px}
        .resumen-total-label{font-size:1rem;font-weight:800;color:#1B2336}
        .resumen-total-precio{font-size:1.4rem;font-weight:900;color:#6B8F71}

        /* Campaign info chip */
        .campana-info{display:flex;align-items:center;gap:7px;background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:9px 13px;font-size:.8rem;color:#166534;font-weight:600;margin-top:14px}
        .campana-info svg{flex-shrink:0}

        /* Stripe button */
        .btn-stripe{width:100%;background:#635bff;color:#fff;border:none;border-radius:12px;padding:16px;font-size:1rem;font-weight:800;font-family:'Saira',sans-serif;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px;transition:background .15s;text-decoration:none;margin-top:14px}
        .btn-stripe:hover:not(:disabled){background:#4f46e5;color:#fff}
        .btn-stripe:disabled{opacity:.55;cursor:not-allowed}
        .stripe-logo{font-size:1.1rem;letter-spacing:-1px}
        .btn-stripe .spinner{width:18px;height:18px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .65s linear infinite;display:none}
        @keyframes spin{to{transform:rotate(360deg)}}
        .btn-stripe.loading .spinner{display:block}
        .btn-stripe.loading .stripe-logo,.btn-stripe.loading .btn-label{display:none}

        /* Login aviso */
        .login-aviso{background:#fef9c3;border:1px solid #fde68a;border-radius:12px;padding:12px 16px;font-size:.84rem;color:#92400e;margin-top:14px;display:flex;align-items:center;gap:8px}
        .login-aviso a{color:#92400e;font-weight:700}

        /* Security chips */
        .seguridad-chips{display:flex;flex-wrap:wrap;gap:6px;margin-top:16px;justify-content:center}
        .seg-chip{display:inline-flex;align-items:center;gap:4px;font-size:.7rem;color:#6b7280;background:#f8fafc;border:1px solid #e5e7eb;border-radius:20px;padding:3px 10px}
        .resumen-ayuda{margin-top:10px;color:#6b7280;font-size:.8rem;line-height:1.5}
    </style>
</head>

<body>
    <?php require __DIR__ . '/../layout/header.php'; ?>

    <div class="carrito-page">
        <div class="mc-container">

            <h1 class="carrito-titulo">
                Mi carrito
                <?php if (!empty($cursos_carrito)): ?>
                    <span style="font-size:1rem;font-weight:500;color:#6b7280;">
                        (<?= count($cursos_carrito) ?> curso<?= count($cursos_carrito) !== 1 ? 's' : '' ?>)
                    </span>
                <?php endif; ?>
            </h1>

            <?php if (!empty($flashMsg)): ?>
                <div class="carrito-flash <?= htmlspecialchars($flashTipo) ?>">
                    <?= htmlspecialchars($flashMsg) ?>
                </div>
            <?php endif; ?>

            <?php if (empty($cursos_carrito)): ?>
                <!-- ── ESTADO VACÍO ── -->
                <div class="carrito-cesta" style="max-width:520px;margin:0 auto;">
                    <div class="carrito-empty-state">
                        <div class="empty-icon-wrap">
                            <svg width="40" height="40" fill="none" stroke="#6B8F71" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h2 class="empty-title">Tu carrito está vacío</h2>
                        <p class="empty-sub">
                            Aún no has añadido ningún curso.<br>
                            Explora el catálogo y encuentra el que más te inspire.
                        </p>
                        <a class="btn-explorar" href="<?= BASE_URL ?>/index.php?url=buscar">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                            Explorar cursos
                        </a>
                    </div>
                </div>

            <?php else: ?>
                <div class="carrito-grid">

                    <!-- ── COLUMNA IZQUIERDA: lista de cursos ── -->
                    <div class="carrito-cesta">
                        <h2>Cursos en tu carrito</h2>

                        <?php foreach ($cursos_carrito as $curso):
                            $img         = matrixcoders_curso_image($curso['imagen'] ?? '', $curso['titulo'] ?? '');
                            $precioBase  = (float)$curso['precio'];
                            $desc        = (float)($discounts[$curso['id']] ?? 0);
                            $precioFinal = ($desc > 0 && $precioBase > 0)
                                ? round($precioBase * (1 - $desc / 100), 2)
                                : $precioBase;
                            $nivel       = $curso['nivel'] ?? '';
                            $cat         = $curso['categoria'] ?? '';
                            $esDup       = isset($ya_matriculados[$curso['id']]);
                            $nivelClass  = match ($nivel) {
                                'principiante' => 'nivel-principiante',
                                'estudiante'   => 'nivel-estudiante',
                                'profesional'  => 'nivel-profesional',
                                default        => ($nivel ? 'nivel-default' : ''),
                            };
                            $nivelLabel  = match ($nivel) {
                                'principiante' => 'Fundamentos',
                                'estudiante'   => 'Ruta académica',
                                'profesional'  => 'Perfil profesional',
                                default        => ucfirst((string)$nivel),
                            };
                        ?>
                            <div class="carrito-item <?= $esDup ? 'item-duplicado' : '' ?>"
                                id="item-<?= $curso['id'] ?>">

                                <?php if ($desc > 0 && !$esDup): ?>
                                    <span class="item-descuento-badge">-<?= (int)$desc ?>%</span>
                                <?php endif; ?>

                                <img src="<?= htmlspecialchars($img) ?>"
                                    class="carrito-item-img"
                                    alt="<?= htmlspecialchars($curso['titulo']) ?>"
                                    onerror="this.src='<?= BASE_URL ?>/img/aprendiendo.png'">

                                <div class="carrito-item-info">
                                    <p class="carrito-item-titulo"><?= htmlspecialchars($curso['titulo']) ?></p>

                                    <div style="display:flex;flex-wrap:wrap;gap:5px;margin:4px 0;">
                                        <?php if ($nivelClass): ?>
                                            <span class="nivel-tag <?= $nivelClass ?>">
                                                <?= htmlspecialchars($nivelLabel) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($cat): ?>
                                            <span class="nivel-tag nivel-default">📂 <?= htmlspecialchars($cat) ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($esDup): ?>
                                        <span class="badge-duplicado">✓ Ya estás matriculado</span>
                                    <?php else: ?>
                                        <p class="carrito-item-desc">
                                            <?= htmlspecialchars(mb_strimwidth($curso['descripcion'] ?? '', 0, 70, '…')) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <div class="carrito-item-precio-wrap" id="precio-wrap-<?= $curso['id'] ?>">
                                    <?php if ($precioBase <= 0): ?>
                                        <span class="precio-gratis">Gratis</span>
                                    <?php elseif ($desc > 0 && !$esDup): ?>
                                        <span class="precio-original-tachado"><?= number_format($precioBase, 2) ?>€</span>
                                        <span class="precio-final"><?= number_format($precioFinal, 2) ?>€</span>
                                    <?php else: ?>
                                        <span class="precio-final"><?= number_format($precioBase, 2) ?>€</span>
                                    <?php endif; ?>
                                </div>

                                <?php if (!$esDup): ?>
                                    <button class="carrito-item-eliminar"
                                        onclick="eliminarItem(<?= $curso['id'] ?>, '<?= htmlspecialchars(addslashes($curso['titulo'])) ?>')"
                                        title="Eliminar del carrito"
                                        aria-label="Eliminar">
                                        ✕
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>

                        <?php if (!empty($ya_matriculados)): ?>
                            <p style="font-size:.78rem;color:#6b7280;margin-top:10px;">
                                ℹ️ Los cursos en los que ya estás matriculado no se incluyen en el pago.
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- ── COLUMNA DERECHA: resumen y pago ── -->
                    <div class="carrito-pago" id="panelResumen">

                        <div class="pago-seccion">
                            <h3>Resumen del pedido</h3>

                            <?php if ($hayAhorro): ?>
                                <div class="resumen-linea">
                                    <span>Precio original (<span id="num-cursos"><?= count($cursosValidos) ?></span> curso<?= count($cursosValidos) !== 1 ? 's' : '' ?>)</span>
                                    <span id="resumen-subtotal-original"><?= number_format($subtotalOriginal, 2) ?>€</span>
                                </div>
                                <div class="resumen-linea descuento-line" id="fila-ahorro">
                                    <span>🏷️ Descuento de campaña</span>
                                    <span id="resumen-ahorro">-<?= number_format($ahorro, 2) ?>€</span>
                                </div>
                                <div class="resumen-linea">
                                    <span>Subtotal con descuento</span>
                                    <span id="resumen-subtotal"><?= number_format($subtotalFinal, 2) ?>€</span>
                                </div>
                            <?php else: ?>
                                <div class="resumen-linea">
                                    <span>Subtotal (<span id="num-cursos"><?= count($cursosValidos) ?></span> curso<?= count($cursosValidos) !== 1 ? 's' : '' ?>)</span>
                                    <span id="resumen-subtotal"><?= number_format($subtotalFinal, 2) ?>€</span>
                                </div>
                            <?php endif; ?>

                            <div class="resumen-linea">
                                <span>IVA (21%)</span>
                                <span id="resumen-iva"><?= number_format($iva, 2) ?>€</span>
                            </div>

                            <div class="resumen-total-row">
                                <span class="resumen-total-label">Total</span>
                                <span class="resumen-total-precio" id="resumen-total"><?= number_format($total, 2) ?>€</span>
                            </div>

                            <!-- Info descuentos automáticos -->
                            <div class="campana-info">
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                <?php if ($hayAhorro): ?>
                                    Los descuentos de campaña se aplican automáticamente.
                                <?php else: ?>
                                    Los descuentos de campaña activos se aplican solos.
                                <?php endif; ?>
                            </div>

                            <p class="resumen-ayuda">El pago se tramita con Stripe. Los cursos ya matriculados se excluyen automáticamente del cobro.</p>
                        </div>

                        <!-- Botón Stripe -->
                        <?php if (!$usuario_id): ?>
                            <div class="login-aviso">
                                ⚠️ <span>Debes <a href="<?= BASE_URL ?>/index.php?url=login&retorno=carrito">iniciar sesión</a> para pagar.</span>
                            </div>
                        <?php elseif (empty($cursosValidos)): ?>
                            <div class="login-aviso">
                                ℹ️ <span>Todos los cursos del carrito ya están en tus matrículas.</span>
                            </div>
                        <?php else: ?>
                            <form method="POST" action="<?= BASE_URL ?>/index.php?url=pagar" id="formPago">
                                <button type="submit"
                                    class="btn-stripe"
                                    id="btnStripe">
                                    <div class="spinner"></div>
                                    <?php if ($total <= 0): ?>
                                        <span class="btn-label">Obtener gratis</span>
                                    <?php else: ?>
                                        <span class="stripe-logo">stripe</span>
                                        <span class="btn-label">Pagar <?= number_format($total, 2) ?>€ de forma segura</span>
                                    <?php endif; ?>
                                </button>
                            </form>
                        <?php endif; ?>

                        <div class="seguridad-chips">
                            <span class="seg-chip">🔒 SSL</span>
                            <span class="seg-chip">💳 Stripe</span>
                            <span class="seg-chip">↩️ Garantía 30 días</span>
                        </div>

                    </div>
                </div><!-- /carrito-grid -->
            <?php endif; ?>

        </div>
    </div>

    <!-- Modal confirmación eliminar -->
    <div class="modal fade" id="modalEliminar" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width:360px;">
            <div class="modal-content" style="border-radius:18px;border:none;overflow:hidden;">
                <div style="background:#fef2f2;padding:28px 24px 20px;text-align:center;border-bottom:1px solid #fee2e2;">
                    <div style="width:48px;height:48px;border-radius:14px;background:#fee2e2;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none"
                            viewBox="0 0 24 24" stroke="#ef4444" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4h6v3M3 7h18"/>
                        </svg>
                    </div>
                    <h5 style="font-weight:800;font-size:1rem;margin:0 0 6px;color:#1B2336;">Eliminar curso</h5>
                    <p id="modal-titulo-curso" style="font-size:.85rem;color:#6b7280;margin:0;line-height:1.5;"></p>
                </div>
                <div style="padding:18px 24px 22px;background:#fff;">
                    <p style="font-size:.83rem;color:#6b7280;margin:0 0 18px;text-align:center;">
                        ¿Seguro que quieres eliminarlo? Podrás volver a añadirlo cuando quieras.
                    </p>
                    <div style="display:flex;gap:10px;">
                        <button data-bs-dismiss="modal"
                            style="flex:1;padding:11px;border-radius:10px;border:1px solid #e5e7eb;background:#fff;font-weight:600;font-size:.88rem;font-family:'Saira',sans-serif;cursor:pointer;color:#1B2336;">
                            Cancelar
                        </button>
                        <button id="btn-confirmar-eliminar"
                            style="flex:1;padding:11px;border-radius:10px;border:none;background:#ef4444;color:#fff;font-weight:700;font-size:.88rem;font-family:'Saira',sans-serif;cursor:pointer;">
                            Sí, eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require __DIR__ . '/../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const baseUrl = '<?= BASE_URL ?>';
        let idAEliminar = null;

        function eliminarItem(id, titulo) {
            idAEliminar = id;
            document.getElementById('modal-titulo-curso').textContent = titulo;
            new bootstrap.Modal(document.getElementById('modalEliminar')).show();
        }

        document.getElementById('btn-confirmar-eliminar')?.addEventListener('click', function () {
            if (!idAEliminar) return;

            const item = document.getElementById('item-' + idAEliminar);
            if (item) {
                item.style.maxHeight = item.offsetHeight + 'px';
                item.style.overflow = 'hidden';
                requestAnimationFrame(() => {
                    item.style.transition = 'all .35s ease';
                    item.style.maxHeight = '0';
                    item.style.opacity = '0';
                    item.style.padding = '0';
                    item.style.marginBottom = '0';
                });
            }

            fetch(baseUrl + '/index.php?url=carrito-eliminar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'curso_id=' + idAEliminar
            })
            .then(r => r.json())
            .then(data => {
                if (!data.ok) return;

                setTimeout(() => item?.remove(), 380);

                // Update summary lines
                const elSubtotal = document.getElementById('resumen-subtotal');
                const elIva      = document.getElementById('resumen-iva');
                const elTotal    = document.getElementById('resumen-total');
                const elAhorro   = document.getElementById('resumen-ahorro');
                const filaAhorro = document.getElementById('fila-ahorro');
                const numCursos  = document.getElementById('num-cursos');

                if (elSubtotal) elSubtotal.textContent = data.subtotal_fmt + '€';
                if (elIva)      elIva.textContent      = data.iva_fmt + '€';
                if (elTotal)    elTotal.textContent    = data.total_fmt + '€';
                if (numCursos)  numCursos.textContent  = data.cantidad_valida;

                // Show/hide savings row
                if (elAhorro && filaAhorro) {
                    if (data.tiene_descuento && data.ahorro_fmt) {
                        elAhorro.textContent = '-' + data.ahorro_fmt + '€';
                        filaAhorro.style.display = '';
                    } else {
                        filaAhorro.style.display = 'none';
                    }
                }

                // Update stripe button
                const btnStripe = document.getElementById('btnStripe');
                if (btnStripe) {
                    const label = btnStripe.querySelector('.btn-label');
                    if (label) label.textContent = 'Pagar ' + data.total_fmt + '€ de forma segura';
                    btnStripe.disabled = data.cantidad_valida === 0;
                }

                // Header cart badge
                const badge = document.querySelector('.carrito-badge');
                if (badge) {
                    if (data.cantidad === 0) {
                        badge.textContent = '';
                        badge.classList.remove('visible');
                    } else {
                        badge.textContent = data.cantidad;
                        badge.classList.add('visible');
                    }
                }

                if (data.cantidad === 0) {
                    setTimeout(() => location.reload(), 400);
                }
            });

            bootstrap.Modal.getInstance(document.getElementById('modalEliminar')).hide();
            idAEliminar = null;
        });

        // Loading spinner on Stripe form submit
        document.getElementById('formPago')?.addEventListener('submit', function () {
            const btn = document.getElementById('btnStripe');
            if (btn && !btn.disabled) {
                btn.disabled = true;
                btn.classList.add('loading');
            }
        });
    </script>
</body>

</html>
