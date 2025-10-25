<?php
// ==========================
// /admin/bookings_action.php — AJAX handler for approve/cancel
// ==========================
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';
$id     = intval($_GET['id'] ?? 0);

if (!$id || !in_array($action, ['approve', 'cancel'])) {
    echo json_encode(['success' => false, 'msg' => 'Invalid request']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id=?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo json_encode(['success' => false, 'msg' => 'Booking not found']);
        exit;
    }

    if ($action === 'approve') {
        $pdo->prepare("UPDATE bookings SET status='approved' WHERE id=?")->execute([$id]);
        add_notification('booking', "تمت الموافقة على الموعد رقم #$id");
        echo json_encode(['success' => true, 'status' => 'approved', 'msg' => 'تمت الموافقة بنجاح.']);
    } elseif ($action === 'cancel') {
        $pdo->prepare("UPDATE bookings SET status='cancelled' WHERE id=?")->execute([$id]);
        add_notification('booking', "تم إلغاء الموعد رقم #$id");
        echo json_encode(['success' => true, 'status' => 'cancelled', 'msg' => 'تم الإلغاء بنجاح.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
}
