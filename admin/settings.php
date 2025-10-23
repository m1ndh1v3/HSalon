<?php
// ==========================
// /admin/settings.php (with notification preferences)
// ==========================
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}
include_once __DIR__ . '/../includes/header.php';

// === Save settings on submit ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    set_setting('notify_booking', isset($_POST['notify_booking']) ? '1' : '0');
    set_setting('notify_system', isset($_POST['notify_system']) ? '1' : '0');
    set_setting('contact_email', trim($_POST['contact_email'] ?? ''));
    set_setting('contact_phone', trim($_POST['contact_phone'] ?? ''));
    set_setting('contact_whatsapp', trim($_POST['contact_whatsapp'] ?? ''));
    add_notification('system', 'تم تحديث إعدادات الإشعارات من قبل المسؤول.');
    echo '<div class="alert alert-success text-center mt-3">تم حفظ الإعدادات بنجاح ✅</div>';
}

// === Load current values ===
$notify_booking  = get_setting('notify_booking', '1');
$notify_system   = get_setting('notify_system', '1');
$contact_email   = get_setting('contact_email', '');
$contact_phone   = get_setting('contact_phone', '');
$contact_whatsapp = get_setting('contact_whatsapp', '');
?>

<h2 class="text-center mb-4">إعدادات الإشعارات</h2>

<form method="post" class="col-md-6 mx-auto card p-4 shadow-sm" dir="rtl">
  <h5 class="mb-3">خيارات الإشعارات</h5>
  <div class="form-check form-switch mb-3 text-end">
    <input class="form-check-input" type="checkbox" name="notify_booking" id="notify_booking"
           <?php if ($notify_booking === '1') echo 'checked'; ?>>
    <label class="form-check-label" for="notify_booking">تفعيل إشعارات المواعيد</label>
  </div>
  <div class="form-check form-switch mb-4 text-end">
    <input class="form-check-input" type="checkbox" name="notify_system" id="notify_system"
           <?php if ($notify_system === '1') echo 'checked'; ?>>
    <label class="form-check-label" for="notify_system">تفعيل إشعارات النظام</label>
  </div>

  <h5 class="mb-3">معلومات التواصل</h5>
  <div class="mb-3">
    <label class="form-label">البريد الإلكتروني</label>
    <input type="email" name="contact_email" value="<?= htmlspecialchars($contact_email) ?>" class="form-control text-end">
  </div>
  <div class="mb-3">
    <label class="form-label">رقم الهاتف</label>
    <input type="text" name="contact_phone" value="<?= htmlspecialchars($contact_phone) ?>" class="form-control text-end">
  </div>
  <div class="mb-3">
    <label class="form-label">واتساب</label>
    <input type="text" name="contact_whatsapp" value="<?= htmlspecialchars($contact_whatsapp) ?>" class="form-control text-end">
  </div>

  <div class="text-center mt-3">
    <button type="submit" class="btn btn-primary px-4">💾 حفظ الإعدادات</button>
  </div>
</form>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
