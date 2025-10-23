<?php
// ==========================
// /admin/calendar_booking_details.php
// ==========================
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['admin_id'])) exit;

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT b.*, s.name AS service_name FROM bookings b LEFT JOIN services s ON b.service_id=s.id WHERE b.id=?");
$stmt->execute([$id]);
$b = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$b) { echo '<div class="text-danger">لم يتم العثور على الموعد.</div>'; exit; }
?>
<div>
  <div><strong>الاسم:</strong> <?php echo clean($b['name']); ?></div>
  <div><strong>الخدمة:</strong> <?php echo clean($b['service_name']); ?></div>
  <div><strong>التاريخ:</strong> <?php echo $b['date']; ?></div>
  <div><strong>الوقت:</strong> <?php echo $b['time']; ?></div>
  <div><strong>الهاتف:</strong> <?php echo $b['phone']; ?></div>
  <div><strong>البريد:</strong> <?php echo $b['email']; ?></div>
  <div><strong>الحالة:</strong> <span class="badge bg-<?php echo $b['status']=='approved'?'success':($b['status']=='pending'?'warning':'secondary'); ?>"><?php echo $b['status']; ?></span></div>
  <div class="mt-3 d-flex gap-2 justify-content-end">
    <?php if ($b['status']!='approved'): ?>
    <a href="bookings.php?approve=<?php echo $b['id']; ?>" class="btn btn-success btn-sm"><i class="bi bi-check"></i> موافقة</a>
    <?php endif; ?>
    <?php if ($b['status']!='cancelled'): ?>
    <a href="bookings.php?cancel=<?php echo $b['id']; ?>" class="btn btn-danger btn-sm"><i class="bi bi-x"></i> إلغاء</a>
    <?php endif; ?>
  </div>
</div>
