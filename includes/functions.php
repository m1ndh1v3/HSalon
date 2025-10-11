<?php
// ==========================
// Global Helper Functions
// ==========================

function log_debug($message) {
    $file = __DIR__ . '/../logs/debug_log.txt';
    $timestamp = date('[Y-m-d H:i:s] ');
    file_put_contents($file, $timestamp . $message . "\n", FILE_APPEND);
}

function send_whatsapp_message($phone, $message) {
    $encoded = urlencode($message);
    $link = "https://wa.me/$phone?text=$encoded";
    log_debug("WhatsApp Message simulated to $phone: $message");
    return $link;
}

function send_email($to, $subject, $body) {
    $headers = "From: " . SITE_EMAIL . "\r\n" .
               "Content-Type: text/html; charset=UTF-8\r\n";
    $result = mail($to, $subject, $body, $headers);
    log_debug("Email send attempt to $to: subject='$subject' result=" . ($result ? 'OK' : 'FAIL'));
    return $result;
}

function safe_trim($v) {
    return trim((string)($v ?? ''));
}

function clean($value) {
    return htmlspecialchars(safe_trim($value), ENT_QUOTES, 'UTF-8');
}

function format_date($datetime, $with_time = false) {
    if (!$datetime) return '';
    $ts = strtotime($datetime);
    return $with_time ? date('d/m/Y H:i', $ts) : date('d/m/Y', $ts);
}
