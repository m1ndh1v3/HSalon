<?php
// ==========================
// /booking.php — public booking page (modern UI + modal confirmation)
// ==========================
require_once __DIR__ . '/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

include_once __DIR__ . '/includes/header.php';

$langKey = $_SESSION['lang'] ?? 'ar';

try {
    $services = $pdo->query("SELECT * FROM services WHERE status='active' ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $services = [];
    log_debug("Error loading services for booking: " . $e->getMessage());
}

$cid    = $_SESSION['client_id']    ?? null;
$cname  = $_SESSION['client_name']  ?? '';
$cphone = $_SESSION['client_phone'] ?? '';
$cemail = $_SESSION['client_email'] ?? '';

$alert = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = clean($_POST['name'] ?? $cname);
    $phone   = normalize_phone(clean($_POST['phone'] ?? $cphone));
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
                $alert = '<div class="alert alert-danger text-center">'.$lang['booking_unavailable'].'</div>';
            } else {
                $stmt = $pdo->prepare("INSERT INTO bookings (client_id, name, phone, email, service_id, date, time, notify_method, status, created_at)
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$cid, $name, $phone, $email, $service, $date, $time, $notify, 'pending']);
                $bookId = $pdo->lastInsertId();

                $serviceName = $pdo->prepare("SELECT name FROM services WHERE id=?");
                $serviceName->execute([$service]);
                $serviceName = $serviceName->fetchColumn() ?: 'غير محددة';

                $msg = "تم إرسال طلب الحجز بنجاح!
                \nيرجى انتظار تأكيد الموعد من قبل الصالون.
                \nالاسم: $name\nالهاتف: $phone
                \nالخدمة: $serviceName\nالتاريخ: $date $time";

                add_notification('booking', "طلب موعد جديد من $name لخدمة $serviceName بتاريخ $date $time");

                $wa = null;
                if ($notify === 'whatsapp') {
                    $wa = send_whatsapp_message($phone, $msg);
                } else {
                    send_email($email, "تأكيد موعد - ".SITE_NAME, nl2br($msg));
                }

                $_SESSION['last_booking'] = [
                    'service' => $serviceName,
                    'date'    => $date,
                    'time'    => $time,
                    'name'    => $name,
                    'phone'   => $phone,
                    'notify'  => $notify,
                    'wa'      => $wa
                ];
                header("Location: booking_success.php");
                exit;
            }
        } catch (Exception $e) {
            log_debug("Booking insert failed: ".$e->getMessage());
            $alert = '<div class="alert alert-danger text-center">'.$lang['booking_error'].'</div>';
        }
    }

}

$isRtl  = ($langKey === 'ar');
$textDir = $isRtl ? 'rtl' : 'ltr';
$textAlign = $isRtl ? 'text-end' : 'text-start';
?>

