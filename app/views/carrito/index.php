<?php
require_once __DIR__ . '/../../helpers/curso_imagen.php';
$pageTitle      = 'Mi carrito';
$usuario_id     = $_SESSION['usuario_id']  ?? null;
$cursos_carrito = $cursos_carrito          ?? [];
$ya_matriculados = $ya_matriculados         ?? [];
$subtotal       = $subtotal                ?? 0;
$iva            = $iva                     ?? 0;
$total          = $total                   ?? 0;
$flash          = $flash                   ?? null;

// Cursos válidos (no matriculados ya)
$cursosValidos = array_filter($cursos_carrito, fn($c) => !isset($ya_matriculados[$c['id']]));
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
        /* ── Nivel tags ── */
        .nivel-tag {
            display: inline-flex;
            align-items: center;
            font-size: .65rem;
            font-weight: 700;
            border-radius: 20px;
            padding: 2px 8px;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .nivel-principiante {
            color: #166534;
            background: #dcfce7;
            border-color: #86efac;
        }

        .nivel-estudiante {
            color: #2563eb;
            background: #dbeafe;
            border-color: #93c5fd;
        }

        .nivel-profesional {
            color: #7c2d12;
            background: #ffedd5;
            border-color: #fdba74;
        }

        .nivel-default {
            color: #6b7280;
            background: #f3f4f6;
            border-color: #e5e7eb;
        }

        /* ── Item animación salida ── */
        .carrito-item {
            transition: all .35s ease;
            overflow: hidden;
        }

        .carrito-item.eliminando {
            opacity: 0;
            max-height: 0;
            padding: 0;
            margin: 0;
            border: 0;
        }

        /* ── Ya matriculado aviso ── */
        .item-duplicado {
            opacity: .5;
            position: relative;
        }

        .badge-duplicado {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: .68rem;
            font-weight: 700;
            background: #fef9c3;
            border: 1px solid #fde68a;
            color: #92400e;
            border-radius: 20px;
            padding: 2px 8px;
            margin-top: 4px;
        }

        .carrito-flash {
            max-width: 760px;
            margin: 0 auto 20px;
            padding: 12px 16px;
            border-radius: 14px;
            background: #fff7ed;
            border: 1px solid #fdba74;
            color: #9a3412;
            font-size: .9rem;
            font-weight: 700;
        }

        /* ── Empty state ── */
        .carrito-empty-state {
            text-align: center;
            padding: 48px 20px;
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 16px;
        }

        .empty-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #1B2336;
            margin-bottom: 8px;
        }

        .empty-sub {
            font-size: .9rem;
            color: #6b7280;
            margin-bottom: 24px;
        }

        .btn-explorar {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #6B8F71;
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-size: .9rem;
            font-weight: 700;
            font-family: 'Saira', sans-serif;
            text-decoration: none;
            cursor: pointer;
            transition: background .15s;
        }

        .btn-explorar:hover {
            background: #4a6b50;
            color: #fff;
        }

        /* ── Resumen derecho ── */
        .resumen-linea {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: .9rem;
        }

        .resumen-linea:last-of-type {
            border-bottom: none;
        }

        .resumen-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 0 0;
            border-top: 2px solid #1B2336;
            margin-top: 8px;
        }

        .resumen-total-label {
            font-size: 1rem;
            font-weight: 800;
            color: #1B2336;
        }

        .resumen-total-precio {
            font-size: 1.4rem;
            font-weight: 900;
            color: #6B8F71;
        }

        /* ── Stripe button ── */
        .btn-stripe {
            width: 100%;
            background: #635bff;
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 16px;
            font-size: 1rem;
            font-weight: 800;
            font-family: 'Saira', sans-serif;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: background .15s;
            text-decoration: none;
            margin-top: 14px;
        }

        .btn-stripe:hover {
            background: #4f46e5;
            color: #fff;
        }

        .btn-stripe:disabled {
            opacity: .5;
            cursor: not-allowed;
        }

        .stripe-logo {
            font-size: 1.1rem;
            letter-spacing: -1px;
        }

        /* ── Código descuento ── */
        .descuento-row {
            display: flex;
            gap: 8px;
            margin-top: 14px;
        }

        .descuento-row input {
            flex: 1;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 9px 13px;
            font-size: .85rem;
            font-family: 'Saira', sans-serif;
            outline: none;
        }

        .descuento-row input:focus {
            border-color: #6B8F71;
        }

        .descuento-row button {
            background: #1B2336;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 9px 14px;
            font-size: .82rem;
            font-weight: 700;
            font-family: 'Saira', sans-serif;
            cursor: pointer;
            white-space: nowrap;
            transition: opacity .15s;
        }

        .descuento-row button:hover {
            opacity: .85;
        }

        .descuento-ok {
            display: none;
            font-size: .78rem;
            color: #16a34a;
            font-weight: 700;
            margin-top: 5px;
        }

        /* ── Login aviso ── */
        .login-aviso {
            background: #fef9c3;
            border: 1px solid #fde68a;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: .84rem;
            color: #92400e;
            margin-top: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .login-aviso a {
            color: #92400e;
            font-weight: 700;
        }

        /* ── Seguridad chips ── */
        .seguridad-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 16px;
            justify-content: center;
        }

        .seg-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: .7rem;
            color: #6b7280;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            padding: 3px 10px;
        }

        .resumen-ayuda {
            margin-top: 10px;
            color: #6b7280;
            font-size: .8rem;
            line-height: 1.5;
        }
    </style>
