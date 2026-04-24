<?php /* Logs de actividad del CRM */ ?>

<div class="crm-page-header">
  <div>
    <h1>Logs de Actividad</h1>
    <p>Registro completo de todas las acciones realizadas en el CRM. Total: <strong><?= $totalRows ?></strong></p>
  </div>
</div>

<!-- Toolbar -->
<form method="GET" action="<?= $crmFormBase ?>" id="logsForm">
  <?= $crmFormHidden ?>
  <input type="hidden" name="sec" value="logs">
  <div class="crm-toolbar">
    <div class="crm-search-wrap">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
      <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar acción o usuario…" class="crm-search-input" id="logsSearch">
    </div>
    <?php if (!empty($tipos)): ?>
    <select name="tipo" class="crm-filter-select" onchange="this.form.submit()">
      <option value="">Todos los tipos</option>
      <?php foreach ($tipos as $t): ?>
        <option value="<?= htmlspecialchars($t) ?>" <?= $tipo===$t?'selected':'' ?>><?= ucfirst(htmlspecialchars($t)) ?></option>
      <?php endforeach; ?>
    </select>
    <?php endif; ?>
    <button type="submit" class="crm-btn crm-btn-secondary">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
      Filtrar
    </button>
    <?php if ($q || $tipo): ?>
    <a href="<?= $crmBase ?>logs" class="crm-btn crm-btn-secondary">Limpiar</a>
    <?php endif; ?>
  </div>
</form>

<!-- Table -->
<div class="crm-table-wrap">
  <table class="crm-table">
    <thead>
      <tr>
        <th style="width:36px">#</th>
        <th>Acción</th>
        <th>Tipo</th>
        <th>Usuario</th>
        <th>Fecha y hora</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($logs)): ?>
      <tr><td colspan="5" style="text-align:center;padding:50px;color:var(--crm-muted)">
        <div class="crm-empty">
          <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
          <h3>Sin registros</h3>
          <p>No hay actividad registrada con ese filtro.</p>
        </div>
      </td></tr>
      <?php else: ?>
      <?php foreach ($logs as $i => $log):
        $badgeColor = match($log['tipo'] ?? 'info') {
          'usuario'   => 'blue',
          'curso'     => 'purple',
          'campana'   => 'orange',
          'incidencia'=> 'red',
          'mensaje'   => 'green',
          'sistema'   => '',
          default     => '',
        };
        $dotClass = match($log['tipo'] ?? 'info') {
          'usuario'    => 'success',
          'curso'      => 'info',
          'campana'    => 'warning',
          'incidencia' => 'danger',
          default      => 'info',
        };
      ?>
      <tr>
        <td style="font-size:11px;color:var(--crm-muted);font-family:monospace"><?= $totalRows - (($page-1)*25) - $i ?></td>
        <td>
          <div style="display:flex;align-items:center;gap:8px">
            <div class="crm-activity-dot <?= $dotClass ?>" style="flex-shrink:0"></div>
            <span style="font-size:13.5px"><?= htmlspecialchars($log['titulo']) ?></span>
          </div>
          <?php if (!empty($log['descripcion'])): ?>
            <div style="font-size:12px;color:var(--crm-muted);margin-top:2px;padding-left:18px"><?= htmlspecialchars($log['descripcion']) ?></div>
          <?php endif; ?>
        </td>
        <td>
          <span class="crm-badge <?= $badgeColor ?>" style="text-transform:capitalize">
            <?= htmlspecialchars($log['tipo'] ?? 'sistema') ?>
          </span>
        </td>
        <td>
          <?php if (!empty($log['usuario_nombre'])): ?>
          <div style="font-size:13px"><?= htmlspecialchars($log['usuario_nombre']) ?></div>
          <div style="font-size:11px;color:var(--crm-muted)"><?= htmlspecialchars($log['usuario_email'] ?? '') ?></div>
          <?php else: ?>
          <span style="font-size:12px;color:var(--crm-muted)">—</span>
          <?php endif; ?>
        </td>
        <td style="font-size:12.5px;color:var(--crm-muted);white-space:nowrap">
          <?= date('d/m/Y', strtotime($log['creado_en'])) ?>
          <span style="display:block;font-size:11px"><?= date('H:i:s', strtotime($log['creado_en'])) ?></span>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <?php if ($totalPags > 1): ?>
  <div class="crm-pagination">
    <div class="crm-pagination-info">Página <?= $page ?> de <?= $totalPags ?> — <?= $totalRows ?> registros</div>
    <div class="crm-pag-btns">
      <?php if ($page > 1): ?>
        <a class="crm-pag-btn" href="<?= $crmBase ?>logs&pag=<?= $page-1 ?>&q=<?= urlencode($q) ?>&tipo=<?= urlencode($tipo) ?>">‹</a>
      <?php endif; ?>
      <?php for ($i = max(1,$page-2); $i <= min($totalPags,$page+2); $i++): ?>
        <a class="crm-pag-btn <?= $i===$page?'active':'' ?>" href="<?= $crmBase ?>logs&pag=<?= $i ?>&q=<?= urlencode($q) ?>&tipo=<?= urlencode($tipo) ?>"><?= $i ?></a>
      <?php endfor; ?>
      <?php if ($page < $totalPags): ?>
        <a class="crm-pag-btn" href="<?= $crmBase ?>logs&pag=<?= $page+1 ?>&q=<?= urlencode($q) ?>&tipo=<?= urlencode($tipo) ?>">›</a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
document.getElementById('logsSearch')?.addEventListener('input', CRM.debounce(() => {
  document.getElementById('logsForm').submit();
}, 600));
</script>
