<?php
// ================================================================
// Registro / verificación manual del webhook de Telegram.
// Uso:
//   /simulador/set_webhook.php           -> registra el webhook
//   /simulador/set_webhook.php?info=1    -> muestra getWebhookInfo
//   /simulador/set_webhook.php?force=1   -> fuerza el re-registro
// ================================================================
header('Content-Type: text/plain; charset=UTF-8');

require __DIR__ . '/data.php'; // esto ya intenta auto-registrar

$host   = $_SERVER['HTTP_HOST'] ?? '';
$scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
       || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
    ? 'https' : 'http';
$webhookUrl = $scheme . '://' . $host . '/simulador/bot.php';

if (isset($_GET['info'])) {
    echo "getWebhookInfo:\n";
    echo file_get_contents("https://api.telegram.org/bot{$token}/getWebhookInfo");
    exit;
}

if (isset($_GET['force'])) {
    @unlink(__DIR__ . '/.webhook_set');
}

$resp = file_get_contents("https://api.telegram.org/bot{$token}/setWebhook?" . http_build_query([
    'url'                  => $webhookUrl,
    'drop_pending_updates' => 'true',
]));

echo "Webhook URL: {$webhookUrl}\n\n";
echo "Respuesta setWebhook:\n{$resp}\n";

$j = json_decode($resp, true);
if (!empty($j['ok'])) {
    @file_put_contents(__DIR__ . '/.webhook_set', sha1($token . '|' . $webhookUrl));
    echo "\nOK. Webhook registrado.\n";
} else {
    echo "\nFALLO. Revisa el token/URL. Recuerda que Telegram exige HTTPS.\n";
}
