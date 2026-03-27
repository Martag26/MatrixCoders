<?php
require_once __DIR__ . '/../../config.php';
$pageTitle = 'Mi carrito';

$carrito        = $_SESSION['carrito'] ?? [];
$cursos_carrito = [];
$total          = 0;

if (!empty($carrito)) {
    $database = new Database();
    $db       = $database->connect();
    $ids      = implode(',', array_map('intval', array_keys($carrito)));
    $stmt     = $db->query("SELECT * FROM curso WHERE id IN ($ids)");
    $cursos_carrito = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cursos_carrito as $c) {
        $total += (float)$c['precio'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/carrito.css">
</head>

<body>

    <?php require __DIR__ . '/../layout/header.php'; ?>

    <div class="carrito-page">
        <div class="mc-container">

            <h1 class="carrito-titulo">Introduce los datos para realizar el pago</h1>

            <div class="carrito-grid">

                <!-- ── CESTA ─────────────────────────── -->
                <div class="carrito-cesta">
                    <h2>Cesta</h2>

                    <?php if (empty($cursos_carrito)): ?>
                        <p class="carrito-vacio">Tu carrito está vacío.</p>
                    <?php else: ?>

                        <?php foreach ($cursos_carrito as $curso): ?>
                            <?php
                            $img = !empty($curso['imagen'])
                                ? BASE_URL . '/img/' . $curso['imagen']
                                : BASE_URL . '/img/curso1.jpg';
                            ?>
                            <div class="carrito-item" id="item-<?= $curso['id'] ?>">
                                <img src="<?= htmlspecialchars($img) ?>"
                                    class="carrito-item-img"
                                    alt="<?= htmlspecialchars($curso['titulo']) ?>">

                                <div class="carrito-item-info">
                                    <p class="carrito-item-titulo"><?= htmlspecialchars($curso['titulo']) ?></p>
                                    <p class="carrito-item-desc">
                                        <?= htmlspecialchars(mb_strimwidth($curso['descripcion'] ?? '', 0, 60, '...')) ?>
                                    </p>
                                </div>

                                <span class="carrito-item-precio">
                                    <?= $curso['precio'] > 0 ? number_format($curso['precio'], 2) . '€' : 'Gratis' ?>
                                </span>

                                <button
                                    class="carrito-item-eliminar"
                                    onclick="confirmarEliminar(<?= $curso['id'] ?>, '<?= htmlspecialchars(addslashes($curso['titulo'])) ?>')"
                                    title="Eliminar">
                                    🗑
                                </button>
                            </div>
                        <?php endforeach; ?>

                        <!-- Descuento -->
                        <div class="carrito-descuento">
                            <input type="text" id="input-descuento" placeholder="Tarjeta de regalo / Descuento">
                            <button onclick="aplicarDescuento()">Aplicar descuento</button>
                        </div>

                        <!-- Total -->
                        <div class="carrito-total">
                            <span>Pago total</span>
                            <span class="carrito-total-precio" id="total-precio">
                                <?= number_format($total, 2) ?>€
                            </span>
                        </div>

                    <?php endif; ?>
                </div>

                <!-- ── PAGO ───────────────────────────── -->
                <div class="carrito-pago">

                    <!-- Datos de contacto -->
                    <div class="pago-seccion">
                        <h3>Datos de contacto</h3>
                        <div class="pago-row">
                            <div class="pago-field">
                                <label>Nombre</label>
                                <input type="text" placeholder="Nombre"
                                    value="<?= htmlspecialchars($_SESSION['usuario_nombre'] ?? '') ?>">
                            </div>
                            <div class="pago-field">
                                <label>Apellidos</label>
                                <input type="text" placeholder="Apellidos">
                            </div>
                        </div>
                        <div class="pago-field mt-3">
                            <label>Email</label>
                            <input type="email" placeholder="correo@ejemplo.com">
                        </div>
                    </div>

                    <!-- Método de pago — solo tarjeta -->
                    <div class="pago-seccion">
                        <h3>Método de pago</h3>
                        <div class="pago-field">
                            <label>Nº Tarjeta</label>
                            <input type="text" placeholder="0000 0000 0000 0000" maxlength="19"
                                oninput="formatarTarjeta(this)">
                        </div>
                        <div class="pago-tarjeta-fila mt-3">
                            <div class="pago-field">
                                <label>MM / AA</label>
                                <input type="text" placeholder="MM / AA" maxlength="7">
                            </div>
                            <div class="pago-field">
                                <label>CVV</label>
                                <input type="text" placeholder="•••" maxlength="4">
                            </div>
                            <div></div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="pago-botones">
                        <button class="btn-atras" onclick="history.back()">Atrás</button>
                        <button class="btn-pagar" <?= empty($cursos_carrito) ? 'disabled' : '' ?>>
                            Pagar <?= !empty($cursos_carrito) ? number_format($total, 2) . '€' : '' ?>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal confirmar eliminación -->
    <div class="modal fade" id="modalEliminar" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content" style="border-radius:16px;">
                <div class="modal-body text-center py-4 px-4">
                    <p style="font-size:1.5rem; margin:0 0 8px;">🗑</p>
                    <p style="font-weight:700; font-size:1rem; margin:0 0 6px;">¿Eliminar curso?</p>
                    <p id="modal-eliminar-titulo" style="font-size:.88rem; color:#6b7280; margin:0 0 20px;"></p>
                    <div class="d-flex gap-2 justify-content-center">
                        <button class="btn-atras" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn-pagar" id="btn-confirmar-eliminar"
                            style="flex:unset; padding: 11px 24px;">
                            Eliminar
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

        // Abre el modal de confirmación
        function confirmarEliminar(id, titulo) {
            idAEliminar = id;
            document.getElementById('modal-eliminar-titulo').textContent = titulo;
            new bootstrap.Modal(document.getElementById('modalEliminar')).show();
        }

        // Confirma y elimina
        document.getElementById('btn-confirmar-eliminar').addEventListener('click', function() {
            if (!idAEliminar) return;

            fetch(baseUrl + '/index.php?url=carrito-eliminar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'curso_id=' + idAEliminar
                })
                .then(r => r.json())
                .then(data => {
                    if (data.ok) {
                        document.getElementById('item-' + idAEliminar)?.remove();
                        document.getElementById('total-precio').textContent = data.total_fmt + '€';

                        const badge = document.querySelector('.carrito-badge');
                        if (badge) {
                            data.cantidad === 0 ? badge.remove() : (badge.textContent = data.cantidad);
                        }

                        const btnPagar = document.querySelector('.btn-pagar');
                        if (data.cantidad === 0) {
                            btnPagar.disabled = true;
                            btnPagar.textContent = 'Pagar';
                            document.querySelector('.carrito-cesta').innerHTML =
                                '<h2>Cesta</h2><p class="carrito-vacio">Tu carrito está vacío.</p>';
                        } else {
                            btnPagar.textContent = 'Pagar ' + data.total_fmt + '€';
                        }

                        bootstrap.Modal.getInstance(document.getElementById('modalEliminar')).hide();
                        idAEliminar = null;
                    }
                });
        });

        function formatarTarjeta(input) {
            let val = input.value.replace(/\D/g, '').substring(0, 16);
            input.value = val.replace(/(.{4})/g, '$1 ').trim();
        }

        function aplicarDescuento() {
            const codigo = document.getElementById('input-descuento').value.trim();
            if (codigo === '') return;
            alert('El código "' + codigo + '" no es válido o ha caducado.');
        }
    </script>

</body>

</html>