<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oráculo — Asistente IA · MatrixCoders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Saira:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/footer.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/sidebar.css">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        :root {
            --mc-green: #6B8F71; --mc-green-d: #4a6b50;
            --mc-navy: #0f172a; --mc-dark: #1B2336;
            --mc-border: #e5e7eb; --mc-soft: #f8fafc; --mc-muted: #6b7280;
        }
        .chatbot-main { flex:1; min-width:0; padding:2rem 1.5rem; display:flex; flex-direction:column; }
        .cb-header { margin-bottom:1.5rem; }
        .cb-title { font-size:1.4rem; font-weight:800; color:var(--mc-dark); margin:0 0 4px; display:flex; align-items:center; gap:.6rem; font-family:'Saira',sans-serif; }
        .cb-badge { font-size:.65rem; font-weight:800; background:var(--mc-green); color:#fff; border-radius:99px; padding:2px 9px; }
        .cb-sub { font-size:.87rem; color:var(--mc-muted); margin:0; }
        .cb-wrap { flex:1; display:flex; flex-direction:column; max-width:780px; width:100%; background:#fff; border:1.5px solid var(--mc-border); border-radius:16px; overflow:hidden; }
        .cb-messages { flex:1; overflow-y:auto; padding:1.25rem; display:flex; flex-direction:column; gap:.85rem; min-height:380px; max-height:520px; scrollbar-width:thin; scrollbar-color:#e5e7eb transparent; }
        .cb-messages::-webkit-scrollbar { width:4px; }
        .cb-messages::-webkit-scrollbar-thumb { background:#e5e7eb; border-radius:99px; }
        .cb-msg { display:flex; gap:.65rem; align-items:flex-start; }
        .cb-msg.user { flex-direction:row-reverse; }
        .cb-avatar { width:32px; height:32px; border-radius:9px; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:.85rem; font-weight:800; }
        .cb-avatar.bot { background:#f0fdf4; color:var(--mc-green-d); border:1px solid #bbf7d0; }
        .cb-avatar.user { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
        .cb-bubble { max-width:82%; padding:.65rem .9rem; border-radius:12px; font-size:.88rem; line-height:1.6; }
        .cb-bubble.bot { background:var(--mc-soft); border:1px solid var(--mc-border); color:var(--mc-dark); border-radius:2px 12px 12px 12px; }
        .cb-bubble.user { background:var(--mc-green); color:#fff; border-radius:12px 2px 12px 12px; }
        .cb-bubble p { margin:0 0 .4rem; } .cb-bubble p:last-child { margin:0; }
        .cb-bubble ul,ol { margin:.4rem 0; padding-left:1.3rem; }
        .cb-bubble strong { font-weight:700; }
        .cb-thinking { color:var(--mc-muted); font-size:.82rem; font-style:italic; }
        .cb-input-area { border-top:1px solid var(--mc-border); padding:.85rem 1rem; display:flex; gap:.6rem; background:#fafafa; }
        .cb-input { flex:1; border:1.5px solid var(--mc-border); border-radius:10px; padding:.55rem .9rem; font-family:'Saira',sans-serif; font-size:.88rem; color:var(--mc-dark); resize:none; outline:none; transition:border-color .15s; min-height:42px; max-height:120px; overflow-y:auto; }
        .cb-input:focus { border-color:var(--mc-green); }
        .cb-send { background:var(--mc-green); border:none; color:#fff; width:42px; height:42px; border-radius:10px; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .15s; flex-shrink:0; }
        .cb-send:hover { background:var(--mc-green-d); }
        .cb-send:disabled { opacity:.4; cursor:default; }
        .cb-suggestions { display:flex; flex-wrap:wrap; gap:.5rem; padding:1rem; border-bottom:1px solid var(--mc-border); background:#fafafa; }
        .cb-suggest-btn { padding:.32rem .75rem; border:1.5px solid var(--mc-border); border-radius:99px; font-size:.78rem; font-weight:600; font-family:'Saira',sans-serif; background:#fff; color:var(--mc-muted); cursor:pointer; transition:all .15s; }
        .cb-suggest-btn:hover { border-color:var(--mc-green); color:var(--mc-green-d); background:#f0fdf4; }
        .cb-info { font-size:.75rem; color:var(--mc-muted); text-align:center; padding:.5rem 1rem; border-top:1px solid var(--mc-border); background:#fafafa; }
    </style>
</head>
<body>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main class="main-dashboard">
    <div class="mc-container">
        <div class="contenedor-dashboard-content">

            <?php require __DIR__ . '/../layout/sidebar.php'; ?>

            <div class="chatbot-main">

                <div class="cb-header">
                    <h1 class="cb-title">
                        🤖 Oráculo
                        <span class="cb-badge">IA</span>
                    </h1>
                    <p class="cb-sub">Asistente virtual de soporte y orientación. Pregúntame sobre la plataforma, cursos o dudas técnicas.</p>
                </div>

                <div class="cb-wrap">
                    <!-- Sugerencias rápidas -->
                    <div class="cb-suggestions">
                        <button class="cb-suggest-btn" onclick="sugerirPregunta(this)">¿Qué cursos me recomiendas?</button>
                        <button class="cb-suggest-btn" onclick="sugerirPregunta(this)">¿Cómo funciona el sistema de certificados?</button>
                        <button class="cb-suggest-btn" onclick="sugerirPregunta(this)">¿Qué pasa si suspendo el examen?</button>
                        <button class="cb-suggest-btn" onclick="sugerirPregunta(this)">¿Cómo descargo los recursos de una lección?</button>
                        <button class="cb-suggest-btn" onclick="sugerirPregunta(this)">¿Cómo consigo el certificado?</button>
                    </div>

                    <!-- Mensajes -->
                    <div class="cb-messages" id="cbMessages">
                        <div class="cb-msg">
                            <div class="cb-avatar bot">🤖</div>
                            <div class="cb-bubble bot">
                                ¡Hola! Soy <strong>Oráculo</strong>, el asistente virtual de MatrixCoders.<br>
                                Puedo ayudarte con dudas sobre la plataforma, orientación sobre qué cursos estudiar o cualquier problema técnico. ¿En qué puedo ayudarte?
                            </div>
                        </div>
                    </div>

                    <!-- Input -->
                    <div class="cb-input-area">
                        <textarea id="cbInput" class="cb-input" placeholder="Escribe tu pregunta aquí…" rows="1"
                                  onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();enviar()}"></textarea>
                        <button class="cb-send" id="cbSendBtn" onclick="enviar()" title="Enviar">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <line x1="22" y1="2" x2="11" y2="13"/>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                            </svg>
                        </button>
                    </div>
                    <div class="cb-info">Oráculo usa IA · Puede cometer errores · Requiere GEMINI_API_KEY configurada</div>
                </div>

            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../layout/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const BASE_URL = '<?= BASE_URL ?>';
const messagesEl = document.getElementById('cbMessages');
const inputEl    = document.getElementById('cbInput');
const sendBtn    = document.getElementById('cbSendBtn');

function addMsg(texto, tipo) {
    const wrap = document.createElement('div');
    wrap.className = 'cb-msg ' + tipo;
    const avatar = document.createElement('div');
    avatar.className = 'cb-avatar ' + tipo;
    avatar.textContent = tipo === 'bot' ? '🤖' : '👤';
    const bubble = document.createElement('div');
    bubble.className = 'cb-bubble ' + tipo;
    if (tipo === 'bot') bubble.innerHTML = marked.parse(texto);
    else bubble.textContent = texto;
    wrap.appendChild(avatar);
    wrap.appendChild(bubble);
    messagesEl.appendChild(wrap);
    messagesEl.scrollTop = messagesEl.scrollHeight;
    return wrap;
}

function addThinking() {
    const wrap = document.createElement('div');
    wrap.className = 'cb-msg';
    wrap.id = 'cb-thinking';
    wrap.innerHTML = '<div class="cb-avatar bot">🤖</div><div class="cb-bubble bot cb-thinking">Oráculo está pensando…</div>';
    messagesEl.appendChild(wrap);
    messagesEl.scrollTop = messagesEl.scrollHeight;
}

function sugerirPregunta(btn) {
    inputEl.value = btn.textContent;
    inputEl.focus();
}

async function enviar() {
    const pregunta = inputEl.value.trim();
    if (!pregunta || sendBtn.disabled) return;
    inputEl.value = '';
    sendBtn.disabled = true;
    addMsg(pregunta, 'user');
    addThinking();
    try {
        const fd = new FormData();
        fd.append('pregunta', pregunta);
        const res = await fetch(BASE_URL + '/index.php?url=chatbot', { method: 'POST', body: fd }).then(r => r.json());
        document.getElementById('cb-thinking')?.remove();
        if (res.ok) addMsg(res.respuesta, 'bot');
        else addMsg('Lo siento, no pude procesar tu pregunta: ' + (res.error || 'error desconocido'), 'bot');
    } catch(e) {
        document.getElementById('cb-thinking')?.remove();
        addMsg('Error de conexión. Comprueba tu red e inténtalo de nuevo.', 'bot');
    } finally {
        sendBtn.disabled = false;
        inputEl.focus();
    }
}

// Auto-resize textarea
inputEl.addEventListener('input', () => {
    inputEl.style.height = 'auto';
    inputEl.style.height = Math.min(inputEl.scrollHeight, 120) + 'px';
});
</script>
</body>
</html>
