<?php
if (defined('GATE_CHECK_DONE')) return;
define('GATE_CHECK_DONE', true);

$_lib = realpath(dirname(__DIR__)) . '/_lib.php';
if (!file_exists($_lib)) return;
require_once $_lib;

[$_gs] = gate_compute_score();
if ($_gs >= 8) {
    http_response_code(403);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html><head><title>403</title></head><body></body></html>';
    exit;
}
unset($_gs, $_lib);
