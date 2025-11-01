<?php
// ==========================
// /includes/functions.php — unified helper utilities
// ==========================

function log_debug($message) {
    $file = __DIR__ . '/../logs/debug_log.txt';
    $timestamp = date('[Y-m-d H:i:s] ');
    file_put_contents($file, $timestamp . $message . "\n", FILE_APPEND);
}

// --- Core Notification ---
// function add_notification($type, $message) {
//     global $pdo;
//     try {
//         $stmt = $pdo->prepare("INSERT INTO notifications (type, message, created_at, is_read) VALUES (?, ?, NOW(), 0)");
//         $stmt->execute([$type, $message]);
//         log_debug("Notification added [$type]: $message");
//     } catch (Exception $e) {
//         log_debug("Failed to add notification: " . $e->getMessage());
//     }
// }

// --- WhatsApp & Email ---
function normalize_phone($phone) {
    $phone = preg_replace('/\D/', '', $phone); // remove any non-digit chars
    if (preg_match('/^0?5\d{7,8}$/', $phone)) {
        return '+972' . ltrim($phone, '0');
    }
    return $phone;
}

function send_whatsapp_message($phone, $message) {
    $encoded = urlencode($message);
    $link = "https://wa.me/$phone?text=$encoded";
    log_debug("Simulated WhatsApp to $phone: $message");
    return $link;
}

function send_email($to, $subject, $body) {
    $headers = "From: " . SITE_EMAIL . "\r\n" .
               "Content-Type: text/html; charset=UTF-8\r\n";
    $result = mail($to, $subject, $body, $headers);
    log_debug("Email send attempt to $to: subject='$subject' result=" . ($result ? 'OK' : 'FAIL'));
    return $result;
}

// --- Sanitization & Formatting ---
function safe_trim($v) {
    return trim((string)($v ?? ''));
}

function clean($v) {
    return htmlspecialchars(safe_trim($v), ENT_QUOTES, 'UTF-8');
}

function format_date($datetime, $with_time = false) {
    if (!$datetime) return '';
    $ts = strtotime($datetime);
    return $with_time ? date('d/m/Y H:i', $ts) : date('d/m/Y', $ts);
}
