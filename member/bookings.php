<?php
// ==========================
// /member/bookings.php
// ==========================

require_once __DIR__ . '/../config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (empty($_SESSION['client_id'])) {
    header("Location: ../login.php");
    exit;
}

// Fallback clean() if not globally defined
if (!function_exists('clean')) {
    function clean($v) { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
}

include_once __DIR__ . '/../includes/header.php';

$cid = (int)$_SESSION['client_id'];

$sql = "SELECT b.*, s.name AS service_name, s.duration, s.price
        FROM bookings b
        LEFT JOIN services s ON b.service_id = s.id
        WHERE b.client_id = ?
        ORDER BY b.date DESC, b.time DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$cid]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lang fallbacks
$txtMyBookings   = $lang['my_bookings']        ?? 'My Bookings';
$txtSvcName      = $lang['service_name']       ?? 'Service';
$txtSvcDuration  = $lang['service_duration']   ?? 'Duration';
$txtSvcPrice     = $lang['service_price']      ?? 'Price';
$txtChooseTime   = $lang['choose_time']        ?? 'Date & Time';
$txtStatus       = $lang['status']             ?? 'Status';
$txtApproved     = $lang['status_approved']    ?? 'Approved';
$txtCancelled    = $lang['status_cancelled']   ?? 'Cancelled';
$txtPending      = $lang['status_pending']     ?? 'Pending';
$txtNone         = $lang['no_bookings_yet']    ?? 'No bookings yet.';
?>

<h2 class="text-center mb-4"><?php echo clean($txtMyBookings); ?></h2>

<div class="table-responsive">
  <table class="table table-bordered table-striped text-center align-middle">
    <thead class="table-light">
      <tr>
        <th><?php echo clean($txtSvcName); ?></th>
        <th><?php echo clean($txtSvcDuration); ?></th>
        <th><?php echo clean($txtSvcPrice); ?></th>
        <th><?php echo clean($txtChooseTime); ?></th>
        <th><?php echo clean($txtStatus); ?></th>
      </tr>
    </thead>
    <tbody>
    <?php if (!empty($rows)): ?>
      <?php foreach ($rows as $r): ?>
        <?php
          $svcName  = $r['service_name'] !== null ? $r['service_name'] : '[deleted]';
          $duration = $r['duration'] !== null ? $r['duration'] : '-';
          $price    = $r['price'] !== null ? $r['price'] : '-';
          $dateStr  = trim(($r['date'] ?? '') . ' ' . ($r['time'] ?? ''));

          switch ($r['status']) {
            case 'approved':
              $statusLabel = '<span class="badge bg-success">'.clean($txtApproved).'</span>';
              break;
            case 'cancelled':
              $statusLabel = '<span class="badge bg-danger">'.clean($txtCancelled).'</span>';
              break;
            default:
              $statusLabel = '<span class="badge bg-warning text-dark">'.clean($txtPending).'</span>';
          }
        ?>
        <tr>
          <td><?php echo clean($svcName); ?></td>
          <td><?php echo clean($duration); ?></td>
          <td><?php echo clean($price); ?>₪</td>
          <td><?php echo clean($dateStr); ?></td>
          <td><?php echo $statusLabel; ?></td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr>
        <td colspan="5" class="text-muted"><?php echo clean($txtNone); ?></td>
      </tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
