<?php
// ==========================
// /member/index.php  (Dashboard)
// ==========================
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['client_id'])) {
    header("Location: ../login.php");
    exit;
}
include_once __DIR__ . '/../includes/header.php';
?>

<h2 class="text-center mb-4"><?php echo $lang['my_account']; ?></h2>

<div class="row justify-content-center">
  <div class="col-md-4 mb-3">
    <a href="profile.php" class="card text-center shadow-sm text-decoration-none text-dark">
      <div class="card-body">
        <i class="bi bi-person-circle fs-1 mb-2"></i>
        <h5><?php echo $lang['profile']; ?></h5>
      </div>
    </a>
  </div>
  <div class="col-md-4 mb-3">
    <a href="bookings.php" class="card text-center shadow-sm text-decoration-none text-dark">
      <div class="card-body">
        <i class="bi bi-calendar-check fs-1 mb-2"></i>
        <h5><?php echo $lang['my_bookings']; ?></h5>
      </div>
    </a>
  </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
