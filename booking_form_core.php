<?php
// ==========================
// /booking_form_core.php — Booking Form Partial (used in booking.php)
// ==========================

if (!function_exists('t')) {
  function t($key, $default = '') {
    global $lang;
    return $lang[$key] ?? $default;
  }
}


try {
  $stmt = $pdo->query("SELECT * FROM services ORDER BY id ASC");
  $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $services = []; }

$cid    = $_SESSION['client_id']    ?? null;
$cname  = $_SESSION['client_name']  ?? '';
$cphone = $_SESSION['client_phone'] ?? '';
$cemail = $_SESSION['client_email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name    = clean($_POST['name'] ?? $cname);
  $phone = normalize_phone($_POST['phone'] ?? $cphone);
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
        echo '<div class="alert alert-danger text-center">'.t('booking_unavailable','الموعد غير متاح').'</div>';
      } else {
        $stmt = $pdo->prepare("INSERT INTO bookings (client_id,name,phone,email,service_id,date,time,notify_method,status,created_at)
                               VALUES (?,?,?,?,?,?,?,?,?,NOW())");
        $stmt->execute([$cid,$name,$phone,$email,$service,$date,$time,$notify,$status]);
        $bookId = $pdo->lastInsertId();

        $serviceName = $pdo->prepare("SELECT name FROM services WHERE id=?");
        $serviceName->execute([$service]);
        $serviceName = $serviceName->fetchColumn() ?: 'غير محددة';

        if ($langKey === 'ar') {
            $msg = "مرحباً، أرغب بتأكيد موعد في الصالون:%0A%0A"
                . "الاسم: $name%0A"
                . "رقم الهاتف: $normalizedPhone%0A"
                . "الخدمة المطلوبة: $serviceName%0A"
                . "التاريخ: $date%0A"
                . "الوقت: $time%0A%0A"
                . "يرجى تأكيد توفر الموعد، وشكراً 🌸";
        } else {
            $msg = "Hello, I'd like to confirm my appointment at the salon:%0A%0A"
                . "Name: $name%0A"
                . "Phone: $normalizedPhone%0A"
                . "Service: $serviceName%0A"
                . "Date: $date%0A"
                . "Time: $time%0A%0A"
                . "Please confirm the availability. Thank you 🌸";
        }

        echo '<div class="alert alert-success text-center">'.nl2br($msg).'</div>';

        if ($notify === 'whatsapp') {
          $wa = send_whatsapp_message($phone, $msg);
          echo '<div class="alert alert-success text-center">'.t('booking_success_whatsapp','تم إرسال التفاصيل عبر واتساب').'<br><a href="'.$wa.'" target="_blank">WhatsApp</a></div>';
        } else {
          send_email($email, "Appointment Confirmation - ".SITE_NAME, nl2br($msg));
          echo '<div class="alert alert-success text-center">'.t('booking_success_sent','تم إرسال التفاصيل إلى بريدك الإلكتروني').'</div>';
        }
      }
    } catch (Exception $e) {
      echo '<div class="alert alert-danger text-center">'.t('booking_error','حدث خطأ أثناء الحجز').'</div>';
    }
  } else {
    echo '<div class="alert alert-warning text-center">'.t('booking_required_fields','الرجاء ملء جميع الحقول المطلوبة').'</div>';
  }
}
?>

<h2 class="text-center mb-4"><?php echo t('booking_page_title','احجز موعدك الآن'); ?></h2>

<form method="POST" class="col-md-8 mx-auto card p-4 shadow-sm fade-in" dir="<?php echo $langKey == 'ar' ? 'rtl' : 'ltr'; ?>">
  <?php if (!empty($cid)): ?>
    <input type="hidden" name="name"  value="<?php echo clean($cname); ?>">
    <input type="hidden" name="phone" value="<?php echo clean($cphone); ?>">
    <input type="hidden" name="email" value="<?php echo clean($cemail); ?>">
    <p class="text-center mb-3"><?php echo t('booking_for','يتم الحجز باسم'); ?> <strong><?php echo clean($cname); ?></strong></p>
  <?php else: ?>
    <div class="mb-3">
      <label class="form-label"><?php echo t('booking_full_name','الاسم الكامل'); ?></label>
      <input type="text" name="name" class="form-control text-end" required>
    </div>
    <div class="mb-3">
      <label class="form-label"><?php echo t('booking_phone','رقم الهاتف'); ?></label>
      <input type="text" name="phone" class="form-control text-end" required>
    </div>
    <div class="mb-3">
      <label class="form-label"><?php echo t('booking_email_optional','البريد الإلكتروني (اختياري)'); ?></label>
      <input type="email" name="email" class="form-control text-end">
    </div>
  <?php endif; ?>

  <div class="mb-3">
    <label class="form-label"><?php echo t('booking_select_service','اختر الخدمة'); ?></label>
    <select name="service" class="form-select text-end" required>
      <option value="" disabled selected>-- <?php echo t('choose_service','اختر الخدمة'); ?> --</option>
      <?php foreach ($services as $s): ?>
        <option value="<?php echo $s['id']; ?>"><?php echo clean($s['name']); ?> (<?php echo $s['price']; ?>₪)</option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="row mb-3">
    <div class="col-md-6">
      <label class="form-label"><?php echo t('booking_select_date','اختر التاريخ'); ?></label>
      <input type="date" id="datePicker" name="date" class="form-control text-end" required min="<?php echo date('Y-m-d'); ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label"><?php echo t('booking_select_time','اختر الوقت'); ?></label>
      <select id="timeSelect" name="time" class="form-select text-end" required disabled>
        <option value=""><?php echo t('booking_select_time_hint','اختر التاريخ أولاً'); ?></option>
      </select>
    </div>
  </div>

  <div class="mb-3">
    <label class="form-label"><?php echo t('booking_notify_method','طريقة التواصل'); ?></label>
    <select name="notify" class="form-select text-end">
      <option value="whatsapp"><?php echo t('booking_notify_whatsapp','واتساب'); ?></option>
      <option value="email"><?php echo t('booking_notify_email','البريد الإلكتروني'); ?></option>
    </select>
  </div>

  <button type="submit" class="btn btn-primary w-100"><?php echo t('booking_button_confirm','تأكيد الموعد'); ?></button>
</form>
