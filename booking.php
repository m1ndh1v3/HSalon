<?php
// ==========================
// /booking.php
// ==========================
require_once __DIR__ . '/config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once __DIR__ . '/includes/header.php';

try {
    $services = $pdo->query("SELECT * FROM services ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $services = [];
    log_debug("Error loading services for booking: " . $e->getMessage());
}

$cid    = $_SESSION['client_id']    ?? null;
$cname  = $_SESSION['client_name']  ?? '';
$cphone = $_SESSION['client_phone'] ?? '';
$cemail = $_SESSION['client_email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = clean($_POST['name'] ?? $cname);
    $phone   = clean($_POST['phone'] ?? $cphone);
    $email   = clean($_POST['email'] ?? $cemail);
    $service = intval($_POST['service'] ?? 0);
    $date    = clean($_POST['date'] ?? '');
    $time    = clean($_POST['time'] ?? '');
    $notify  = clean($_POST['notify'] ?? 'email');
    $status  = 'pending';

    if ($name && $phone && $service && $date && $time) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE date=? AND time=? AND status IN ('pending','approved')");
            $stmt->execute([$date, $time]);
            $exists = $stmt->fetchColumn();

            if ($exists) {
                echo '<div class="alert alert-danger text-center">الوقت المحدد غير متاح حالياً، يرجى اختيار موعد آخر.</div>';
            } else {
                $stmt = $pdo->prepare("INSERT INTO bookings (client_id, name, phone, email, service_id, date, time, notify_method, status, created_at)
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$cid, $name, $phone, $email, $service, $date, $time, $notify, $status]);

                $bookId = $pdo->lastInsertId();
                log_debug("New booking created: ID=$bookId for $name");

                $msg = "تم استلام طلب الحجز الخاص بك وهو الآن قيد المراجعة.\nالخدمة رقم: $service\nالتاريخ: $date $time";
                if ($notify === 'whatsapp') {
                    $wa = send_whatsapp_message($phone, $msg);
                    echo '<div class="alert alert-success text-center">تم إرسال طلب الحجز بنجاح!<br><a href="'.$wa.'" target="_blank">فتح واتساب</a></div>';
                } else {
                    send_email($email, "تأكيد الحجز - ".SITE_NAME, nl2br($msg));
                    echo '<div class="alert alert-success text-center">تم إرسال طلب الحجز بنجاح!<br>سيتم التواصل معك قريباً لتأكيد الموعد.</div>';
                }
            }
        } catch (Exception $e) {
            log_debug("Booking insert failed: ".$e->getMessage());
            echo '<div class="alert alert-danger text-center">حدث خطأ أثناء إنشاء الحجز، يرجى المحاولة لاحقاً.</div>';
        }
    } else {
        echo '<div class="alert alert-warning text-center">يرجى تعبئة جميع الحقول المطلوبة لإتمام الحجز.</div>';
    }
}
?>

<h2 class="text-center mb-4">احجزي موعدك الآن</h2>

<form method="POST" class="col-md-8 mx-auto card p-4 shadow-sm text-end" dir="rtl">
  <?php if (!empty($cid)): ?>
    <input type="hidden" name="name"  value="<?php echo clean($cname); ?>">
    <input type="hidden" name="phone" value="<?php echo clean($cphone); ?>">
    <input type="hidden" name="email" value="<?php echo clean($cemail); ?>">
    <p class="text-center mb-3">
      يتم الحجز باسم <strong><?php echo clean($cname); ?></strong>
      <?php if ($cemail): ?>(<?php echo clean($cemail); ?>)<?php endif; ?>
    </p>
  <?php else: ?>
    <div class="mb-3">
      <label class="form-label">الاسم الكامل</label>
      <input type="text" name="name" class="form-control text-end" required>
    </div>
    <div class="mb-3">
      <label class="form-label">رقم الهاتف</label>
      <input type="text" name="phone" class="form-control text-end" required>
    </div>
    <div class="mb-3">
      <label class="form-label">البريد الإلكتروني (اختياري)</label>
      <input type="email" name="email" class="form-control text-end">
    </div>
  <?php endif; ?>

  <div class="mb-3">
    <label class="form-label">اختاري الخدمة</label>
    <select name="service" class="form-select text-end" required>
      <option value="" selected disabled hidden>-- اختاري --</option>
      <?php foreach ($services as $srv): ?>
        <option value="<?php echo $srv['id']; ?>">
          <?php echo clean($srv['name']); ?> (<?php echo $srv['price']; ?>₪)
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="row mb-3">
    <div class="col-md-6">
      <label class="form-label">التاريخ</label>
      <input type="date" name="date" class="form-control text-end" required min="<?php echo date('Y-m-d'); ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">الوقت</label>
      <input type="time" name="time" class="form-control text-end" required>
    </div>
  </div>

  <div class="mb-3">
    <label class="form-label">طريقة الإشعار</label>
    <select name="notify" class="form-select text-end">
      <option value="email">بريد إلكتروني</option>
      <option value="whatsapp">واتساب</option>
    </select>
  </div>

  <button type="submit" class="btn btn-primary w-100">تأكيد الحجز</button>
</form>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
