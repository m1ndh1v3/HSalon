<?php
// ==========================
// /get_available_times.php
// ==========================
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

$date = $_GET['date'] ?? '';
if (!$date) {
    echo json_encode(['success'=>false]);
    exit;
}

try {
    $weekday = date('l', strtotime($date));
    $stmt = $pdo->prepare("SELECT * FROM work_hours WHERE day_name=? LIMIT 1");
    $stmt->execute([$weekday]);
    $day = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$day || !$day['is_open']) {
        echo json_encode(['success'=>false]);
        exit;
    }

    $start = strtotime($day['open_time']);
    $end = strtotime($day['close_time']);
    $breakStart = $day['break_start'] ? strtotime($day['break_start']) : null;
    $breakEnd   = $day['break_end'] ? strtotime($day['break_end']) : null;
    $step = 30 * 60;

    $available = [];
    for ($t = $start; $t < $end; $t += $step) {
        $timeStr = date('H:i', $t);
        if ($breakStart && $breakEnd && $t >= $breakStart && $t < $breakEnd) continue;
        $check = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE date=? AND time=? AND status IN ('pending','approved')");
        $check->execute([$date, $timeStr]);
        if ($check->fetchColumn() == 0) $available[] = $timeStr;
    }

    echo json_encode(['success'=>true, 'times'=>$available]);
} catch (Exception $e) {
    echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
}
