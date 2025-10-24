<?php
// ==========================
// /member/bookings.php — Client Dashboard “My Bookings”
// ==========================
require_once __DIR__ . '/../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['client_id'])) {
  header("Location: ../login.php");
  exit;
}

include_once __DIR__ . '/../includes/header.php';

$cid = (int)$_SESSION['client_id'];
$langKey = $_SESSION['lang'] ?? 'ar';
$dir = $langKey == 'ar' ? 'rtl' : 'ltr';

if (isset($_GET['cancel'])) {
  $bid = intval($_GET['cancel']);
  $pdo->prepare("UPDATE bookings SET status='cancelled' WHERE id=? AND client_id=?")->execute([$bid, $cid]);
  echo '<div class="alert alert-info text-center">'.($lang['booking_cancelled'] ?? 'تم إلغاء الموعد.').'</div>';
}

$stmt = $pdo->prepare("
  SELECT b.*, s.name AS service_name, s.duration, s.price
  FROM bookings b
  LEFT JOIN services s ON b.service_id=s.id
  WHERE b.client_id=? ORDER BY b.date DESC, b.time DESC
");
$stmt->execute([$cid]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container py-4" dir="<?php echo $dir; ?>">
  <h2 class="text-center mb-4"><?php echo $lang['my_bookings'] ?? 'مواعيدي'; ?></h2>

  <div class="table-responsive fade-in">
    <table class="table table-striped table-hover adaptive-table text-center align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th><?php echo $lang['service_name'] ?? 'الخدمة'; ?></th>
          <th><?php echo $lang['service_price'] ?? 'السعر'; ?></th>
          <th><?php echo $lang['choose_time'] ?? 'التاريخ والوقت'; ?></th>
          <th><?php echo $lang['status'] ?? 'الحالة'; ?></th>
          <th><?php echo $lang['actions'] ?? 'إجراء'; ?></th>
        </tr>
      </thead>
      <tbody>
      <?php if ($rows): ?>
        <?php foreach ($rows as $r): 
          $badge = match($r['status']) {
            'approved'  => '<span class="badge bg-success">'.($lang['status_approved'] ?? 'موافقة').'</span>',
            'cancelled' => '<span class="badge bg-danger">'.($lang['status_cancelled'] ?? 'ملغاة').'</span>',
            default     => '<span class="badge bg-warning text-dark">'.($lang['status_pending'] ?? 'معلقة').'</span>'
          };
        ?>
        <tr>
          <td><?php echo clean($r['service_name'] ?? '-'); ?></td>
          <td><?php echo clean($r['price'] ?? '-'); ?>₪</td>
          <td><?php echo clean(($r['date'] ?? '').' '.($r['time'] ?? '')); ?></td>
          <td><?php echo $badge; ?></td>
          <td>
            <?php if ($r['status'] == 'pending'): ?>
              <a href="?cancel=<?php echo $r['id']; ?>" class="btn btn-outline-danger btn-sm"
                 onclick="return confirm('<?php echo $lang['confirm_cancel'] ?? 'هل أنت متأكد من الإلغاء؟'; ?>');">
                 <i class="bi bi-x-circle"></i> <?php echo $lang['cancel'] ?? 'إلغاء'; ?>
              </a>
            <?php else: ?>
              <span class="text-muted small">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="5" class="text-muted py-4"><?php echo $lang['no_bookings_yet'] ?? 'لا توجد مواعيد حتى الآن.'; ?></td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="text-center mt-4">
    <a href="../booking.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> <?php echo $lang['book_new'] ?? 'احجز موعد جديد'; ?></a>
  </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
