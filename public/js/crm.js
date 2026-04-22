/* ============================================================
   Matrix CRM — JavaScript Utilities
   ============================================================ */

/* --- Sidebar ------------------------------------------------ */
(function () {
  const sidebar = document.getElementById('crmSidebar');
  const main    = document.getElementById('crmMain');
  const toggle  = document.getElementById('crmToggle');
  const overlay = document.getElementById('crmOverlay');
  const KEY     = 'crm_sidebar';

  if (!sidebar) return;

  const isMobile = () => window.innerWidth <= 768;

  function applySidebar(collapsed) {
    if (isMobile()) {
      sidebar.classList.toggle('mobile-open', !collapsed);
      if (overlay) overlay.classList.toggle('visible', !collapsed);
    } else {
      sidebar.classList.toggle('collapsed', collapsed);
      if (main) main.classList.toggle('expanded', collapsed);
      localStorage.setItem(KEY, collapsed ? '1' : '0');
    }
  }

  // Restore state on load
  const saved = localStorage.getItem(KEY) === '1';
  if (!isMobile()) {
    sidebar.classList.toggle('collapsed', saved);
    if (main) main.classList.toggle('expanded', saved);
  }

  if (toggle) {
    toggle.addEventListener('click', () => {
      if (isMobile()) {
        applySidebar(sidebar.classList.contains('mobile-open'));
      } else {
        applySidebar(!sidebar.classList.contains('collapsed'));
      }
    });
  }

  if (overlay) {
    overlay.addEventListener('click', () => applySidebar(true));
  }
})();

/* --- Toast notifications ----------------------------------- */
const CRM = {
  toast(message, type = 'success', duration = 3500) {
    const icons = {
      success: '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>',
      error:   '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>',
      info:    '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>',
      warning: '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>'
    };
    const container = document.getElementById('crmToastContainer');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `crm-toast`;
    toast.innerHTML = `
      <div class="crm-toast-icon ${type}">${icons[type] || icons.info}</div>
      <div class="crm-toast-msg">${message}</div>
      <button class="crm-toast-close" onclick="this.closest('.crm-toast').remove()">×</button>
    `;
    container.appendChild(toast);
    setTimeout(() => {
      toast.classList.add('fade-out');
      toast.addEventListener('animationend', () => toast.remove());
    }, duration);
  },

  async api(action, data = {}, method = 'POST') {
    try {
      const opts = { method, headers: { 'Content-Type': 'application/json' } };
      if (method !== 'GET') opts.body = JSON.stringify(data);
      const apiBase = window.CRM_API_URL || `${window.CRM_BASE_URL}/index.php?url=crm-api`;
      const url = `${apiBase}&action=${action}`;
      const res = await fetch(url, opts);
      return await res.json();
    } catch (e) {
      console.error('CRM API error:', e);
      return { ok: false, error: e.message };
    }
  },

  confirm(msg) {
    return window.confirm(msg);
  },

  formatDate(str) {
    if (!str) return '—';
    const d = new Date(str);
    return d.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
  },

  timeAgo(str) {
    if (!str) return '';
    const d = new Date(str);
    const now = new Date();
    const diff = Math.floor((now - d) / 1000);
    if (diff < 60) return 'ahora mismo';
    if (diff < 3600) return `hace ${Math.floor(diff/60)} min`;
    if (diff < 86400) return `hace ${Math.floor(diff/3600)} h`;
    if (diff < 604800) return `hace ${Math.floor(diff/86400)} días`;
    return CRM.formatDate(str);
  },

  debounce(fn, delay = 350) {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); };
  },

  escapeHtml(str) {
    const el = document.createElement('div');
    el.textContent = str;
    return el.innerHTML;
  }
};

/* Make BASE_URL available from PHP (set in base layout) */
const BASE_URL = window.CRM_BASE_URL || '';

/* --- Search debounce helper -------------------------------- */
document.querySelectorAll('[data-crm-search]').forEach(input => {
  input.addEventListener('input', CRM.debounce(e => {
    const target = document.getElementById(input.dataset.crmSearch);
    if (!target) return;
    const q = e.target.value.toLowerCase();
    target.querySelectorAll('[data-search-row]').forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(q) ? '' : 'none';
    });
  }));
});

/* --- Modal helper ------------------------------------------ */
function openModal(id) {
  const modal = document.getElementById(id);
  if (modal) new bootstrap.Modal(modal).show();
}
function closeModal(id) {
  const modal = document.getElementById(id);
  if (modal) { const m = bootstrap.Modal.getInstance(modal); if (m) m.hide(); }
}

/* --- Confirm delete helper --------------------------------- */
document.addEventListener('click', e => {
  const btn = e.target.closest('[data-crm-confirm]');
  if (!btn) return;
  if (!CRM.confirm(btn.dataset.crmConfirm || '¿Confirmar acción?')) e.preventDefault();
});

/* --- Auto-resize textareas --------------------------------- */
document.querySelectorAll('textarea[data-autoresize]').forEach(ta => {
  ta.addEventListener('input', () => {
    ta.style.height = 'auto';
    ta.style.height = ta.scrollHeight + 'px';
  });
});
