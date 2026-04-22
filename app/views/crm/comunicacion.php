<?php /* Módulo de Comunicación — mensajes de curso + incidencias */ ?>

<div class="crm-page-header">
  <div>
    <h1>Comunicación</h1>
    <p>Gestiona mensajes entre alumnos e instructores, e incidencias de soporte.</p>
  </div>
  <div class="crm-page-actions">
    <button class="crm-btn crm-btn-primary" onclick="openModal('modalIncidencia')">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
      Nueva incidencia
    </button>
  </div>
</div>

<!-- Tabs -->
<div class="crm-tabs">
  <button class="crm-tab <?= $tab==='mensajes'?'active':'' ?>" onclick="cambiarTab('mensajes')">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
    Mensajes de curso
    <?php if (count($cursosConMensajes)): ?>
      <span class="crm-tab-count"><?= count($cursosConMensajes) ?></span>
    <?php endif; ?>
  </button>
  <button class="crm-tab <?= $tab==='incidencias'?'active':'' ?>" onclick="cambiarTab('incidencias')">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    Incidencias
    <?php
      $abiertas = array_filter($incidencias, fn($i) => $i['estado'] !== 'cerrada');
      if (count($abiertas)):
    ?>
      <span class="crm-tab-count"><?= count($abiertas) ?></span>
    <?php endif; ?>
  </button>
</div>

<!-- Tab: Mensajes de curso -->
<div class="crm-tab-panel <?= $tab==='mensajes'?'active':'' ?>" id="panelMensajes">
  <div class="crm-comm-layout">

    <!-- Sidebar: course list -->
    <div class="crm-comm-sidebar">
      <div class="crm-comm-sidebar-header">
        <h3>Conversaciones por curso</h3>
        <div class="crm-search-wrap" style="max-width:100%">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
          <input type="text" placeholder="Filtrar curso…" class="crm-search-input" id="searchCursoMsg" style="font-size:12.5px">
        </div>
      </div>
      <div class="crm-comm-list" id="cursosCommList">
        <?php if (empty($cursosConMensajes)): ?>
          <div style="padding:30px 16px;text-align:center;color:var(--crm-muted);font-size:13px">
            Sin mensajes de curso aún.
          </div>
        <?php else: ?>
          <?php foreach ($cursosConMensajes as $cm): ?>
          <div class="crm-comm-item <?= $cursoFiltro==$cm['id']?'active':'' ?>"
               onclick="seleccionarCurso(<?= $cm['id'] ?>)"
               data-nombre="<?= htmlspecialchars(strtolower($cm['titulo'])) ?>">
            <div class="crm-user-row-avatar" style="width:36px;height:36px;font-size:13px;flex-shrink:0">
              <?= mb_strtoupper(mb_substr($cm['titulo'],0,1,'UTF-8'), 'UTF-8') ?>
            </div>
            <div class="crm-comm-item-info">
              <div class="crm-comm-item-name"><?= htmlspecialchars(mb_strimwidth($cm['titulo'],0,30,'…')) ?></div>
              <div class="crm-comm-item-preview"><?= $cm['total'] ?> mensaje(s)</div>
            </div>
            <div class="crm-comm-item-meta">
              <?php if ($cm['no_leidos'] > 0): ?>
                <div class="crm-unread-dot" title="<?= $cm['no_leidos'] ?> sin leer"></div>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Chat area -->
    <div class="crm-chat-area">
      <?php if (!$cursoFiltro): ?>
        <div class="crm-chat-empty">
          <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
          <h3 style="font-size:15px">Selecciona un curso</h3>
          <p style="font-size:13px">Elige un curso de la lista para ver sus mensajes.</p>
        </div>
      <?php else: ?>
        <div class="crm-chat-header">
          <div class="crm-user-row-avatar" style="width:36px;height:36px">
            <?= mb_strtoupper(mb_substr($cursoSeleccionado['titulo']??'C',0,1,'UTF-8'), 'UTF-8') ?>
          </div>
          <div class="crm-chat-header-info">
            <div class="crm-chat-header-name"><?= htmlspecialchars($cursoSeleccionado['titulo'] ?? '') ?></div>
            <div class="crm-chat-header-sub">Conversación del curso · <?= count($mensajes) ?> mensajes</div>
          </div>
          <div style="margin-left:auto">
            <span class="crm-badge activo">Activo</span>
          </div>
        </div>

        <div class="crm-chat-messages" id="chatMessages">
          <?php if (empty($mensajes)): ?>
            <div class="crm-chat-empty">
              <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
              <p>Sin mensajes en este curso todavía.</p>
            </div>
          <?php else: ?>
            <?php foreach ($mensajes as $m):
              $esMio = $m['remitente_id'] == $usuario['id'];
            ?>
            <div class="crm-msg <?= $esMio?'mine':'theirs' ?>">
              <div class="crm-msg-bubble"><?= nl2br(htmlspecialchars($m['cuerpo'])) ?></div>
              <div class="crm-msg-meta">
                <?= $esMio?'Tú':htmlspecialchars($m['remitente_nombre']) ?> ·
                <?= date('d/m H:i', strtotime($m['creado_en'])) ?>
              </div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <div class="crm-chat-input-area">
          <textarea class="crm-chat-input" id="chatInput" placeholder="Escribe un mensaje…" rows="1" data-autoresize></textarea>
          <button class="crm-btn crm-btn-primary" onclick="enviarMensaje()">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
          </button>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<!-- Tab: Incidencias -->
