<?php
// ==========================
// /booking.php — localized version (Arabic/English)
// ==========================
require_once __DIR__ . '/config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once __DIR__ . '/includes/header.php';

$langKey = $_SESSION['lang'] ?? 'ar';

try {
    $stmt = $pdo->query("SELECT * FROM services ORDER BY id ASC");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                echo '<div class="alert alert-danger text-center">' . $lang['booking_unavailable'] . '</div>';
            } else {
                $stmt = $pdo->prepare("INSERT INTO bookings (client_id, name, phone, email, service_id, date, time, notify_method, status, created_at)
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$cid, $name, $phone, $email, $service, $date, $time, $notify, $status]);
                $bookId = $pdo->lastInsertId();
                log_debug("New booking created: ID=$bookId for $name");

                $msg = "Your appointment request has been received and is now under review.\nService ID: $service\nDate: $date $time";

                if ($notify === 'whatsapp') {
                    $wa = send_whatsapp_message($phone, $msg);
                    echo '<div class="alert alert-success text-center">' . $lang['booking_success_whatsapp'] . '<br><a href="'.$wa.'" target="_blank">WhatsApp</a></div>';
                } else {
                    send_email($email, "Appointment Confirmation - ".SITE_NAME, nl2br($msg));
                    echo '<div class="alert alert-success text-center">' . $lang['booking_success_sent'] . '</div>';
                }
            }
        } catch (Exception $e) {
            log_debug("Booking insert failed: ".$e->getMessage());
            echo '<div class="alert alert-danger text-center">' . $lang['booking_error'] . '</div>';
        }
    } else {
        echo '<div class="alert alert-warning text-center">' . $lang['booking_required_fields'] . '</div>';
    }
}
?>

<h2 class="text-center mb-4"><?php echo $lang['booking_page_title']; ?></h2>

<form method="POST" class="col-md-8 mx-auto card p-4 shadow-sm <?php echo ($langKey == 'ar') ? 'text-end' : 'text-start'; ?>" dir="<?php echo ($langKey == 'ar') ? 'rtl' : 'ltr'; ?>">
  <?php if (!empty($cid)): ?>
    <input type="hidden" name="name"  value="<?php echo clean($cname); ?>">
    <input type="hidden" name="phone" value="<?php echo clean($cphone); ?>">
    <input type="hidden" name="email" value="<?php echo clean($cemail); ?>">
    <p class="text-center mb-3">
      <?php echo ($langKey == 'ar') ? "يتم الموعد باسم" : "Appointment will be under the name of"; ?>
      <strong><?php echo clean($cname); ?></strong>
      <?php if ($cemail): ?>(<?php echo clean($cemail); ?>)<?php endif; ?>
    </p>
  <?php else: ?>
    <div class="mb-3">
      <label class="form-label"><?php echo $lang['booking_full_name']; ?></label>
      <input type="text" name="name" class="form-control <?php echo ($langKey == 'ar') ? 'text-end' : ''; ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label"><?php echo $lang['booking_phone']; ?></label>
      <input type="text" name="phone" class="form-control <?php echo ($langKey == 'ar') ? 'text-end' : ''; ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label"><?php echo $lang['booking_email_optional']; ?></label>
      <input type="email" name="email" class="form-control <?php echo ($langKey == 'ar') ? 'text-end' : ''; ?>">
    </div>
  <?php endif; ?>

  <div class="mb-3">
    <label class="form-label"><?php echo $lang['booking_select_service']; ?></label>
    <select name="service" class="form-select <?php echo ($langKey == 'ar') ? 'text-end' : ''; ?>" required>
      <option value="" selected disabled hidden>-- <?php echo $lang['choose_service']; ?> --</option>
      <?php foreach ($services as $srv): 
        $srvName = $srv['name_' . $langKey] ?? $srv['name'];
      ?>
        <option value="<?php echo $srv['id']; ?>">
          <?php echo clean($srvName); ?> (<?php echo $srv['price']; ?>₪)
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="row mb-3">
    <div class="col-md-6">
      <label class="form-label"><?php echo $lang['booking_select_date']; ?></label>
      <input type="date" id="datePicker" name="date" class="form-control <?php echo ($langKey == 'ar') ? 'text-end' : ''; ?>" required min="<?php echo date('Y-m-d'); ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label"><?php echo $lang['booking_select_time']; ?></label>
      <select name="time" id="timeSelect" class="form-select <?php echo ($langKey == 'ar') ? 'text-end' : ''; ?>" required disabled>
        <option value=""><?php echo $lang['booking_select_time_hint']; ?></option>
      </select>
    </div>
  </div>

  <div class="mb-3">
    <label class="form-label"><?php echo $lang['booking_notify_method']; ?></label>
    <select name="notify" class="form-select <?php echo ($langKey == 'ar') ? 'text-end' : ''; ?>">
      <option value="email"><?php echo $lang['booking_notify_email']; ?></option>
      <option value="whatsapp"><?php echo $lang['booking_notify_whatsapp']; ?></option>
    </select>
  </div>

  <button type="submit" class="btn btn-primary w-100"><?php echo $lang['booking_button_confirm']; ?></button>
</form>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