</head>

<body>
    <?php require __DIR__ . '/../layout/header.php'; ?>

    <div class="carrito-page">
        <div class="mc-container">

            <h1 class="carrito-titulo">
                Mi carrito
                <?php if (!empty($cursos_carrito)): ?>
                    <span style="font-size:1rem;font-weight:500;color:#6b7280;">(<?= count($cursos_carrito) ?> curso<?= count($cursos_carrito) !== 1 ? 's' : '' ?>)</span>
                <?php endif; ?>
            </h1>

            <?php if (!empty($flash)): ?>
                <div class="carrito-flash"><?= htmlspecialchars($flash) ?></div>
            <?php endif; ?>

            <?php if (empty($cursos_carrito)): ?>
                <!-- ── ESTADO VACÍO ── -->
                <div class="carrito-cesta" style="max-width:520px;margin:0 auto;">
                    <div class="carrito-empty-state">
                        <div class="empty-icon">🛒</div>
                        <h2 class="empty-title">Tu carrito está vacío</h2>
                        <p class="empty-sub">
                            Aún no has añadido ningún curso.<br>
                            ¡Explora nuestro catálogo y encuentra el que más te inspire!
                        </p>
                        <a class="btn-explorar" href="<?= BASE_URL ?>/index.php?url=buscar">
                            🔍 Explorar cursos
                        </a>
                    </div>
                </div>

            <?php else: ?>
                <div class="carrito-grid">

                    <!-- ── COLUMNA IZQUIERDA: lista de cursos ── -->
                    <div class="carrito-cesta">
                        <h2>Cursos en tu carrito</h2>

                        <?php foreach ($cursos_carrito as $curso):
                            $img = matrixcoders_curso_image($curso['imagen'] ?? '', $curso['titulo'] ?? '');
                            $precio  = (float)$curso['precio'];
                            $nivel   = $curso['nivel'] ?? '';
                            $cat     = $curso['categoria'] ?? '';
                            $esDup   = isset($ya_matriculados[$curso['id']]);
                            $nivelClass = match ($nivel) {
                                'principiante' => 'nivel-principiante',
                                'estudiante'   => 'nivel-estudiante',
                                'profesional'  => 'nivel-profesional',
                                default        => ($nivel ? 'nivel-default' : ''),
                            };
                            $nivelLabel = match ($nivel) {
                                'principiante' => 'Fundamentos',
                                'estudiante'   => 'Ruta academica',
                                'profesional'  => 'Perfil profesional',
                                default        => ucfirst((string)$nivel),
                            };
                        ?>
                            <div class="carrito-item <?= $esDup ? 'item-duplicado' : '' ?>"
                                id="item-<?= $curso['id'] ?>">

                                <img src="<?= htmlspecialchars($img) ?>"
                                    class="carrito-item-img"
                                    alt="<?= htmlspecialchars($curso['titulo']) ?>"
                                    onerror="this.src='<?= BASE_URL ?>/img/aprendiendo.png'">

                                <div class="carrito-item-info">
                                    <p class="carrito-item-titulo"><?= htmlspecialchars($curso['titulo']) ?></p>

                                    <!-- Tags nivel y categoría -->
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

                                <span class="carrito-item-precio" id="precio-<?= $curso['id'] ?>">
                                    <?= $precio > 0 ? number_format($precio, 2) . '€' : 'Gratis' ?>
                                </span>

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

                            <div class="resumen-linea">
                                <span>Subtotal (<span id="num-cursos"><?= count($cursosValidos) ?></span> curso<?= count($cursosValidos) !== 1 ? 's' : '' ?>)</span>
                                <span id="resumen-subtotal"><?= number_format($subtotal, 2) ?>€</span>
                            </div>
                            <div class="resumen-linea">
                                <span>IVA (21%)</span>
                                <span id="resumen-iva"><?= number_format($iva, 2) ?>€</span>
                            </div>

                            <!-- Código descuento -->
                            <div class="descuento-row">
                                <input type="text" id="input-descuento" placeholder="¿Tienes un código?">
                                <button onclick="aplicarDescuento()">Aplicar</button>
                            </div>
                            <div class="descuento-ok" id="descuento-ok">✓ Descuento aplicado</div>

                            <div class="resumen-total-row">
                                <span class="resumen-total-label">Total</span>
                                <span class="resumen-total-precio" id="resumen-total"><?= number_format($total, 2) ?>€</span>
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
                                    id="btnStripe"
                                    <?= ($total <= 0) ? 'disabled' : '' ?>>
                                    <span class="stripe-logo">stripe</span>
                                    Pagar <?= number_format($total, 2) ?>€ de forma segura
                                </button>
                            </form>
                        <?php endif; ?>

                        <!-- Chips de seguridad -->
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

                <!-- Franja superior de color -->
                <div style="background:#fef2f2;padding:28px 24px 20px;text-align:center;border-bottom:1px solid #fee2e2;">
                    <div style="width:48px;height:48px;border-radius:14px;background:#fee2e2;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none"
                            viewBox="0 0 24 24" stroke="#ef4444" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4h6v3M3 7h18" />
                        </svg>
                    </div>
                    <h5 style="font-weight:800;font-size:1rem;margin:0 0 6px;color:#1B2336;">
                        Eliminar curso
                    </h5>
                    <p id="modal-titulo-curso"
                        style="font-size:.85rem;color:#6b7280;margin:0;line-height:1.5;"></p>
                </div>

                <!-- Cuerpo -->
                <div style="padding:18px 24px 22px;background:#fff;">
                    <p style="font-size:.83rem;color:#6b7280;margin:0 0 18px;text-align:center;">
                        ¿Seguro que quieres eliminarlo? Podrás volver a añadirlo cuando quieras.
                    </p>
                    <div style="display:flex;gap:10px;">
                        <button data-bs-dismiss="modal"
                            style="flex:1;padding:11px;border-radius:10px;border:1px solid #e5e7eb;
                                   background:#fff;font-weight:600;font-size:.88rem;
                                   font-family:'Saira',sans-serif;cursor:pointer;color:#1B2336;">
                            Cancelar
                        </button>
                        <button id="btn-confirmar-eliminar"
                            style="flex:1;padding:11px;border-radius:10px;border:none;
                                   background:#ef4444;color:#fff;font-weight:700;
                                   font-size:.88rem;font-family:'Saira',sans-serif;cursor:pointer;">
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

        // Abre modal de confirmación
        function eliminarItem(id, titulo) {
            idAEliminar = id;
            document.getElementById('modal-titulo-curso').textContent = titulo;
            new bootstrap.Modal(document.getElementById('modalEliminar')).show();
        }

        // Confirma y elimina con animación
        document.getElementById('btn-confirmar-eliminar')?.addEventListener('click', function() {
            if (!idAEliminar) return;

            const item = document.getElementById('item-' + idAEliminar);

            // Animación de salida
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
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'curso_id=' + idAEliminar
                })
                .then(r => r.json())
                .then(data => {
                    if (!data.ok) return;

                    // Eliminar nodo tras animación
                    setTimeout(() => item?.remove(), 380);

                    // Actualizar resumen
                    document.getElementById('resumen-subtotal').textContent = data.subtotal_fmt + '€';
                    document.getElementById('resumen-iva').textContent = data.iva_fmt + '€';
                    document.getElementById('resumen-total').textContent = data.total_fmt + '€';
                    const numCursos = document.getElementById('num-cursos');
                    if (numCursos) numCursos.textContent = data.cantidad_valida;

                    // Actualizar botón Stripe
                    const btnStripe = document.getElementById('btnStripe');
                    if (btnStripe) {
                        btnStripe.innerHTML = `<span class="stripe-logo">stripe</span> Pagar ${data.total_fmt}€ de forma segura`;
                        btnStripe.disabled = data.cantidad_valida === 0;
                    }

                    // Badge del header
                    const badge = document.querySelector('.carrito-badge');
                    if (badge) {
                        data.cantidad === 0 ? badge.remove() : (badge.textContent = data.cantidad);
                    }

                    // Si el carrito queda vacío, mostrar empty state
                    if (data.cantidad === 0) {
                        setTimeout(() => location.reload(), 400);
                    }
                });

            bootstrap.Modal.getInstance(document.getElementById('modalEliminar')).hide();
            idAEliminar = null;
        });

        // Código de descuento (placeholder — conectar con backend cuando esté listo)
        function aplicarDescuento() {
            const codigo = document.getElementById('input-descuento').value.trim();
            if (!codigo) return;
            // TODO: fetch a /index.php?url=descuento con el código
            alert('Los códigos de descuento estarán disponibles próximamente.');
        }

    </script>
</body>

</html>
