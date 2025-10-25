<?php
// ==========================
// /member/bookings.php — language-aware unified modal design
// ==========================
require_once __DIR__ . '/../config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (empty($_SESSION['client_id'])) {
    header("Location: ../login.php");
    exit;
}

// --- load language ---
$langKey = $_SESSION['lang'] ?? 'ar';
$langFile = __DIR__ . "/../lang/{$langKey}.php";
if (file_exists($langFile)) include $langFile;

include_once __DIR__ . '/../includes/header.php';

$cid = (int)$_SESSION['client_id'];

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

$upcoming = isset($_GET['upcoming']) && $_GET['upcoming'] === '1';
$flash = '';

if (isset($_GET['cancel'])) {
    $bid = (int)$_GET['cancel'];
    try {
        $stmt = $pdo->prepare("SELECT id, client_id, status, CONCAT(date,' ',time) AS dt FROM bookings WHERE id=? LIMIT 1");
        $stmt->execute([$bid]);
        $bk = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($bk && (int)$bk['client_id'] === $cid && $bk['status'] === 'pending' && strtotime($bk['dt']) > time()) {
            $pdo->prepare("UPDATE bookings SET status='cancelled' WHERE id=?")->execute([$bid]);
            add_notification('booking', "العميل رقم $cid ألغى الموعد رقم #$bid");
            $flash = '<div class="alert alert-success text-center">'.h($lang['booking_cancel_success'] ?? 'Booking was cancelled.').'</div>';
        } else {
            $flash = '<div class="alert alert-danger text-center">'.h($lang['booking_cancel_fail'] ?? 'Unable to cancel this booking.').'</div>';
        }
    } catch (Exception $e) {
        log_debug("Member self-cancel failed: ".$e->getMessage());
        $flash = '<div class="alert alert-danger text-center">'.h($lang['booking_cancel_fail'] ?? 'Unable to cancel this booking.').'</div>';
    }
}

$sql = "SELECT b.*, s.name AS service_name FROM bookings b
        LEFT JOIN services s ON b.service_id=s.id
        WHERE b.client_id=?";
$params = [$cid];
if ($upcoming) $sql .= " AND CONCAT(b.date,' ',b.time) >= NOW()";
$sql .= " ORDER BY b.date DESC, b.time DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$rtl   = ($langKey === 'ar');
$dir   = $rtl ? 'rtl' : 'ltr';
$align = $rtl ? 'text-end' : 'text-start';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="m-0"><?php echo h($lang['my_bookings'] ?? 'My Bookings'); ?></h2>
  <div class="d-flex gap-2">
    <a href="<?php echo SITE_URL; ?>/booking.php" class="btn btn-primary">
      <i class="bi bi-calendar-plus"></i> <?php echo h($lang['book_now'] ?? 'Book Now'); ?>
    </a>
    <?php if ($upcoming): ?>
      <a href="?upcoming=0" class="btn btn-outline-secondary"><?php echo h($lang['all'] ?? 'All'); ?></a>
    <?php else: ?>
      <a href="?upcoming=1" class="btn btn-outline-secondary"><?php echo h($lang['upcoming_only'] ?? 'Upcoming only'); ?></a>
    <?php endif; ?>
  </div>
</div>

<?php echo $flash; ?>

