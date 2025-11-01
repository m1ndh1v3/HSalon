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
    $phone = normalize_phone(clean($_POST['phone'] ?? $cphone));
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
?>

<h2 class="text-center mb-4"><?php echo $lang['booking_page_title']; ?></h2>

<div class="col-md-8 mx-auto card p-4 shadow-sm <?php echo ($langKey == 'ar') ? 'text-end' : 'text-start'; ?>" dir="<?php echo ($langKey == 'ar') ? 'rtl' : 'ltr'; ?>">
  <?php echo $alert; ?>

  <form method="POST">
    <?php if (!empty($cid)): ?>
      <input type="hidden" name="name" value="<?php echo clean($cname); ?>">
      <input type="hidden" name="phone" value="<?php echo clean($cphone); ?>">
      <input type="hidden" name="email" value="<?php echo clean($cemail); ?>">
      <p class="text-center mb-3">
        <?php echo ($langKey == 'ar') ? "يتم حجز الموعد باسم" : "Booking appointment for"; ?>
        <strong><?php echo clean($cname); ?></strong>
        <?php if ($cemail): ?>(<?php echo clean($cemail); ?>)<?php endif; ?>
      </p>
    <?php else: ?>
      <div class="mb-3">
        <label class="form-label"><?php echo $lang['booking_full_name']; ?></label>
        <input type="text" name="name" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label"><?php echo $lang['booking_phone']; ?></label>
        <input type="text" name="phone" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label"><?php echo $lang['booking_email_optional']; ?></label>
        <input type="email" name="email" class="form-control">
      </div>
    <?php endif; ?>

    <div class="mb-3">
      <label class="form-label"><?php echo $lang['booking_select_service']; ?></label>
      <select name="service" class="form-select" required>
        <option value="" selected disabled hidden>-- <?php echo $lang['choose_service']; ?> --</option>
        <?php foreach ($services as $srv): ?>
          <option value="<?php echo $srv['id']; ?>"><?php echo clean($srv['name']); ?> (<?php echo $srv['price']; ?>₪)</option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="row mb-3">
      <div class="col-md-6">
        <label class="form-label"><?php echo $lang['booking_select_date']; ?></label>
        <input type="date" id="datePicker" name="date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label"><?php echo $lang['booking_select_time']; ?></label>
        <select name="time" id="timeSelect" class="form-select" required disabled>
          <option value=""><?php echo $lang['booking_select_time_hint']; ?></option>
        </select>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label"><?php echo $lang['booking_notify_method']; ?></label>
      <select name="notify" class="form-select">
        <option value="whatsapp"><?php echo $lang['booking_notify_whatsapp']; ?></option>
        <option value="email"><?php echo $lang['booking_notify_email']; ?></option>
      </select>
    </div>

    <button type="submit" class="btn btn-primary w-100"><?php echo $lang['booking_button_confirm']; ?></button>
  </form>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
