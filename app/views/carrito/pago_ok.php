<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>¡Pago completado! — MatrixCoders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Saira:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
    <style>
        /* Purchased courses list */
        .cursos-comprados{margin:0 0 28px;text-align:left;display:flex;flex-direction:column;gap:10px}
        .curso-ok-item{display:flex;align-items:center;gap:12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:10px 14px}
        .curso-ok-thumb{width:44px;height:44px;border-radius:8px;object-fit:cover;flex-shrink:0;background:#e5e7eb}
        .curso-ok-info{flex:1;min-width:0}
        .curso-ok-titulo{font-size:.85rem;font-weight:700;color:#1B2336;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin:0 0 2px}
        .curso-ok-precio{font-size:.75rem;color:#6b7280;margin:0}
        .curso-ok-check{color:#16a34a;font-size:1.1rem;flex-shrink:0}
    </style>
    <style>
        :root {
            --mc-green: #6B8F71;
            --mc-green-d: #4a6b50;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            font-family: 'Saira', sans-serif;
            background: #f9fafb;
            margin: 0;
        }

        .ok-page {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .ok-card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, .1);
            padding: 52px 44px;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }

        .ok-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6B8F71, #4a6b50);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            margin: 0 auto 24px;
            box-shadow: 0 8px 24px rgba(107, 143, 113, .35);
        }

        .ok-title {
            font-size: 1.6rem;
            font-weight: 900;
            color: #1B2336;
            margin: 0 0 10px;
        }

        .ok-sub {
            font-size: .95rem;
            color: #6b7280;
            line-height: 1.6;
            margin: 0 0 32px;
        }

        .ok-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn-ok {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 20px;
            border-radius: 12px;
            font-size: .92rem;
            font-weight: 700;
            font-family: 'Saira', sans-serif;
            text-decoration: none;
            transition: all .15s;
            border: none;
            cursor: pointer;
        }

        .btn-ok.primary {
            background: var(--mc-green);
            color: #fff;
        }

        .btn-ok.primary:hover {
            background: var(--mc-green-d);
            color: #fff;
        }

        .btn-ok.secondary {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #e5e7eb;
        }

        .btn-ok.secondary:hover {
            background: #e5e7eb;
        }

        .confetti {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 9999;
        }
    </style>
</head>

<body>
    <?php
    require __DIR__ . '/../layout/header.php';
    require_once __DIR__ . '/../../helpers/curso_imagen.php';
    $esAltaGratis    = !empty($_GET['gratis']);
    $esPagoSimulado  = !empty($_GET['simulado']);
    $cursosComprados = $cursosComprados ?? [];
    ?>

    <div class="ok-page">
        <div class="ok-card">
            <div class="ok-circle">✓</div>
            <h1 class="ok-title">
                <?= $esPagoSimulado ? '¡Pago simulado completado!' : ($esAltaGratis ? '¡Acceso activado!' : '¡Pago completado!') ?>
            </h1>
            <p class="ok-sub">
                <?php if ($esPagoSimulado): ?>
                    Se ha registrado una compra de prueba y los cursos ya están disponibles en tu cuenta sin realizar ningún cobro real.
                <?php elseif ($esAltaGratis): ?>
                    Tus cursos gratuitos ya están activos y listos para empezar.
                <?php else: ?>
                    Ya tienes acceso a tus nuevos cursos. Están disponibles en tu espacio de aprendizaje.
                <?php endif; ?>
            </p>

            <?php if (!empty($cursosComprados)): ?>
                <div class="cursos-comprados">
                    <?php foreach ($cursosComprados as $c):
                        $img = matrixcoders_curso_image($c['imagen'] ?? '', $c['titulo'] ?? '');
                        $precio = (float)($c['precio'] ?? 0);
                    ?>
                        <div class="curso-ok-item">
                            <img class="curso-ok-thumb"
                                src="<?= htmlspecialchars($img) ?>"
                                alt="<?= htmlspecialchars($c['titulo']) ?>"
                                onerror="this.src='<?= BASE_URL ?>/img/aprendiendo.png'">
                            <div class="curso-ok-info">
                                <p class="curso-ok-titulo"><?= htmlspecialchars($c['titulo']) ?></p>
                                <p class="curso-ok-precio">
                                    <?= $precio > 0 ? number_format($precio, 2) . '€' : 'Gratuito' ?>
                                </p>
                            </div>
                            <span class="curso-ok-check">✓</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="ok-actions">
                <a class="btn-ok primary" href="<?= BASE_URL ?>/index.php?url=dashboard">
                    📚 Ir a mis cursos
                </a>
                <a class="btn-ok secondary" href="<?= BASE_URL ?>/index.php?url=buscar">
                    🔍 Seguir explorando
                </a>
            </div>
        </div>
    </div>

    <?php require __DIR__ . '/../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Confeti simple con Canvas
        (function() {
            const canvas = document.createElement('canvas');
            canvas.className = 'confetti';
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            document.body.appendChild(canvas);
            const ctx = canvas.getContext('2d');
            const colors = ['#6B8F71', '#4a6b50', '#fbbf24', '#60a5fa', '#f472b6', '#34d399'];
            const particles = Array.from({
                length: 80
            }, () => ({
                x: Math.random() * canvas.width,
                y: Math.random() * -200,
                r: Math.random() * 6 + 3,
                d: Math.random() * 80 + 80,
                color: colors[Math.floor(Math.random() * colors.length)],
                tilt: Math.random() * 10 - 10,
                tiltAngle: 0,
                tiltAngleInc: Math.random() * .07 + .05,
            }));
            let angle = 0,
                tick = 0;

            function draw() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                angle += .01;
                tick++;
                particles.forEach((p, i) => {
                    p.tiltAngle += p.tiltAngleInc;
                    p.y += (Math.cos(angle + p.d) + 3 + p.r / 2) / 2;
                    p.x += Math.sin(angle);
                    p.tilt = Math.sin(p.tiltAngle) * 15;
                    ctx.beginPath();
                    ctx.lineWidth = p.r / 2;
                    ctx.strokeStyle = p.color;
                    ctx.moveTo(p.x + p.tilt + p.r / 4, p.y);
                    ctx.lineTo(p.x + p.tilt, p.y + p.tilt + p.r / 4);
                    ctx.stroke();
                });
                if (tick < 200) requestAnimationFrame(draw);
                else {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    canvas.remove();
                }
            }
            draw();
        })();
    </script>
</body>

</html>
