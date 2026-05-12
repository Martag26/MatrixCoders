<?php

/**
 * Página de Gestión de Incidencias — CRM
 * 
 * Interfaz moderna para visualizar, filtrar y responder incidencias de soporte.
 */

?>

<style>
  .inc-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
  }

  .inc-header-title {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .inc-header-title h1 {
    font-size: 24px;
    font-weight: 800;
    margin: 0;
  }

  .inc-badge-count {
    background: #fca5a5;
    color: #7f1d1d;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
  }

  .inc-toolbar {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 20px;
  }

  .inc-filter-btn {
    padding: 8px 16px;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
    transition: all .15s;
    color: #6b7280;
  }

  .inc-filter-btn.active {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
  }

  .inc-filter-btn:hover {
    border-color: #d1d5db;
  }

  .inc-filter-btn.active:hover {
    box-shadow: 0 4px 12px rgba(59, 130, 246, .3);
  }

  .inc-layout {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 16px;
  }

  @media (max-width: 1024px) {
    .inc-layout {
      grid-template-columns: 1fr;
    }
  }

  .inc-lista-panel {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
  }

  .inc-lista-header {
    padding: 16px;
    border-bottom: 1px solid #e5e7eb;
    font-weight: 700;
    font-size: 14px;
    color: #1f2937;
  }

  .inc-lista-items {
    display: flex;
    flex-direction: column;
    max-height: 600px;
    overflow-y: auto;
  }

  .inc-lista-item {
    padding: 14px 16px;
    border-bottom: 1px solid #f3f4f6;
    cursor: pointer;
    transition: background .15s;
  }

  .inc-lista-item:hover {
    background: #f9fafb;
  }

  .inc-lista-item.selected {
    background: #eff6ff;
    border-left: 3px solid #3b82f6;
    padding-left: 13px;
  }

  .inc-item-row {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 6px;
  }

  .inc-item-chips {
    display: flex;
    gap: 6px;
    margin-bottom: 6px;
  }

  .inc-chip {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 12px;
    white-space: nowrap;
  }

  .inc-chip.urgente { background: #fee2e2; color: #7f1d1d; }
  .inc-chip.alta { background: #fed7aa; color: #92400e; }
  .inc-chip.normal { background: #dbeafe; color: #0c4a6e; }
  .inc-chip.baja { background: #e5e7eb; color: #374151; }

  .inc-chip.abierta { background: #dcfce7; color: #166534; }
  .inc-chip.en_proceso { background: #fef3c7; color: #92400e; }
  .inc-chip.cerrada { background: #f3f4f6; color: #6b7280; }

  .inc-item-asunto {
    font-weight: 600;
    font-size: 13px;
    color: #1f2937;
    margin-bottom: 2px;
  }

  .inc-item-user {
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 4px;
  }

  .inc-item-footer {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: #9ca3af;
  }

  .inc-detalle-panel {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 0;
    display: none;
  }

  .inc-detalle-panel.active {
    display: block;
  }

  .inc-detalle-header {
    padding: 18px;
    border-bottom: 1px solid #e5e7eb;
  }

  .inc-detalle-title {
    font-size: 15px;
    font-weight: 800;
    color: #1f2937;
    margin: 0 0 12px;
  }

  .inc-detalle-meta {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    font-size: 12px;
  }

  .inc-meta-item {
    background: #f9fafb;
    padding: 10px;
    border-radius: 8px;
  }

  .inc-meta-label {
    color: #6b7280;
    font-weight: 600;
    margin-bottom: 3px;
  }

  .inc-meta-value {
    color: #1f2937;
    font-weight: 700;
  }

  .inc-actions {
    padding: 14px 18px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    gap: 8px;
  }

  .inc-detalle-content {
    max-height: 400px;
    overflow-y: auto;
    padding: 18px;
    border-bottom: 1px solid #e5e7eb;
  }

  .inc-respuesta {
    margin-bottom: 14px;
    display: flex;
    gap: 10px;
  }

  .inc-respuesta-avatar {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #eff6ff;
    color: #3b82f6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 12px;
    flex-shrink: 0;
  }

  .inc-respuesta-body {
    flex: 1;
  }

  .inc-respuesta-header {
    font-weight: 700;
    font-size: 12px;
    margin-bottom: 2px;
  }

  .inc-respuesta-role {
    font-size: 10px;
    background: #f0fdf4;
    color: #166534;
    padding: 1px 6px;
    border-radius: 4px;
    margin-left: 4px;
    font-weight: 700;
  }

  .inc-respuesta-meta {
    font-size: 11px;
    color: #9ca3af;
    margin-bottom: 6px;
  }

  .inc-respuesta-text {
    font-size: 13px;
    color: #374151;
    line-height: 1.6;
    white-space: pre-wrap;
    word-break: break-word;
    background: #f9fafb;
    padding: 10px;
    border-radius: 8px;
  }

  .inc-responder-form {
    padding: 18px;
    background: #f9fafb;
  }

  .inc-responder-label {
    font-size: 12px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 8px;
    display: block;
  }

  .inc-responder-textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    font-family: inherit;
    font-size: 13px;
    resize: vertical;
    min-height: 80px;
    margin-bottom: 10px;
    color: #1f2937;
  }

  .inc-responder-textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, .1);
  }

  .inc-responder-buttons {
    display: flex;
    gap: 8px;
  }

  .inc-empty {
    padding: 60px 30px;
    text-align: center;
    color: #9ca3af;
  }

  .inc-empty svg {
    width: 48px;
    height: 48px;
    opacity: .5;
    margin-bottom: 12px;
  }

  .inc-empty h3 {
    font-size: 15px;
    font-weight: 700;
    color: #6b7280;
    margin: 0;
  }

  .inc-empty p {
    font-size: 13px;
    margin: 4px 0 0;
  }
</style>

<!-- Cabecera + tabs globales -->
<div class="inc-page-header">
  <div class="inc-header-title">
    <h1>Comunicación</h1>
    <span class="inc-badge-count" id="inc-badge-count" style="display:none">
      <span id="inc-badge-num">0</span> abiertas
    </span>
  </div>
</div>

<div style="display:flex;gap:4px;background:#f1f5f9;border-radius:12px;padding:4px;margin-bottom:20px;max-width:360px">
  <button id="ctab-btn-inc" onclick="comTab('incidencias')"
    style="flex:1;padding:8px 14px;border:none;border-radius:9px;font-family:inherit;font-size:.85rem;font-weight:700;cursor:pointer;background:#fff;color:#1B2336;box-shadow:0 1px 5px rgba(0,0,0,.1)">
    Incidencias
  </button>
  <button id="ctab-btn-msg" onclick="comTab('mensajes')"
    style="flex:1;padding:8px 14px;border:none;border-radius:9px;font-family:inherit;font-size:.85rem;font-weight:700;cursor:pointer;background:transparent;color:#6b7280">
    Mensajes
  </button>
</div>

<!-- ══════════ TAB INCIDENCIAS ══════════ -->
<div id="ctab-incidencias">

<!-- Toolbar de filtros -->
<div class="inc-toolbar">
  <button class="inc-filter-btn active" onclick="incCambiarEstado('todas')">Todas</button>
  <button class="inc-filter-btn" onclick="incCambiarEstado('abierta')">Abiertas</button>
  <button class="inc-filter-btn" onclick="incCambiarEstado('en_proceso')">En proceso</button>
  <button class="inc-filter-btn" onclick="incCambiarEstado('cerrada')">Cerradas</button>
  
  <div style="width: 1px; height: 20px; background: #e5e7eb; margin: 0 6px"></div>
  
  <select id="inc-prioridad-select" class="crm-filter-select" style="padding: 8px 12px; font-size: 13px" onchange="incCambiarPrioridad(this.value)">
    <option value="">Todas las prioridades</option>
    <option value="baja">Baja</option>
    <option value="normal">Normal</option>
    <option value="alta">Alta</option>
    <option value="urgente">Urgente</option>
  </select>
</div>

<!-- Layout: Lista + Detalle -->
<div class="inc-layout">
  
  <!-- LISTA (izquierda) -->
  <div class="inc-lista-panel">
    <div class="inc-lista-header">Incidencias</div>
    <div class="inc-lista-items" id="inc-lista">
      <div class="inc-empty">
        <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path stroke-linecap="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        <p>Cargando incidencias…</p>
      </div>
    </div>
  </div>

  <!-- DETALLE (derecha) -->
  <div class="inc-detalle-panel" id="inc-detalle">
    
    <!-- Header -->
    <div class="inc-detalle-header">
      <p class="inc-detalle-title" id="inc-det-asunto">—</p>
      <div class="inc-detalle-meta">
        <div class="inc-meta-item">
          <div class="inc-meta-label">Estado</div>
          <div class="inc-meta-value" id="inc-det-estado">—</div>
        </div>
        <div class="inc-meta-item">
          <div class="inc-meta-label">Prioridad</div>
          <div class="inc-meta-value" id="inc-det-prioridad">—</div>
        </div>
        <div class="inc-meta-item">
          <div class="inc-meta-label">Usuario</div>
          <div class="inc-meta-value" id="inc-det-usuario">—</div>
        </div>
        <div class="inc-meta-item">
          <div class="inc-meta-label">Asignado a</div>
          <div class="inc-meta-value" id="inc-det-asignado">—</div>
        </div>
      </div>
    </div>

    <!-- Acciones -->
    <div class="inc-actions">
      <button class="crm-btn crm-btn-secondary crm-btn-sm" onclick="incAsignarme()" id="inc-btn-asignarme">
        Asignarme
      </button>
      <select id="inc-estado-select" class="crm-filter-select" style="padding: 6px 10px; font-size: 12px" onchange="incCambiarEstadoDetalle(this.value)">
        <option value="">Cambiar estado…</option>
        <option value="abierta">Abierta</option>
        <option value="en_proceso">En proceso</option>
        <option value="cerrada">Cerrada</option>
      </select>
    </div>

    <!-- Respuestas -->
    <div class="inc-detalle-content" id="inc-det-respuestas">
      <p style="color: #9ca3af; text-align: center; padding: 20px 0">Cargando respuestas…</p>
    </div>

    <!-- Formulario responder -->
    <div class="inc-responder-form">
      <label class="inc-responder-label">Añade una respuesta</label>
      <textarea class="inc-responder-textarea" id="inc-respuesta-text" placeholder="Escribe tu respuesta…"></textarea>
      <div class="inc-responder-buttons">
        <button class="crm-btn crm-btn-primary crm-btn-sm" onclick="incResponder()">Enviar respuesta</button>
      </div>
    </div>

  </div>

</div>

</div><!-- /ctab-incidencias -->

<!-- ══════════ TAB MENSAJES ══════════ -->
<div id="ctab-mensajes" style="display:none">

  <!-- Compose area (se rellena dinámicamente) -->
  <div id="msg-compose-panel"></div>

  <!-- Subtabs + botón nuevo -->
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:10px">
    <div style="display:flex;gap:0;border-bottom:2px solid #e5e7eb">
      <button id="msg-stab-recibidos" onclick="msgCargarLista('recibidos')"
        style="background:none;border:none;padding:8px 18px;font-family:inherit;font-size:.86rem;font-weight:800;color:#1B2336;cursor:pointer;border-bottom:2px solid #3b82f6;margin-bottom:-2px">
        Recibidos
      </button>
      <button id="msg-stab-enviados" onclick="msgCargarLista('enviados')"
        style="background:none;border:none;padding:8px 18px;font-family:inherit;font-size:.86rem;font-weight:600;color:#6b7280;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px">
        Enviados
      </button>
    </div>
    <button onclick="msgMostrarCompose()"
      style="display:inline-flex;align-items:center;gap:6px;font-size:.84rem;font-weight:700;background:#1B2336;color:#fff;border:none;border-radius:10px;padding:9px 18px;cursor:pointer;font-family:inherit">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Nuevo mensaje
    </button>
  </div>

  <!-- Lista + Detalle -->
  <div style="display:grid;grid-template-columns:1fr 420px;gap:14px">
    <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:14px;overflow:hidden;max-height:580px;overflow-y:auto">
      <div id="msg-lista">
        <div style="padding:40px;text-align:center;color:#9ca3af;font-size:.84rem">Cargando…</div>
      </div>
    </div>
    <div id="msg-detalle-area" style="background:#fff;border:1.5px solid #e5e7eb;border-radius:14px;overflow:hidden;display:none;max-height:580px;overflow-y:auto"></div>
  </div>

</div><!-- /ctab-mensajes -->

<script>
const INC_ADMIN_ID = <?= (int)$_SESSION['usuario_id'] ?>;
let incEstadoActual = 'todas';
let incPrioridadActual = '';
let incDetalleActual = null;

/**
 * Cargar lista de incidencias
 */
async function incCargarLista(estado, prioridad) {
  const listaEl = document.getElementById('inc-lista');
  listaEl.innerHTML = '<div class="inc-empty"><svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg><p>Cargando…</p></div>';

  try {
    const res = await CRM.api('incidencias_lista', { estado, prioridad });
    if (!res.ok) throw new Error(res.error);

    const inc = res.incidencias || [];

    // Contar abiertas + en_proceso
    const conteo = inc.filter(i => i.estado !== 'cerrada').length;
    const badge = document.getElementById('inc-badge-count');
    if (conteo > 0) {
      document.getElementById('inc-badge-num').textContent = conteo;
      badge.style.display = '';
    } else {
      badge.style.display = 'none';
    }

    if (!inc.length) {
      listaEl.innerHTML = '<div class="inc-empty"><svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><h3>Sin incidencias</h3><p>No hay tickets en esta categoría</p></div>';
      return;
    }

    listaEl.innerHTML = inc.map(i => `
      <div class="inc-lista-item ${incDetalleActual?.id === i.id ? 'selected' : ''}" onclick="incVerDetalle(${i.id})">
        <div class="inc-item-chips">
          <span class="inc-chip ${i.prioridad === 'urgente' ? 'urgente' : i.prioridad}">${incFormatPrioridad(i.prioridad)}</span>
          <span class="inc-chip ${i.estado}">${incFormatEstado(i.estado)}</span>
        </div>
        <div class="inc-item-asunto">${CRM.escapeHtml(i.asunto)}</div>
        <div class="inc-item-user">${CRM.escapeHtml(i.nombre_usuario)} · ${CRM.escapeHtml(i.email_usuario)}</div>
        <div class="inc-item-footer">
          <span>${incFormatFecha(i.creado_en)}</span>
          <span>${i.num_respuestas} respuesta${i.num_respuestas !== 1 ? 's' : ''}</span>
        </div>
      </div>
    `).join('');
  } catch (e) {
    listaEl.innerHTML = `<div class="inc-empty"><p style="color: #dc2626">Error: ${CRM.escapeHtml(e.message)}</p></div>`;
  }
}

/**
 * Ver detalle de incidencia
 */
async function incVerDetalle(id) {
  try {
    const res = await CRM.api('incidencia_detalle', { id });
    if (!res.ok) throw new Error(res.error);

    incDetalleActual = res.incidencia;
    const respuestas = res.respuestas || [];

    // Marcar la fila como seleccionada
    document.querySelectorAll('.inc-lista-item').forEach(el => el.classList.remove('selected'));
    document.querySelector(`.inc-lista-item[onclick="incVerDetalle(${id})"]`)?.classList.add('selected');

    // Rellenar header
    document.getElementById('inc-det-asunto').textContent = CRM.escapeHtml(incDetalleActual.asunto);
    document.getElementById('inc-det-estado').textContent = incFormatEstado(incDetalleActual.estado);
    document.getElementById('inc-det-prioridad').textContent = incFormatPrioridad(incDetalleActual.prioridad);
    document.getElementById('inc-det-usuario').textContent = CRM.escapeHtml(incDetalleActual.nombre_usuario);
    document.getElementById('inc-det-asignado').textContent = incDetalleActual.nombre_asignado ? CRM.escapeHtml(incDetalleActual.nombre_asignado) : '—';

    // Botón asignarme
    const btnAsignarm = document.getElementById('inc-btn-asignarme');
    if (incDetalleActual.asignado_a === INC_ADMIN_ID) {
      btnAsignarm.textContent = 'Ya asignada a ti';
      btnAsignarm.disabled = true;
    } else {
      btnAsignarm.textContent = 'Asignarme';
      btnAsignarm.disabled = false;
    }

    // Select estado
    document.getElementById('inc-estado-select').value = '';

    // Rellenar respuestas
    const respEl = document.getElementById('inc-det-respuestas');
    if (!respuestas.length) {
      respEl.innerHTML = '<div style="padding: 20px; text-align: center; color: #9ca3af; font-size: 13px">Sin respuestas aún. Sé el primero en responder.</div>';
    } else {
      respEl.innerHTML = respuestas.map((r, idx) => {
        const inicial = (r.nombre_autor || '?')[0].toUpperCase();
        const fecha = incFormatFecha(r.creado_en);
        const roleTag = r.rol_autor && r.rol_autor !== 'USUARIO' ? `<span class="inc-respuesta-role">${r.rol_autor}</span>` : '';
        return `
          <div class="inc-respuesta">
            <div class="inc-respuesta-avatar">${inicial}</div>
            <div class="inc-respuesta-body">
              <div class="inc-respuesta-header">${CRM.escapeHtml(r.nombre_autor || '?')}${roleTag}</div>
              <div class="inc-respuesta-meta">${fecha}</div>
              <div class="inc-respuesta-text">${CRM.escapeHtml(r.mensaje)}</div>
            </div>
          </div>
        `;
      }).join('');
    }

    // Limpiar textarea
    document.getElementById('inc-respuesta-text').value = '';

    // Mostrar panel detalle
    document.getElementById('inc-detalle').classList.add('active');
  } catch (e) {
    CRM.toast('Error: ' + e.message, 'error');
  }
}

/**
 * Responder a incidencia
 */
async function incResponder() {
  if (!incDetalleActual) return;
  const mensaje = document.getElementById('inc-respuesta-text').value.trim();
  if (!mensaje) {
    CRM.toast('Escribe un mensaje', 'warning');
    return;
  }

  try {
    const res = await CRM.api('incidencia_responder', {
      incidencia_id: incDetalleActual.id,
      mensaje
    });
    if (!res.ok) throw new Error(res.error);

    CRM.toast('Respuesta enviada', 'success');
    incVerDetalle(incDetalleActual.id);
  } catch (e) {
    CRM.toast('Error: ' + e.message, 'error');
  }
}

/**
 * Asignarme la incidencia
 */
async function incAsignarme() {
  if (!incDetalleActual) return;
  try {
    const res = await CRM.api('incidencia_estado', {
      id: incDetalleActual.id,
      asignado_a: INC_ADMIN_ID
    });
    if (!res.ok) throw new Error(res.error);

    CRM.toast('Incidencia asignada', 'success');
    incVerDetalle(incDetalleActual.id);
    incCargarLista(incEstadoActual, incPrioridadActual);
  } catch (e) {
    CRM.toast('Error: ' + e.message, 'error');
  }
}

/**
 * Cambiar estado desde select
 */
async function incCambiarEstadoDetalle(estado) {
  if (!estado || !incDetalleActual) return;
  try {
    const res = await CRM.api('incidencia_estado', {
      id: incDetalleActual.id,
      estado
    });
    if (!res.ok) throw new Error(res.error);

    CRM.toast('Estado actualizado', 'success');
    incVerDetalle(incDetalleActual.id);
    incCargarLista(incEstadoActual, incPrioridadActual);
  } catch (e) {
    CRM.toast('Error: ' + e.message, 'error');
  }
}

/**
 * Cambiar filtro estado
 */
function incCambiarEstado(estado) {
  incEstadoActual = estado;
  document.querySelectorAll('.inc-filter-btn').forEach((btn, idx) => {
    if (idx < 4) btn.classList.toggle('active', btn.textContent.toLowerCase().includes(estado.replace('_', ' ')) || (estado === 'todas' && idx === 0));
  });
  incCargarLista(estado, incPrioridadActual);
}

/**
 * Cambiar filtro prioridad
 */
function incCambiarPrioridad(prioridad) {
  incPrioridadActual = prioridad;
  incCargarLista(incEstadoActual, prioridad);
}

/**
 * Formatear estado
 */
function incFormatEstado(estado) {
  const map = {
    'abierta': 'Abierta',
    'en_proceso': 'En proceso',
    'cerrada': 'Cerrada'
  };
  return map[estado] || estado;
}

/**
 * Formatear prioridad
 */
function incFormatPrioridad(prioridad) {
  const map = {
    'urgente': '🔴 Urgente',
    'alta': '🟠 Alta',
    'normal': '🔵 Normal',
    'baja': '⚪ Baja'
  };
  return map[prioridad] || prioridad;
}

/**
 * Formatear fecha
 */
function incFormatFecha(fecha) {
  if (!fecha) return '—';
  const d = new Date(fecha.replace(' ', 'T') + 'Z');
  const hoy = new Date();
  const ayer = new Date(hoy);
  ayer.setDate(ayer.getDate() - 1);
  
  const esMismoFecha = (d1, d2) => d1.toDateString() === d2.toDateString();
  
  if (esMismoFecha(d, hoy)) {
    return d.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
  } else if (esMismoFecha(d, ayer)) {
    return 'Ayer';
  } else {
    return d.toLocaleDateString('es-ES', { month: 'short', day: 'numeric' });
  }
}

// Cargar lista al iniciar
document.addEventListener('DOMContentLoaded', () => {
  incCargarLista('todas', '');
});

// ── Tab switcher global ────────────────────────────────────────────────────────
function comTab(tab) {
  const showInc = tab === 'incidencias';
  document.getElementById('ctab-incidencias').style.display = showInc ? '' : 'none';
  document.getElementById('ctab-mensajes').style.display    = showInc ? 'none' : '';
  document.getElementById('ctab-btn-inc').style.cssText = showInc
    ? 'flex:1;padding:8px 14px;border:none;border-radius:9px;font-family:inherit;font-size:.85rem;font-weight:700;cursor:pointer;background:#fff;color:#1B2336;box-shadow:0 1px 5px rgba(0,0,0,.1)'
    : 'flex:1;padding:8px 14px;border:none;border-radius:9px;font-family:inherit;font-size:.85rem;font-weight:700;cursor:pointer;background:transparent;color:#6b7280';
  document.getElementById('ctab-btn-msg').style.cssText = showInc
    ? 'flex:1;padding:8px 14px;border:none;border-radius:9px;font-family:inherit;font-size:.85rem;font-weight:700;cursor:pointer;background:transparent;color:#6b7280'
    : 'flex:1;padding:8px 14px;border:none;border-radius:9px;font-family:inherit;font-size:.85rem;font-weight:700;cursor:pointer;background:#fff;color:#1B2336;box-shadow:0 1px 5px rgba(0,0,0,.1)';
  if (!showInc && !msgCargados) msgCargarLista('recibidos');
}

// ── Mensajes generales ────────────────────────────────────────────────────────
let msgCargados = false;
let msgTabActual = 'recibidos';
let msgDetalleActual = null;
let msgUsuariosCache = null;

function msgTimeAgo(str) {
  if (!str) return '—';
  const diff = (Date.now() - new Date(str.replace(' ', 'T') + 'Z')) / 1000;
  if (diff < 60)     return 'ahora mismo';
  if (diff < 3600)   return 'hace ' + Math.floor(diff / 60) + 'm';
  if (diff < 86400)  return 'hace ' + Math.floor(diff / 3600) + 'h';
  if (diff < 604800) return 'hace ' + Math.floor(diff / 86400) + 'd';
  return new Date(str.replace(' ', 'T') + 'Z').toLocaleDateString('es-ES');
}

async function msgCargarLista(tab) {
  msgTabActual = tab;
  msgCargados  = true;
  // update subtab buttons
  ['recibidos','enviados'].forEach(t => {
    const btn = document.getElementById('msg-stab-' + t);
    if (btn) btn.style.fontWeight = t === tab ? '800' : '600', btn.style.borderBottomColor = t === tab ? '#3b82f6' : 'transparent', btn.style.color = t === tab ? '#1B2336' : '#6b7280';
  });
  const listaEl = document.getElementById('msg-lista');
  listaEl.innerHTML = '<div style="padding:30px;text-align:center;color:#9ca3af;font-size:.84rem">Cargando…</div>';

  const res = await CRM.api('mensajes_lista', { tab });
  const msgs = res.mensajes || [];

  if (!msgs.length) {
    listaEl.innerHTML = '<div style="padding:40px;text-align:center;color:#9ca3af;font-size:.84rem">No hay mensajes en esta bandeja.</div>';
    return;
  }

  listaEl.innerHTML = msgs.map(m => {
    const otro = tab === 'recibidos' ? m.nombre_emisor : m.nombre_receptor;
    const unread = tab === 'recibidos' && !+m.leido;
    return `
      <div onclick="msgVerDetalle(${m.id})" style="padding:14px 16px;border-bottom:1px solid #f0f0f0;cursor:pointer;background:${msgDetalleActual?.id===m.id?'#eff6ff':'#fff'};transition:background .1s;display:flex;align-items:flex-start;gap:12px" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='${msgDetalleActual?.id===m.id?'#eff6ff':'#fff'}'">
        <div style="width:36px;height:36px;border-radius:10px;background:#dbeafe;color:#2563eb;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.9rem;flex-shrink:0">${CRM.escapeHtml((otro||'?')[0].toUpperCase())}</div>
        <div style="flex:1;min-width:0">
          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:2px">
            <span style="font-size:.86rem;font-weight:${unread?'800':'700'};color:#1B2336">${CRM.escapeHtml(otro||'—')}</span>
            ${unread ? '<span style="width:7px;height:7px;border-radius:50%;background:#3b82f6;display:inline-block"></span>' : ''}
          </div>
          <div style="font-size:.83rem;font-weight:${unread?'700':'600'};color:#374151;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${CRM.escapeHtml(m.asunto||'(sin asunto)')}</div>
          <div style="font-size:.74rem;color:#9ca3af;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${CRM.escapeHtml(m.resumen||'')}</div>
        </div>
        <div style="font-size:.7rem;color:#9ca3af;white-space:nowrap;flex-shrink:0">${msgTimeAgo(m.enviado_en)}</div>
      </div>`;
  }).join('');
}

async function msgVerDetalle(id) {
  document.getElementById('msg-detalle-area').style.display = '';
  document.getElementById('msg-detalle-area').innerHTML = '<div style="padding:40px;text-align:center;color:#9ca3af;font-size:.84rem">Cargando…</div>';

  const res = await CRM.api('mensajes_detalle', { id });
  if (!res.ok) { CRM.toast('Error al cargar mensaje', 'error'); return; }
  const m = res.mensaje;
  msgDetalleActual = m;
  msgCargarLista(msgTabActual); // refrescar lista para highlight

  document.getElementById('msg-detalle-area').innerHTML = `
    <div style="padding:18px 20px;border-bottom:1px solid #f0f0f0">
      <div style="font-size:1rem;font-weight:800;color:#1B2336;margin-bottom:6px">${CRM.escapeHtml(m.asunto||'(sin asunto)')}</div>
      <div style="font-size:.78rem;color:#6b7280">
        De: <strong>${CRM.escapeHtml(m.nombre_emisor)}</strong> →
        Para: <strong>${CRM.escapeHtml(m.nombre_receptor)}</strong> · ${msgTimeAgo(m.enviado_en)}
      </div>
    </div>
    <div style="padding:18px 20px;font-size:.9rem;color:#374151;white-space:pre-wrap;word-break:break-word;line-height:1.7;min-height:120px">${CRM.escapeHtml(m.cuerpo||'')}</div>
    <div style="padding:14px 20px;border-top:1px solid #f0f0f0">
      <button onclick="msgMostrarCompose(${m.emisor_id}, ${CRM.escapeHtml(JSON.stringify(m.nombre_emisor))}, 'Re: ${CRM.escapeHtml(m.asunto||'')}' )"
        style="font-size:.82rem;font-weight:700;background:#1B2336;color:#fff;border:none;border-radius:9px;padding:8px 16px;cursor:pointer;font-family:inherit">
        Responder
      </button>
    </div>`;
}

async function msgMostrarCompose(preDestId, preDestNombre, preAsunto) {
  // Cargar usuarios si no están en cache
  if (!msgUsuariosCache) {
    const r = await CRM.api('usuarios_destinatarios', {});
    msgUsuariosCache = (r.usuarios || []);
  }
  const opts = msgUsuariosCache.map(u => `<option value="${u.id}" ${u.id==preDestId?'selected':''}>${CRM.escapeHtml(u.nombre)} (${CRM.escapeHtml(u.email)})</option>`).join('');

  document.getElementById('msg-compose-panel').innerHTML = `
    <div style="background:#fff;border:1.5px solid #e5e7eb;border-radius:14px;padding:20px;margin-bottom:16px">
      <div style="font-size:.92rem;font-weight:800;color:#1B2336;margin-bottom:14px">Nuevo mensaje</div>
      <div style="margin-bottom:12px">
        <label style="display:block;font-size:.78rem;font-weight:700;color:#374151;margin-bottom:4px">Para</label>
        <select id="msg-c-dest" style="width:100%;padding:8px 12px;border:1.5px solid #e5e7eb;border-radius:9px;font-family:inherit;font-size:.88rem;color:#1B2336">
          <option value="">Seleccionar…</option>${opts}
        </select>
      </div>
      <div style="margin-bottom:12px">
        <label style="display:block;font-size:.78rem;font-weight:700;color:#374151;margin-bottom:4px">Asunto</label>
        <input id="msg-c-asunto" type="text" maxlength="150" value="${CRM.escapeHtml(preAsunto||'')}" style="width:100%;padding:8px 12px;border:1.5px solid #e5e7eb;border-radius:9px;font-family:inherit;font-size:.88rem;color:#1B2336">
      </div>
      <div style="margin-bottom:14px">
        <label style="display:block;font-size:.78rem;font-weight:700;color:#374151;margin-bottom:4px">Mensaje</label>
        <textarea id="msg-c-cuerpo" rows="5" style="width:100%;padding:8px 12px;border:1.5px solid #e5e7eb;border-radius:9px;font-family:inherit;font-size:.88rem;color:#1B2336;resize:vertical"></textarea>
      </div>
      <div style="display:flex;gap:8px;justify-content:flex-end">
        <button onclick="document.getElementById('msg-compose-panel').innerHTML=''" style="font-size:.82rem;font-weight:700;background:none;border:1.5px solid #e5e7eb;border-radius:9px;padding:7px 16px;cursor:pointer;font-family:inherit;color:#6b7280">Cancelar</button>
        <button onclick="msgEnviarCompose()" style="font-size:.82rem;font-weight:700;background:#3b82f6;color:#fff;border:none;border-radius:9px;padding:7px 16px;cursor:pointer;font-family:inherit">Enviar</button>
      </div>
    </div>`;
}

async function msgEnviarCompose() {
  const receptor_id = parseInt(document.getElementById('msg-c-dest').value);
  const asunto = document.getElementById('msg-c-asunto').value.trim();
  const cuerpo = document.getElementById('msg-c-cuerpo').value.trim();
  if (!receptor_id || !cuerpo) { CRM.toast('Faltan datos obligatorios', 'warning'); return; }
  const res = await CRM.api('mensajes_enviar', { receptor_id, asunto, cuerpo });
  if (res.ok) {
    CRM.toast('Mensaje enviado', 'success');
    document.getElementById('msg-compose-panel').innerHTML = '';
    msgCargados = false;
    msgCargarLista(msgTabActual);
  } else {
    CRM.toast('Error: ' + (res.error||''), 'error');
  }
}
</script>