<div class="container my-4">
  <h2 class="text-center mb-4"><?php echo $lang['booking_page_title']; ?></h2>

  <div class="row justify-content-center">
    <div class="col-lg-8">

      <?php echo $alert; ?>

      <div class="card mb-3 p-3 booking-card-main">
        <div class="d-flex align-items-center gap-2 mb-2 <?php echo $textAlign; ?>">
          <span class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;background:linear-gradient(135deg,#b76e79,#d68d9b);color:#fff;">
            <i class="bi bi-person-heart"></i>
          </span>
          <div>
            <h5 class="mb-0"><?php echo $lang['booking_step_client'] ?? ($isRtl ? 'تأكيد البيانات' : 'Your details'); ?></h5>
            <small class="text-muted"><?php echo $lang['booking_step_client_hint'] ?? ($isRtl ? 'تأكد من صحة بيانات التواصل' : 'Make sure we can reach you.'); ?></small>
          </div>
        </div>

        <div class="<?php echo $textAlign; ?>" dir="<?php echo $textDir; ?>">
          <?php if (!empty($cid)): ?>
            <input type="hidden" name="name" form="bookingForm" value="<?php echo clean($cname); ?>">
            <input type="hidden" name="phone" form="bookingForm" value="<?php echo clean($cphone); ?>">
            <input type="hidden" name="email" form="bookingForm" value="<?php echo clean($cemail); ?>">
            <div class="booking-info-box rounded-3 p-3 mb-0">
              <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
                <div>
                  <div class="fw-semibold"><?php echo $isRtl ? 'سيتم حجز الموعد باسم:' : 'Booking for:'; ?></div>
                  <div><?php echo clean($cname); ?><?php if ($cemail): ?> (<?php echo clean($cemail); ?>)<?php endif; ?></div>
                  <?php if ($cphone): ?>
                    <div class="small text-muted"><?php echo $isRtl ? 'هاتف:' : 'Phone:'; ?> <?php echo clean($cphone); ?></div>
                  <?php endif; ?>
                </div>
                <div class="small text-muted">
                  <i class="bi bi-info-circle"></i>
                  <?php echo $lang['booking_logged_in_hint'] ?? ($isRtl ? 'يمكنك تغيير بياناتك من صفحة الحساب.' : 'Update details from your profile page.'); ?>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <form method="POST" id="bookingForm" class="<?php echo $textAlign; ?>" dir="<?php echo $textDir; ?>">

        <?php if (empty($cid)): ?>
          <div class="card mb-3 p-3 booking-card-section">
            <div class="d-flex align-items-center gap-2 mb-3">
              <span class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;background:linear-gradient(135deg,#b76e79,#d68d9b);color:#fff;">
                <i class="bi bi-person-vcard"></i>
              </span>
              <div>
                <h5 class="mb-0"><?php echo $lang['booking_step_client'] ?? ($isRtl ? 'بياناتك' : 'Your details'); ?></h5>
                <small class="text-muted"><?php echo $lang['booking_step_client_hint'] ?? ($isRtl ? 'املأ بيانات الاتصال الأساسية' : 'Fill in your contact details.'); ?></small>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label"><?php echo $lang['booking_full_name']; ?></label>
              <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label"><?php echo $lang['booking_phone']; ?></label>
              <input type="text" name="phone" class="form-control" required>
            </div>
            <div class="mb-0">
              <label class="form-label"><?php echo $lang['booking_email_optional']; ?></label>
              <input type="email" name="email" class="form-control">
            </div>
          </div>
        <?php endif; ?>

        <div class="card mb-3 p-3 booking-card-section">
          <div class="d-flex align-items-center gap-2 mb-3">
            <span class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;background:linear-gradient(135deg,#b76e79,#d68d9b);color:#fff;">
              <i class="bi bi-scissors"></i>
            </span>
            <div>
              <h5 class="mb-0"><?php echo $lang['booking_select_service']; ?></h5>
              <small class="text-muted"><?php echo $lang['booking_select_service_hint'] ?? ($isRtl ? 'اختاري الخدمة المطلوبة' : 'Choose the service you want.'); ?></small>
            </div>
          </div>

          <div class="mb-0">
            <select name="service" class="form-select" required>
              <option value="" selected disabled hidden>-- <?php echo $lang['choose_service']; ?> --</option>
              <?php foreach ($services as $srv): ?>
                <option value="<?php echo $srv['id']; ?>">
                  <?php echo clean($srv['name']); ?> (<?php echo $srv['price']; ?>₪)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="card mb-3 p-3 booking-card-section">
          <div class="d-flex align-items-center gap-2 mb-3">
            <span class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;background:linear-gradient(135deg,#b76e79,#d68d9b);color:#fff;">
              <i class="bi bi-calendar-event"></i>
            </span>
            <div>
              <h5 class="mb-0"><?php echo $lang['booking_step_datetime'] ?? ($isRtl ? 'التاريخ والوقت' : 'Date & time'); ?></h5>
              <small class="text-muted">
                <?php echo $lang['booking_step_datetime_hint'] ?? ($isRtl ? 'اختاري موعداً متاحاً حسب جدول العمل.' : 'Pick an available slot matching our working hours.'); ?>
              </small>
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label"><?php echo $lang['booking_select_date']; ?></label>

              <input type="date" id="datePicker" name="date" class="form-control" required
                    min="<?php echo date('Y-m-d'); ?>">

          </div>

          <div class="col-md-6">
            <label class="form-label"><?php echo $lang['booking_select_time']; ?></label>

              <select name="time" id="timeSelect" class="form-select" required disabled>
                <option value=""><?php echo $lang['booking_select_time_hint']; ?></option>
              </select>

          </div>

        </div>
        </div>

        <div class="card mb-3 p-3 booking-card-section">
          <div class="d-flex align-items-center gap-2 mb-3">
            <span class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;background:linear-gradient(135deg,#b76e79,#d68d9b);color:#fff;">
              <i class="bi bi-bell"></i>
            </span>
            <div>
              <h5 class="mb-0"><?php echo $lang['booking_notify_method']; ?></h5>
              <small class="text-muted"><?php echo $lang['booking_notify_hint'] ?? ($isRtl ? 'اختاري طريقة استلام تأكيد أو تذكير بالموعد.' : 'How would you like to receive confirmation or reminders?'); ?></small>
            </div>
          </div>

          <div class="mb-0">
            <select name="notify" class="form-select">
              <option value="whatsapp"><?php echo $lang['booking_notify_whatsapp']; ?></option>
              <!-- <option value="email"><?php echo $lang['booking_notify_email']; ?></option> -->
            </select>
          </div>
        </div>

        <div class="card p-3 booking-card-submit mb-5">
          <button type="submit" class="btn btn-primary w-100 py-2">
            <i class="bi bi-check2-circle"></i>
            <span class="ms-2"><?php echo $lang['booking_button_confirm']; ?></span>
          </button>
        </div>

      </form>
    </div>
  </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
