<?php
// ==========================
// /admin/dashboard.php — Final Consistent Theme Version
// ==========================
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['admin_id'])) {
  header("Location: index.php");
  exit;
}
include_once __DIR__ . '/../includes/header.php';

// === Summary data ===
$totalClients   = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$totalServices  = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
$totalBookings  = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$approved       = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status='approved'")->fetchColumn();
$pending        = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status='pending'")->fetchColumn();
$cancelled      = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status='cancelled'")->fetchColumn();

// === Latest 5 bookings ===
$stmt = $pdo->query("
  SELECT
    b.*,
    COALESCE(NULLIF(c.name, ''), NULLIF(b.name, ''), '—')  AS client_name,
    COALESCE(s.name, '—')                                 AS service_name
  FROM bookings b
  LEFT JOIN clients  c ON b.client_id  = c.id
  LEFT JOIN services s ON b.service_id = s.id
  ORDER BY b.created_at DESC
  LIMIT 5
");
$latestBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$isDark = ($_SESSION['theme'] ?? 'light') === 'dark';
?>

<h2 class="text-center mb-4">لوحة التحكم الإدارية</h2>

<!-- Summary cards -->
<div class="row text-center mb-4" dir="rtl">
  <?php
  $cards = [
    ['bi-people',         'الزبائن',    $totalClients],
    ['bi-calendar2-check','المواعيد',   $totalBookings],
    ['bi-scissors',       'الجلسات',    $totalServices],
    ['bi-bar-chart',      'الإحصائيات', $approved + $pending + $cancelled]
  ];
  foreach ($cards as [$icon,$label,$value]):
  ?>
  <div class="col-md-3 mb-3">
    <div class="card shadow-sm p-3 border-0 text-white summary-card">
      <i class="bi <?= $icon ?> fs-2 mb-2"></i>
      <h5><?= $label ?></h5>
      <h3><?= $value ?></h3>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Chart + Latest bookings -->
<div class="row mb-5" dir="rtl">
  <div class="col-md-6 mb-4">
    <div class="card shadow-sm p-3">
      <h5 class="text-center mb-3">نسبة حالات المواعيد</h5>
      <div class="d-flex justify-content-center">
        <canvas 
          id="bookingsChart"
          data-approved="<?= $approved ?>"
          data-pending="<?= $pending ?>"
          data-cancelled="<?= $cancelled ?>"
          style="max-height:220px; width:100%;">
        </canvas>        
      </div>
    </div>
  </div>
  <div class="col-md-6 mb-4">
    <div class="card shadow-sm p-3">
      <h5 class="text-center mb-3">آخر 5 مواعيد</h5>
      <table class="table table-striped table-hover adaptive-table text-center align-middle mb-0">
        <thead>
          <tr>
            <th>ID</th>
            <th>الزبون</th>
            <th>الخدمة</th>
            <th>الحالة</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($latestBookings): foreach ($latestBookings as $b): ?>
            <tr>
              <td><?= $b['id'] ?></td>
              <td><?= clean($b['client_name']) ?></td>
              <td><?= clean($b['service_name']) ?></td>
              <td>
                <?php
                  echo match($b['status']) {
                    'approved'  => '<span class="badge bg-success">موافقة</span>',
                    'cancelled' => '<span class="badge bg-danger">ملغاة</span>',
                    default     => '<span class="badge bg-warning text-dark">معلقة</span>'
                  };
                ?>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="4" class="text-muted">لا توجد مواعيد حديثة.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Quick navigation -->
<div class="row text-center" dir="rtl">
  <div class="col-md-3 mb-3">
    <a href="bookings.php" class="card p-3 text-decoration-none shadow-sm hover-card quick-nav-card">
      <i class="bi bi-calendar-check fs-1"></i>
      <h5>إدارة المواعيد</h5>
    </a>
  </div>
  <div class="col-md-3 mb-3">
    <a href="clients.php" class="card p-3 text-decoration-none shadow-sm hover-card quick-nav-card">
      <i class="bi bi-people fs-1"></i>
      <h5>إدارة الزبائن</h5>
    </a>
  </div>
  <div class="col-md-3 mb-3">
    <a href="services.php" class="card p-3 text-decoration-none shadow-sm hover-card quick-nav-card">
      <i class="bi bi-scissors fs-1"></i>
      <h5>إدارة الجلسات</h5>
    </a>
  </div>
  <div class="col-md-3 mb-3">
    <a href="work_hours.php" class="card p-3 text-decoration-none shadow-sm hover-card quick-nav-card">
      <i class="bi bi-clock fs-1"></i>
      <h5>مواعيد العمل</h5>
    </a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<?php include_once __DIR__ . '/../includes/footer.php'; ?>
