<?php
$tituloExamen = htmlspecialchars($examen['titulo'] ?? 'Examen Final');
$tituloCurso  = htmlspecialchars($curso['titulo']  ?? 'Curso');
$nPreguntas   = count($preguntas);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tituloExamen ?> — MatrixCoders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Saira:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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

        .exam-wrap {
            max-width: 780px;
            margin: 0 auto;
            padding: 28px 20px 60px;
        }

        .exam-hero {
            background: linear-gradient(135deg, var(--mc-navy) 0%, #1e3a5f 100%);
            border-radius: 18px;
            padding: 28px 32px;
            color: #fff;
            margin-bottom: 28px;
        }
        .exam-hero .badge-curso {
            display: inline-block;
            background: rgba(107,143,113,.3);
            color: #a3d9a8;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            padding: 3px 10px;
            border-radius: 20px;
            margin-bottom: 10px;
        }
        .exam-hero h1 { font-size: 1.35rem; font-weight: 800; margin: 0 0 8px; }
        .exam-hero p  { font-size: .88rem; color: #94a3b8; margin: 0; line-height: 1.6; }
        .exam-meta {
            display: flex;
            gap: 18px;
            margin-top: 16px;
            flex-wrap: wrap;
        }
        .exam-meta span {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: .8rem;
            color: #cbd5e1;
        }

        .pregunta-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,.06);
            padding: 22px 24px;
            margin-bottom: 18px;
        }
        .pregunta-num {
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--mc-green);
            margin-bottom: 8px;
        }
        .pregunta-enunciado {
            font-size: .98rem;
            font-weight: 700;
            color: var(--mc-dark);
            margin-bottom: 16px;
            line-height: 1.45;
        }

        .opcion-label {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border: 1.5px solid var(--mc-border);
            border-radius: 10px;
            cursor: pointer;
            margin-bottom: 8px;
            font-size: .9rem;
            transition: border-color .15s, background .15s;
        }
        .opcion-label:hover { border-color: var(--mc-green); background: #f0fdf4; }
        .opcion-label input[type=radio] { accent-color: var(--mc-green); width: 16px; height: 16px; flex-shrink: 0; }
        .opcion-label.selected { border-color: var(--mc-green); background: #f0fdf4; }

        .exam-footer {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,.06);
            padding: 20px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .exam-footer p { font-size: .85rem; color: var(--mc-muted); margin: 0; }
        .btn-submit {
            background: var(--mc-green);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: .65rem 1.8rem;
            font-size: .95rem;
            font-weight: 700;
            font-family: 'Saira', sans-serif;
            cursor: pointer;
            transition: background .15s;
        }
        .btn-submit:hover { background: var(--mc-green-d); }
        .btn-submit:disabled { opacity: .5; cursor: not-allowed; }

        .progress-sticky {
            position: sticky;
            top: 66px;
            z-index: 10;
            background: #fff;
            border-bottom: 1px solid var(--mc-border);
            padding: 8px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: .8rem;
            color: var(--mc-muted);
        }
        .progress-bar-wrap { flex: 1; height: 5px; background: var(--mc-border); border-radius: 99px; overflow: hidden; }
        .progress-bar-fill { height: 100%; background: var(--mc-green); border-radius: 99px; transition: width .2s; }
    </style>
</head>

<body>
    <?php require __DIR__ . '/../layout/header.php'; ?>

    <div class="progress-sticky">
        <span id="progressLabel">0 / <?= $nPreguntas ?> respondidas</span>
        <div class="progress-bar-wrap">
            <div class="progress-bar-fill" id="progressFill" style="width:0%"></div>
        </div>
    </div>

    <div class="exam-wrap">

        <!-- Hero -->
        <div class="exam-hero">
            <div class="badge-curso">📚 <?= $tituloCurso ?></div>
            <h1><?= $tituloExamen ?></h1>
            <p><?= htmlspecialchars($examen['descripcion'] ?? '') ?></p>
            <div class="exam-meta">
                <span>❓ <?= $nPreguntas ?> preguntas</span>
                <span>✅ Aprobado con <?= number_format((float)$examen['nota_minima'], 1) ?>/10</span>
                <span>📄 Una única respuesta por pregunta</span>
            </div>
        </div>

        <!-- Formulario -->
        <form method="POST" action="<?= BASE_URL ?>/index.php?url=examen&curso=<?= (int)$curso['id'] ?>" id="examForm">

            <?php foreach ($preguntas as $idx => $p): ?>
                <div class="pregunta-card" id="q<?= $p['id'] ?>">
                    <div class="pregunta-num">Pregunta <?= $idx + 1 ?> de <?= $nPreguntas ?></div>
                    <div class="pregunta-enunciado"><?= htmlspecialchars($p['enunciado']) ?></div>

                    <?php foreach ($p['opciones'] as $op): ?>
                        <label class="opcion-label" id="lbl-<?= $op['id'] ?>">
                            <input type="radio" name="p<?= $p['id'] ?>" value="<?= $op['id'] ?>"
                                onchange="onSelect(this)">
                            <?= htmlspecialchars($op['texto']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <div class="exam-footer">
                <p>Revisa tus respuestas antes de enviar. No podrás cambiarlas después.</p>
                <button type="submit" class="btn-submit" id="btnSubmit">
                    Enviar examen →
                </button>
            </div>
        </form>

    </div>

    <?php require __DIR__ . '/../layout/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const TOTAL = <?= $nPreguntas ?>;
        let answered = 0;

        function onSelect(radio) {
            const name = radio.name;
            document.querySelectorAll(`label.opcion-label`).forEach(l => {
                const inp = l.querySelector('input[type=radio]');
                if (inp && inp.name === name) l.classList.remove('selected');
            });
            radio.closest('label').classList.add('selected');

            answered = document.querySelectorAll('input[type=radio]:checked').length;
            document.getElementById('progressLabel').textContent = answered + ' / ' + TOTAL + ' respondidas';
            document.getElementById('progressFill').style.width = Math.round((answered / TOTAL) * 100) + '%';
        }

        document.getElementById('examForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('btnSubmit');
            btn.disabled = true;
            btn.textContent = 'Enviando…';
        });
    </script>
</body>

</html>
