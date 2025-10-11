<?php
// ==========================
// /admin/dashboard.php (Enhanced Admin Overview - Chart resized)
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
  SELECT b.*, c.name AS client_name, s.name AS service_name
  FROM bookings b
  LEFT JOIN clients c ON b.client_id=c.id
  LEFT JOIN services s ON b.service_id=s.id
  ORDER BY b.created_at DESC
  LIMIT 5
");
$latestBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="text-center mb-4">لوحة التحكم الإدارية</h2>

<!-- Summary cards -->
<div class="row text-center mb-4" dir="rtl">
  <div class="col-md-3 mb-3">
    <div class="card shadow-sm p-3 border-0 bg-primary text-white">
      <i class="bi bi-people fs-2 mb-2"></i>
      <h5>العملاء</h5>
      <h3><?= $totalClients ?></h3>
    </div>
  </div>
  <div class="col-md-3 mb-3">
    <div class="card shadow-sm p-3 border-0 bg-success text-white">
      <i class="bi bi-calendar2-check fs-2 mb-2"></i>
      <h5>الحجوزات</h5>
      <h3><?= $totalBookings ?></h3>
    </div>
  </div>
  <div class="col-md-3 mb-3">
    <div class="card shadow-sm p-3 border-0 bg-warning text-dark">
      <i class="bi bi-scissors fs-2 mb-2"></i>
      <h5>الخدمات</h5>
      <h3><?= $totalServices ?></h3>
    </div>
  </div>
  <div class="col-md-3 mb-3">
    <div class="card shadow-sm p-3 border-0 bg-danger text-white">
      <i class="bi bi-bar-chart fs-2 mb-2"></i>
      <h5>الإحصائيات</h5>
      <h3><?= $approved + $pending + $cancelled ?></h3>
    </div>
  </div>
</div>

<!-- Chart + Latest bookings -->
<div class="row mb-5" dir="rtl">
  <div class="col-md-6 mb-4">
    <div class="card shadow-sm p-3">
      <h5 class="text-center mb-3">نسبة حالات الحجوزات</h5>
      <div class="d-flex justify-content-center">
        <canvas id="bookingsChart" style="max-height:220px; width:100%;"></canvas>
      </div>
    </div>
  </div>
  <div class="col-md-6 mb-4">
    <div class="card shadow-sm p-3">
      <h5 class="text-center mb-3">آخر 5 حجوزات</h5>
      <table class="table table-sm table-striped text-center align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>العميل</th>
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
                    'approved' => '<span class="badge bg-success">موافقة</span>',
                    'cancelled' => '<span class="badge bg-danger">ملغاة</span>',
                    default => '<span class="badge bg-warning text-dark">معلقة</span>'
                  };
                ?>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="4" class="text-muted">لا توجد حجوزات حديثة.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Quick navigation -->
<div class="row text-center" dir="rtl">
  <div class="col-md-3 mb-3">
    <a href="bookings.php" class="card p-3 text-decoration-none text-dark shadow-sm hover-card">
      <i class="bi bi-calendar-check fs-1"></i>
      <h5>إدارة الحجوزات</h5>
    </a>
  </div>
  <div class="col-md-3 mb-3">
    <a href="clients.php" class="card p-3 text-decoration-none text-dark shadow-sm hover-card">
      <i class="bi bi-people fs-1"></i>
      <h5>إدارة العملاء</h5>
    </a>
  </div>
  <div class="col-md-3 mb-3">
    <a href="services.php" class="card p-3 text-decoration-none text-dark shadow-sm hover-card">
      <i class="bi bi-scissors fs-1"></i>
      <h5>إدارة الخدمات</h5>
    </a>
  </div>
  <div class="col-md-3 mb-3">
    <a href="schedule.php" class="card p-3 text-decoration-none text-dark shadow-sm hover-card">
      <i class="bi bi-clock fs-1"></i>
      <h5>مواعيد العمل</h5>
    </a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('bookingsChart');
new Chart(ctx, {
  type: 'pie',
  data: {
    labels: ['موافقة', 'معلقة', 'ملغاة'],
    datasets: [{
      data: [<?= $approved ?>, <?= $pending ?>, <?= $cancelled ?>],
      backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
    }]
  },
  options: {
    aspectRatio: 1.3,
    plugins: {
      legend: { position: 'bottom' }
    }
  }
});
</script>

<style>
.hover-card { transition: transform .2s, box-shadow .2s; }
.hover-card:hover { transform: translateY(-5px); box-shadow: 0 0 10px rgba(0,0,0,0.2); }
</style>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
