<?php
// ==========================
// HSalon - Configuration File
// ==========================

// --- Error reporting ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Default timezone ---
date_default_timezone_set('Asia/Jerusalem');

// --- Session start ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Output buffering ---
if (!ob_get_level()) {
    ob_start();
}

// --- Site constants ---
define('SITE_NAME', 'HAYA Bridal Salon');
define('SITE_EMAIL', 'noreply@hsalon.local');
define('SITE_URL', 'http://localhost/hsalon');
define('LANG_DEFAULT', 'ar');
define('LANG_DIR', __DIR__ . '/lang/');
define('LOG_DIR', __DIR__ . '/logs/');

// --- Database configuration ---
$DB_HOST = 'localhost';
$DB_NAME = 'hsalon_db';
$DB_USER = 'root';
$DB_PASS = '';
$DB_CHARSET = 'utf8mb4';

// --- PDO connection ---
try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=$DB_CHARSET", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// ==========================
// Notification helper
// ==========================
function add_notification($type, $msg) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO notifications (type, message) VALUES (?, ?)");
        $stmt->execute([$type, $msg]);
    } catch (Exception $e) {
        error_log("Notification insert failed: " . $e->getMessage());
    }
}

// ==========================
// Admin settings helpers
// ==========================
function get_setting($name, $default = null) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT value FROM admin_settings WHERE name=?");
    $stmt->execute([$name]);
    $val = $stmt->fetchColumn();
    return ($val !== false) ? $val : $default;
}

function set_setting($name, $value) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO admin_settings (name, value)
                           VALUES (?, ?) ON DUPLICATE KEY UPDATE value=VALUES(value)");
    $stmt->execute([$name, $value]);
}

// --- Language selection ---
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang_code = $_SESSION['lang'] ?? LANG_DEFAULT;
$lang_file = LANG_DIR . $lang_code . '.php';
if (!file_exists($lang_file)) {
    $lang_file = LANG_DIR . LANG_DEFAULT . '.php';
}
include_once $lang_file;

// --- Theme preference ---
if (isset($_GET['theme'])) {
    $_SESSION['theme'] = $_GET['theme'];
}

// --- Load global helper functions ---
require_once __DIR__ . '/includes/functions.php';

// --- Safe output flush at script end (optional) ---
register_shutdown_function(function () {
    if (ob_get_length()) {
        @ob_end_flush();
    }
});