<div class="table-responsive">
  <table class="table table-bordered table-striped text-center align-middle" dir="<?php echo $dir; ?>">
    <thead class="table-light">
      <tr>
        <th><?php echo h($lang['service_name'] ?? 'Service'); ?></th>
        <th><?php echo h($lang['choose_time'] ?? 'Date & Time'); ?></th>
        <th><?php echo h($lang['status'] ?? 'Status'); ?></th>
        <th><?php echo h($lang['actions'] ?? 'Actions'); ?></th>
      </tr>
    </thead>
    <tbody>
    <?php if (!empty($rows)): foreach ($rows as $r):
        $svcName  = $r['service_name'] ?? '[deleted]';
        $dateStr  = trim(($r['date'] ?? '') . ' ' . ($r['time'] ?? ''));
        $isFuture = strtotime($dateStr) > time();
        $bid = (int)$r['id'];
        $canCancel = ($r['status'] === 'pending' && $isFuture);

        $statusLabel = match($r['status']) {
          'approved'  => '<span class="badge bg-success">'.h($lang['status_approved'] ?? 'Approved').'</span>',
          'cancelled' => '<span class="badge bg-danger">'.h($lang['status_cancelled'] ?? 'Cancelled').'</span>',
          default     => '<span class="badge bg-warning text-dark">'.h($lang['status_pending'] ?? 'Pending').'</span>'
        };
    ?>
      <tr>
        <td class="<?php echo $align; ?>"><?php echo h($svcName); ?></td>
        <td><?php echo h($dateStr); ?></td>
        <td><?php echo $statusLabel; ?></td>
        <td>
          <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#bkView<?php echo $bid; ?>">
            <i class="bi bi-eye"></i> <?php echo h($lang['view'] ?? 'View'); ?>
          </button>
          <?php if ($canCancel): ?>
            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#bkCancel<?php echo $bid; ?>">
              <i class="bi bi-x-circle"></i> <?php echo h($lang['cancel'] ?? 'Cancel'); ?>
            </button>
          <?php else: ?>
            <button class="btn btn-sm btn-secondary" disabled><?php echo h($lang['cancel'] ?? 'Cancel'); ?></button>
          <?php endif; ?>
        </td>
      </tr>

      <!-- Details Modal -->
      <div class="modal fade" id="bkView<?php echo $bid; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
          <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white border-0 rounded-top-4">
              <h5 class="modal-title"><?php echo h($lang['booking_details'] ?? 'Booking Details'); ?></h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body <?php echo $align; ?> py-4">
              <p><strong><?php echo h($lang['service_name'] ?? 'Service'); ?>:</strong> <?php echo h($svcName); ?></p>
              <p><strong><?php echo h($lang['choose_time'] ?? 'Date & Time'); ?>:</strong> <?php echo h($dateStr); ?></p>
              <p><strong><?php echo h($lang['status'] ?? 'Status'); ?>:</strong> <?php echo strip_tags($statusLabel); ?></p>
              <?php if (!empty($r['email'])): ?><p><strong>Email:</strong> <?php echo h($r['email']); ?></p><?php endif; ?>
              <?php if (!empty($r['phone'])): ?><p><strong>Phone:</strong> <?php echo h($r['phone']); ?></p><?php endif; ?>
            </div>
            <div class="modal-footer justify-content-center border-0 pb-4">
              <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal"><?php echo h($lang['close'] ?? 'Close'); ?></button>
            </div>
          </div>
        </div>
      </div>

      <!-- Cancel Modal -->
      <?php if ($canCancel): ?>
      <div class="modal fade" id="bkCancel<?php echo $bid; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
          <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-danger text-white border-0 rounded-top-4">
              <h5 class="modal-title"><?php echo h($lang['cancel'] ?? 'Cancel'); ?></h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
              <p class="mb-1"><?php echo h($lang['are_you_sure_cancel'] ?? 'Are you sure you want to cancel this booking?'); ?></p>
              <p class="text-muted small mb-0"><?php echo h($svcName.' — '.$dateStr); ?></p>
            </div>
            <div class="modal-footer justify-content-center border-0 pb-4">
              <a href="?cancel=<?php echo $bid; ?>" class="btn btn-danger px-4"><?php echo h($lang['cancel'] ?? 'Cancel'); ?></a>
              <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal"><?php echo h($lang['close'] ?? 'Close'); ?></button>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

    <?php endforeach; else: ?>
      <tr><td colspan="4" class="text-muted"><?php echo h($lang['no_bookings_yet'] ?? 'No bookings yet.'); ?></td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
