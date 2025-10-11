<?php
// ==========================
// /admin/notifications_action.php
// ==========================
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';
$id = intval($_GET['id'] ?? 0);

try {
    switch ($action) {
        case 'mark_all':
            $pdo->exec("UPDATE notifications SET is_read=1");
            echo json_encode(['success' => true, 'msg' => 'All marked as read']);
            break;

        case 'clear':
            $pdo->exec("TRUNCATE TABLE notifications");
            add_notification('system', 'تم مسح جميع الإشعارات.');
            echo json_encode(['success' => true, 'msg' => 'All cleared']);
            break;

        case 'read':
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE notifications SET is_read=1 WHERE id=?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true, 'msg' => 'Marked as read']);
            } else {
                echo json_encode(['success' => false, 'msg' => 'Invalid ID']);
            }
            break;

        case 'delete':
            if ($id > 0) {
                $stmt = $pdo->prepare("DELETE FROM notifications WHERE id=?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true, 'msg' => 'Deleted']);
            } else {
                echo json_encode(['success' => false, 'msg' => 'Invalid ID']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'msg' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
}
