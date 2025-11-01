<?php
require_once __DIR__.'/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$langKey = $_SESSION['lang'] ?? 'ar';
$langFile = __DIR__."/lang/{$langKey}.php";
if (file_exists($langFile)) include $langFile;

$bk = $_SESSION['last_booking'] ?? null;
if (!$bk) { header("Location: booking.php"); exit; }

include_once __DIR__.'/includes/header.php';
?>


<section class="py-5 fade-in text-center" style="min-height:80vh;">
  <div class="container">
    <div class="card mx-auto shadow-lg border-0 p-4" style="max-width:600px;">
      <div class="card-body position-relative" style="z-index:3;">
        <h2 class="text-success fw-bold mb-3"><?php echo $lang['booking_success_title'] ?? 'تم إرسال طلبك بنجاح'; ?></h2>
        <p class="lead mb-4"><?php echo $lang['booking_pending_msg'] ?? 'طلب الموعد قيد المراجعة وسيتم تأكيده قريباً.'; ?></p>
        <hr class="my-4">

        <div class="booking-info-box p-3 rounded-4 mb-4">
          <p><strong><?php echo $lang['booking_service'] ?? 'الخدمة'; ?>:</strong> <?php echo clean($bk['service']); ?></p>
          <p><strong><?php echo $lang['booking_time'] ?? 'الموعد'; ?>:</strong><br>
            <?php echo date('d/m/Y', strtotime($bk['date'])); ?><br><?php echo clean($bk['time']); ?></p>
          <p><strong><?php echo $lang['client_name'] ?? 'الاسم'; ?>:</strong> <?php echo clean($bk['name']); ?></p>
          <?php
          $displayPhone = preg_replace('/^\+?972/', '0', $bk['phone']);
          ?>
          <p><strong><?php echo $lang['booking_phone'] ?? 'رقم الهاتف'; ?>:</strong> <?php echo clean($displayPhone); ?></p>
        </div>

        <p class="text-muted small mt-3">
          <?php if ($langKey === 'ar'): ?>
            للمزيد من المعلومات أو بحالات الضرورة, يمكنكِ التواصل عبر الواتساب.
          <?php else: ?>
            For further information or updates, you can contact us via WhatsApp.
          <?php endif; ?>
        </p>

        <?php if (!empty($bk['wa'])): ?>
          <a href="<?php echo $bk['wa']; ?>" target="_blank" class="btn btn-success mt-3 px-4 py-2 rounded-pill position-relative" style="z-index:5;">
            <i class="bi bi-whatsapp"></i> <?php echo $lang['booking_contact_whatsapp'] ?? 'Whatsapp/واتساب'; ?>
          </a>
        <?php endif; ?>

        <div class="mt-4 d-flex justify-content-center flex-wrap gap-2 success-actions position-relative" style="z-index:5;">
          <a href="member/bookings.php" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-calendar-check"></i> <?php echo $lang['my_bookings'] ?? 'مواعيدي'; ?>
          </a>
          <a href="booking.php" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="bi bi-plus-circle"></i> <?php echo $lang['book_another'] ?? 'لحجز موعد آخر'; ?>
          </a>
          <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="bi bi-house"></i> <?php echo $lang['back_home'] ?? 'الصفحة الرئيسية'; ?>
          </a>
        </div>
      </div>
    </div>
  </div>
</section>


<?php
unset($_SESSION['last_booking']);
include_once __DIR__.'/includes/footer.php';
?>
