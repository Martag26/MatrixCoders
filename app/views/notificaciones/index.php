<?php
$tiposConfig = [
    'tarea'     => ['label' => 'Tarea',      'color' => '#f59e0b', 'bg' => '#fffbeb', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
    'expiracion'=> ['label' => 'Expiración', 'color' => '#ef4444', 'bg' => '#fef2f2', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
    'crm'       => ['label' => 'Campaña',    'color' => '#7c3aed', 'bg' => '#f5f3ff', 'icon' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z'],
    'mensaje'   => ['label' => 'Mensaje',    'color' => '#3b82f6', 'bg' => '#eff6ff', 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
    'info'      => ['label' => 'Info',       'color' => '#6b7280', 'bg' => '#f9fafb', 'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
];

function notifTimeAgo(?string $str): string {
    if (!$str) return '';
    $diff = time() - strtotime($str);
    if ($diff < 60)    return 'ahora mismo';
    if ($diff < 3600)  return 'hace ' . floor($diff/60) . ' min';
    if ($diff < 86400) return 'hace ' . floor($diff/3600) . 'h';
    if ($diff < 604800) return 'hace ' . floor($diff/86400) . 'd';
    return date('d/m/Y', strtotime($str));
}

function buildNotifUrl(array $overrides = []): string {
    global $filtroTipo, $page;
    $p = [
        'url'  => 'notificaciones',
        'tipo' => $overrides['tipo'] ?? $filtroTipo,
        'p'    => $overrides['p']   ?? $page,
    ];
    $p = array_filter($p, fn($v) => $v !== '' && $v !== null && $v !== 1);
    if (isset($p['p']) && (int)$p['p'] === 1) unset($p['p']);
    return BASE_URL . '/index.php?' . http_build_query($p);
}
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
    <style>
        *,*::before,*::after{box-sizing:border-box}
        body{font-family:'Saira',sans-serif;background:#f8fafc;margin:0;color:#1B2336}

        .notif-page{max-width:760px;margin:0 auto;padding:36px 20px 60px}

        /* Header */
        .notif-page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
        .notif-page-title{font-size:1.5rem;font-weight:900;color:#1B2336;margin:0;display:flex;align-items:center;gap:10px}
        .notif-page-title .badge-count{background:#ef4444;color:#fff;font-size:.7rem;font-weight:800;border-radius:99px;padding:2px 8px;letter-spacing:.3px}
        .btn-marcar-todas{display:inline-flex;align-items:center;gap:7px;background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:9px 16px;font-size:.84rem;font-weight:700;font-family:'Saira',sans-serif;cursor:pointer;color:#374151;transition:background .15s}
        .btn-marcar-todas:hover{background:#f3f4f6}

        /* Type filter tabs */
        .notif-filter-tabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:20px}
        .notif-tab{display:inline-flex;align-items:center;gap:5px;padding:6px 14px;border-radius:20px;font-size:.8rem;font-weight:600;border:1.5px solid #e5e7eb;background:#fff;color:#6b7280;cursor:pointer;text-decoration:none;transition:all .15s}
        .notif-tab:hover{border-color:#1B2336;color:#1B2336}
        .notif-tab.active{background:#1B2336;color:#fff;border-color:#1B2336}
        .notif-tab-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0}

        /* Notification cards */
        .notif-list-page{display:flex;flex-direction:column;gap:8px}
        .notif-card{display:flex;gap:14px;align-items:flex-start;background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:16px 18px;text-decoration:none;color:inherit;transition:box-shadow .15s,border-color .15s;position:relative;overflow:hidden}
        .notif-card:hover{box-shadow:0 4px 18px rgba(0,0,0,.08);border-color:#d1d5db}
        .notif-card.unread{border-left:3px solid var(--tipo-color, #6b7280);background:#fff}
        .notif-card.unread .notif-card-title{font-weight:800}
        .notif-card-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px}
        .notif-card-body{flex:1;min-width:0}
        .notif-card-title{font-size:.9rem;font-weight:600;color:#1B2336;margin:0 0 3px;line-height:1.4}
        .notif-card-sub{font-size:.8rem;color:#6b7280;margin:0 0 6px;line-height:1.45;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
        .notif-card-meta{display:flex;align-items:center;gap:8px;font-size:.74rem;color:#9ca3af}
        .notif-card-type{display:inline-flex;align-items:center;gap:3px;padding:1px 8px;border-radius:99px;font-size:.7rem;font-weight:700}
        .notif-card-actions{display:flex;align-items:center;gap:6px;flex-shrink:0}
        .notif-btn-read{background:none;border:1px solid #e5e7eb;border-radius:8px;padding:5px 10px;font-size:.75rem;font-weight:600;cursor:pointer;color:#6b7280;font-family:'Saira',sans-serif;transition:all .15s;text-decoration:none}
        .notif-btn-read:hover{background:#f3f4f6;color:#374151}
        .notif-read-check{color:#10b981;font-size:.85rem;opacity:.6}

        /* Empty state */
        .notif-empty-state{text-align:center;padding:56px 20px;color:#6b7280}
        .notif-empty-state svg{opacity:.3;margin-bottom:16px}
        .notif-empty-state h3{font-size:1.1rem;font-weight:700;color:#1B2336;margin-bottom:6px}
        .notif-empty-state p{font-size:.88rem}

        /* Pagination */
        .notif-pagination{display:flex;justify-content:center;gap:6px;margin-top:28px;flex-wrap:wrap}
        .notif-pag-btn{display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:9px;font-size:.85rem;font-weight:600;border:1px solid #e5e7eb;background:#fff;color:#374151;text-decoration:none;transition:all .15s}
        .notif-pag-btn:hover{background:#f3f4f6}
        .notif-pag-btn.active{background:#1B2336;color:#fff;border-color:#1B2336}
        .notif-pag-btn.disabled{opacity:.4;pointer-events:none}
    </style>
</head>
<body>
    <?php require __DIR__ . '/../layout/header.php'; ?>

    <div class="notif-page">

        <!-- Page header -->
        <div class="notif-page-header">
            <h1 class="notif-page-title">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="M15 17H9m3.5 3.5a2 2 0 01-3 0M18 8A6 6 0 106 8c0 5-3 7-3 7h18s-3-2-3-7"/>
                </svg>
                Notificaciones
                <?php if ($noLeidas > 0): ?>
                    <span class="badge-count"><?= $noLeidas ?> nuevas</span>
                <?php endif; ?>
            </h1>

            <?php if ($noLeidas > 0): ?>
                <form method="POST" action="<?= BASE_URL ?>/index.php?url=notificaciones">
                    <input type="hidden" name="accion" value="marcar-todas">
                    <?php if ($filtroTipo): ?><input type="hidden" name="tipo" value="<?= htmlspecialchars($filtroTipo) ?>"><?php endif; ?>
                    <button type="submit" class="btn-marcar-todas">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg>
                        Marcar todas como leídas
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Type filters -->
        <div class="notif-filter-tabs">
            <a href="<?= buildNotifUrl(['tipo' => '', 'p' => 1]) ?>"
               class="notif-tab <?= $filtroTipo === '' ? 'active' : '' ?>">
                Todas
                <span style="font-size:.72rem;font-weight:500;opacity:.7">(<?= $totalRows ?>)</span>
            </a>
            <?php foreach ($tiposConfig as $tipoKey => $tc): ?>
                <a href="<?= buildNotifUrl(['tipo' => $tipoKey, 'p' => 1]) ?>"
                   class="notif-tab <?= $filtroTipo === $tipoKey ? 'active' : '' ?>">
                    <span class="notif-tab-dot" style="background:<?= $tc['color'] ?>"></span>
                    <?= $tc['label'] ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- List -->
        <?php if (empty($notificaciones)): ?>
            <div class="notif-empty-state">
                <svg width="52" height="52" fill="none" stroke="currentColor" stroke-width="1.3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="M15 17H9m3.5 3.5a2 2 0 01-3 0M18 8A6 6 0 106 8c0 5-3 7-3 7h18s-3-2-3-7"/>
                </svg>
                <h3>Sin notificaciones</h3>
                <p>No hay notificaciones<?= $filtroTipo ? ' de este tipo' : '' ?> por el momento.</p>
            </div>
        <?php else: ?>
            <div class="notif-list-page">
                <?php foreach ($notificaciones as $n):
                    $tc      = $tiposConfig[$n['tipo']] ?? $tiposConfig['info'];
                    $esLeida = (bool)$n['leido'];
                    $leerUrl = BASE_URL . '/index.php?url=notificaciones&leer=' . $n['id']
                               . ($n['url_accion'] ? '&goto=' . urlencode($n['url_accion']) : '')
                               . ($filtroTipo ? '&tipo=' . urlencode($filtroTipo) : '')
                               . '&p=' . $page;
                ?>
                <div class="notif-card <?= $esLeida ? '' : 'unread' ?>"
                     style="<?= !$esLeida ? '--tipo-color:' . $tc['color'] : '' ?>">

                    <div class="notif-card-icon" style="background:<?= $tc['bg'] ?>">
                        <svg width="18" height="18" fill="none" stroke="<?= $tc['color'] ?>" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="<?= $tc['icon'] ?>"/>
                        </svg>
                    </div>

                    <div class="notif-card-body">
                        <p class="notif-card-title"><?= htmlspecialchars($n['titulo']) ?></p>
                        <?php if (!empty($n['cuerpo'])): ?>
                            <p class="notif-card-sub"><?= htmlspecialchars($n['cuerpo']) ?></p>
                        <?php endif; ?>
                        <div class="notif-card-meta">
                            <span class="notif-card-type" style="background:<?= $tc['bg'] ?>;color:<?= $tc['color'] ?>">
                                <?= $tc['label'] ?>
                            </span>
                            <span><?= notifTimeAgo($n['creado_en']) ?></span>
                            <?php if ($esLeida): ?>
                                <span class="notif-read-check">✓ Leída</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="notif-card-actions">
                        <?php if ($n['url_accion']): ?>
                            <a href="<?= htmlspecialchars($leerUrl) ?>"
                               class="notif-btn-read">
                                <?= $esLeida ? 'Ver' : 'Ver →' ?>
                            </a>
                        <?php elseif (!$esLeida): ?>
                            <a href="<?= htmlspecialchars($leerUrl) ?>"
                               class="notif-btn-read">
                                Marcar leída
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPags > 1): ?>
                <div class="notif-pagination">
                    <a href="<?= buildNotifUrl(['p' => max(1, $page-1)]) ?>"
                       class="notif-pag-btn <?= $page <= 1 ? 'disabled' : '' ?>">‹</a>
                    <?php for ($i = max(1, $page-2); $i <= min($totalPags, $page+2); $i++): ?>
                        <a href="<?= buildNotifUrl(['p' => $i]) ?>"
                           class="notif-pag-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    <a href="<?= buildNotifUrl(['p' => min($totalPags, $page+1)]) ?>"
                       class="notif-pag-btn <?= $page >= $totalPags ? 'disabled' : '' ?>">›</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>

    <?php require __DIR__ . '/../layout/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