<div class="crm-tab-panel <?= $tab==='incidencias'?'active':'' ?>" id="panelIncidencias">
  <?php if (empty($incidencias)): ?>
  <div class="crm-empty">
    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <h3>Sin incidencias</h3>
    <p>No hay tickets abiertos. ¡Todo en orden!</p>
  </div>
  <?php else: ?>
  <div class="crm-table-wrap">
    <table class="crm-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Asunto</th>
          <th>Usuario</th>
          <th>Prioridad</th>
          <th>Estado</th>
          <th>Asignado a</th>
          <th>Fecha</th>
          <th style="text-align:right">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($incidencias as $inc): ?>
        <tr>
          <td style="font-size:12px;color:var(--crm-muted)">#<?= $inc['id'] ?></td>
          <td style="font-weight:600;max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
            <?= htmlspecialchars($inc['asunto']) ?>
          </td>
          <td style="font-size:12.5px"><?= htmlspecialchars($inc['usuario_nombre']) ?></td>
          <td><span class="crm-badge <?= $inc['prioridad'] ?>"><?= ucfirst($inc['prioridad']) ?></span></td>
          <td><span class="crm-badge <?= $inc['estado'] ?>"><?= ucfirst(str_replace('_',' ',$inc['estado'])) ?></span></td>
          <td style="font-size:12.5px"><?= $inc['asignado_nombre'] ? htmlspecialchars($inc['asignado_nombre']) : '<span style="color:var(--crm-muted)">—</span>' ?></td>
          <td style="font-size:12px;color:var(--crm-muted)"><?= date('d/m/Y H:i', strtotime($inc['creado_en'])) ?></td>
          <td style="text-align:right">
            <div style="display:flex;gap:4px;justify-content:flex-end">
              <?php if ($inc['estado'] !== 'cerrada'): ?>
              <select class="crm-filter-select" style="font-size:11.5px;padding:4px 8px" onchange="cambiarEstado(<?= $inc['id'] ?>, this.value)">
                <option value="abierta"    <?= $inc['estado']==='abierta'   ?'selected':'' ?>>Abierta</option>
                <option value="en_proceso" <?= $inc['estado']==='en_proceso'?'selected':'' ?>>En proceso</option>
                <option value="cerrada"    <?= $inc['estado']==='cerrada'   ?'selected':'' ?>>Cerrada</option>
              </select>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <!-- Pagination incidencias -->
    <?php if ($totalPagsInc > 1): ?>
    <div class="crm-pagination">
      <div class="crm-pagination-info">Página <?= $pageInc ?> de <?= $totalPagsInc ?></div>
      <div class="crm-pag-btns">
        <?php if ($pageInc > 1): ?>
          <a class="crm-pag-btn" href="<?= $crmBase ?>comunicacion&tab=incidencias&pinc=<?= $pageInc-1 ?>">‹</a>
        <?php endif; ?>
        <?php for ($i=max(1,$pageInc-2); $i<=min($totalPagsInc,$pageInc+2); $i++): ?>
          <a class="crm-pag-btn <?= $i===$pageInc?'active':'' ?>" href="<?= $crmBase ?>comunicacion&tab=incidencias&pinc=<?= $i ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($pageInc < $totalPagsInc): ?>
          <a class="crm-pag-btn" href="<?= $crmBase ?>comunicacion&tab=incidencias&pinc=<?= $pageInc+1 ?>">›</a>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<!-- Modal: Nueva incidencia -->
