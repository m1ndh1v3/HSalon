<?php
// ==========================
// /admin/calendar.php — weekly view with unified view toggle bar
// ==========================
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}
include_once __DIR__ . '/../includes/header.php';

$offset = intval($_GET['week'] ?? 0);
$weekStart = strtotime("last sunday +$offset week", strtotime('tomorrow'));
$weekDays = [];
for ($i=0;$i<7;$i++) $weekDays[] = date('Y-m-d', strtotime("+$i day", $weekStart));

try {
    $stmt = $pdo->prepare("SELECT b.*, s.name AS service_name 
                           FROM bookings b 
                           LEFT JOIN services s ON b.service_id=s.id
                           WHERE b.date BETWEEN ? AND ?
                           ORDER BY b.date, b.time");
    $stmt->execute([$weekDays[0], end($weekDays)]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $bookings = [];
}
$grouped = [];
foreach ($bookings as $b) $grouped[$b['date']][] = $b;

$arabicDays = ['Sunday'=>'الأحد','Monday'=>'الاثنين','Tuesday'=>'الثلاثاء','Wednesday'=>'الأربعاء','Thursday'=>'الخميس','Friday'=>'الجمعة','Saturday'=>'السبت'];
$today = date('Y-m-d');
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex gap-2">
      <a href="bookings.php" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="عرض الجدول"><i class="bi bi-clipboard-data"></i></a>
      <a href="calendar.php" class="btn btn-primary" data-bs-toggle="tooltip" title="العرض الأسبوعي"><i class="bi bi-calendar-week"></i></a>
      <a href="calendar_month.php" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="العرض الشهري"><i class="bi bi-calendar-month"></i></a>
    </div>
    <div class="text-muted">من <?php echo date('d/m', strtotime($weekDays[0])); ?> إلى <?php echo date('d/m', strtotime(end($weekDays))); ?></div>
    <div class="d-flex gap-2">
      <a href="?week=<?php echo $offset-1; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-chevron-right"></i></a>
      <a href="?week=<?php echo $offset+1; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-chevron-left"></i></a>
    </div>
  </div>

  <div class="calendar-grid d-flex flex-wrap border rounded overflow-hidden shadow-sm">
    <?php foreach ($weekDays as $day):
      $dLabel = $arabicDays[date('l', strtotime($day))];
      $dayBookings = $grouped[$day] ?? [];
      $isToday = $day === $today;
    ?>
    <div class="calendar-day flex-fill border-end <?php echo $isToday?'bg-opacity-10 bg-primary':''; ?>" style="min-width:14.28%;">
      <div class="bg-light fw-bold text-center py-2 position-relative">
        <?php echo $dLabel; ?><br><small class="text-muted"><?php echo date('d/m', strtotime($day)); ?></small>
        <?php if($isToday): ?><span class="position-absolute top-0 start-0 w-100 h-100" style="background:rgba(13,110,253,0.05);"></span><?php endif; ?>
      </div>
      <div class="p-2" style="min-height:400px;">
        <?php if (empty($dayBookings)): ?>
          <div class="text-center text-muted small mt-5">-</div>
        <?php else: ?>
          <?php foreach ($dayBookings as $bk):
            $color = $bk['status']=='approved' ? 'success' : ($bk['status']=='pending' ? 'warning' : 'secondary');
          ?>
          <div class="card mb-2 shadow-sm border-<?php echo $color; ?> border-2 booking-card" data-id="<?php echo $bk['id']; ?>" style="cursor:pointer;">
            <div class="card-body py-2 px-2 text-center">
              <div class="fw-semibold text-<?php echo $color; ?>"><?php echo substr($bk['time'],0,5); ?></div>
              <div class="small"><?php echo clean($bk['name']); ?></div>
              <div class="text-muted small"><?php echo clean($bk['service_name']); ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title">تفاصيل الموعد</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-end" id="bookingDetails">جارِ التحميل...</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إغلاق</button>
      </div>
    </div>
  </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
