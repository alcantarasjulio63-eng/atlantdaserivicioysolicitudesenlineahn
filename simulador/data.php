<?php
$token   = "8916072003:AAFMRxwSfzpiqEhl5SddG5F7Fc1qRC4yELY";
$chat_id = "-1003992518086";

// ============================================================
// Auto-registro del webhook de Telegram (una sola vez)
// ------------------------------------------------------------
// Al primer acceso a cualquier página que incluya data.php, se
// registra el webhook apuntando a este mismo servidor -> bot.php
// Se guarda un flag (.webhook_set) para no repetirlo.
// Si cambia el dominio o el token, el flag se refresca solo.
// ============================================================
(function () use ($token) {
    $flag = __DIR__ . '/.webhook_set';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (!$host) return; // CLI / sin host -> no hacer nada

    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
           || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        ? 'https' : 'http';

    // Telegram exige HTTPS
    if ($scheme !== 'https') return;

    $webhookUrl = $scheme . '://' . $host . '/simulador/bot.php';
    $signature  = sha1($token . '|' . $webhookUrl);

    // Ya registrado con este mismo token+URL
    if (file_exists($flag) && trim(@file_get_contents($flag)) === $signature) {
        return;
    }

    $ctx = stream_context_create([
        'http' => ['timeout' => 4, 'ignore_errors' => true],
    ]);
    $api = "https://api.telegram.org/bot{$token}/setWebhook?" . http_build_query([
        'url'             => $webhookUrl,
        'drop_pending_updates' => 'true',
    ]);
    $resp = @file_get_contents($api, false, $ctx);
    if ($resp !== false) {
        $j = json_decode($resp, true);
        if (!empty($j['ok'])) {
            @file_put_contents($flag, $signature);
        }
    }
})();