<div class="modal fade crm-modal" id="modalIncidencia" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nueva incidencia</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="crm-form-row">
          <div class="crm-form-group" style="flex:2">
            <label class="crm-label">Asunto *</label>
            <input type="text" class="crm-input" id="incAsunto" placeholder="Describe brevemente el problema">
          </div>
          <div class="crm-form-group">
            <label class="crm-label">Prioridad</label>
            <select class="crm-select" id="incPrioridad">
              <option value="baja">Baja</option>
              <option value="normal" selected>Normal</option>
              <option value="alta">Alta</option>
              <option value="urgente">Urgente</option>
            </select>
          </div>
        </div>
        <div class="crm-form-group">
          <label class="crm-label">Descripción del problema *</label>
          <textarea class="crm-textarea" id="incMensaje" rows="4" placeholder="Describe el problema con detalle…"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="crm-btn crm-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="crm-btn crm-btn-primary" onclick="crearIncidencia()">Crear incidencia</button>
      </div>
    </div>
  </div>
</div>

<script>
const CURSO_ACTUAL = <?= $cursoFiltro ?: 'null' ?>;

function cambiarTab(tab) {
  document.querySelectorAll('.crm-tab').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.crm-tab-panel').forEach(p => p.classList.remove('active'));
  event.target.closest('.crm-tab').classList.add('active');
  document.getElementById('panel' + tab.charAt(0).toUpperCase() + tab.slice(1)).classList.add('active');
}

function seleccionarCurso(id) {
  window.location.href = window.CRM_NAV_BASE + `comunicacion&curso=${id}&tab=mensajes`;
}

// Scroll to bottom of chat
const chatMsgs = document.getElementById('chatMessages');
if (chatMsgs) chatMsgs.scrollTop = chatMsgs.scrollHeight;

async function enviarMensaje() {
  const input = document.getElementById('chatInput');
  const cuerpo = input.value.trim();
  if (!cuerpo || !CURSO_ACTUAL) return;

  const res = await CRM.api('enviar_mensaje', { curso_id: CURSO_ACTUAL, cuerpo });
  if (res.ok) {
    input.value = '';
    input.style.height = 'auto';
    const div = document.createElement('div');
    div.className = 'crm-msg mine';
    div.innerHTML = `<div class="crm-msg-bubble">${CRM.escapeHtml(cuerpo)}</div>
      <div class="crm-msg-meta">Tú · ahora mismo</div>`;
    chatMsgs.appendChild(div);
    chatMsgs.scrollTop = chatMsgs.scrollHeight;
  } else CRM.toast(res.error, 'error');
}

document.getElementById('chatInput')?.addEventListener('keydown', e => {
  if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); enviarMensaje(); }
});

async function crearIncidencia() {
  const asunto  = document.getElementById('incAsunto').value.trim();
  const mensaje  = document.getElementById('incMensaje').value.trim();
  const prior    = document.getElementById('incPrioridad').value;
  const res = await CRM.api('crear_incidencia', { asunto, mensaje, prioridad: prior });
  if (res.ok) { CRM.toast(res.mensaje, 'success'); closeModal('modalIncidencia'); setTimeout(()=>location.reload(),800); }
  else CRM.toast(res.error, 'error');
}

async function cambiarEstado(id, estado) {
  const res = await CRM.api('incidencia_estado', { id, estado });
  if (res.ok) CRM.toast(res.mensaje, 'success');
  else CRM.toast(res.error, 'error');
}

// Filter course list
document.getElementById('searchCursoMsg')?.addEventListener('input', e => {
  const q = e.target.value.toLowerCase();
  document.querySelectorAll('#cursosCommList .crm-comm-item').forEach(item => {
    item.style.display = item.dataset.nombre.includes(q) ? '' : 'none';
  });
});
</script>
