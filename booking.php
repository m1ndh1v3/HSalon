<?php
// ==========================
// /booking.php — Simplified Public Booking Form (Safe Final Version)
// ==========================
require_once __DIR__ . '/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
include_once __DIR__ . '/includes/header.php';

$langKey = $_SESSION['lang'] ?? 'ar';
?>

<div class="container py-5" dir="<?php echo $langKey == 'ar' ? 'rtl' : 'ltr'; ?>">
  <div class="col-md-10 mx-auto text-center mb-4">
    <h2 class="fw-bold mb-3"><?php echo $lang['booking_page_title'] ?? 'احجز موعدك الآن'; ?></h2>
    <p class="text-muted"><?php echo $lang['booking_intro'] ?? 'املأ التفاصيل أدناه لحجز موعد في الصالون'; ?></p>
  </div>

  <?php include_once __DIR__ . '/booking_form_core.php'; ?>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
