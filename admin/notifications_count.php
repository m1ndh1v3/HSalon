<?php
// ==========================
// /admin/notifications_count.php
// ==========================
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $count = $pdo->query("SELECT COUNT(*) FROM notifications WHERE is_read=0")->fetchColumn();
    echo json_encode(['count' => (int)$count]);
} catch (Exception $e) {
    echo json_encode(['count' => 0]);
}
