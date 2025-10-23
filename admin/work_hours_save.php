<?php
// ==========================
// /admin/work_hours_save.php
// ==========================
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
  echo json_encode(['success'=>false,'msg'=>'unauthorized']);
  exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !is_array($input)) {
  echo json_encode(['success'=>false,'msg'=>'invalid input']);
  exit;
}

try {
  $pdo->beginTransaction();
  $stmt = $pdo->prepare("UPDATE work_hours 
    SET is_open=?, open_time=?, close_time=?, break_start=?, break_end=?, updated_at=NOW() 
    WHERE id=?");
  foreach ($input as $row) {
    $stmt->execute([
      $row['is_open'],
      $row['open_time'],
      $row['close_time'],
      $row['break_start'],
      $row['break_end'],
      $row['id']
    ]);
  }
  $pdo->commit();
  echo json_encode(['success'=>true]);
} catch (Exception $e) {
  $pdo->rollBack();
  echo json_encode(['success'=>false,'msg'=>$e->getMessage()]);
}
