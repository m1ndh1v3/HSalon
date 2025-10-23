<?php
// ==========================
// /admin/calendar_day_details.php
// ==========================
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['admin_id'])) exit;

$date = clean($_GET['date'] ?? '');
if (!$date) exit;
$stmt = $pdo->prepare("SELECT b.*, s.name AS service_name FROM bookings b LEFT JOIN services s ON b.service_id=s.id WHERE b.date=? ORDER BY b.time");
$stmt->execute([$date]);
$list = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$list) { echo '<div class="text-center text-muted">لا توجد مواعيد في هذا اليوم</div>'; exit; }
?>
<div class="table-responsive">
<table class="table table-sm text-center align-middle">
  <thead class="table-light"><tr><th>الوقت</th><th>الاسم</th><th>الخدمة</th><th>الحالة</th><th>إجراءات</th></tr></thead>
  <tbody>
    <?php foreach ($list as $b):
      $color = $b['status']=='approved'?'success':($b['status']=='pending'?'warning':'secondary');
    ?>
    <tr>
      <td><?php echo $b['time']; ?></td>
      <td><?php echo clean($b['name']); ?></td>
      <td><?php echo clean($b['service_name']); ?></td>
      <td><span class="badge bg-<?php echo $color; ?>"><?php echo $b['status']; ?></span></td>
      <td>
        <?php if ($b['status']!='approved'): ?>
          <a href="bookings.php?approve=<?php echo $b['id']; ?>" class="btn btn-success btn-sm"><i class="bi bi-check"></i></a>
        <?php endif; ?>
        <?php if ($b['status']!='cancelled'): ?>
          <a href="bookings.php?cancel=<?php echo $b['id']; ?>" class="btn btn-danger btn-sm"><i class="bi bi-x"></i></a>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
