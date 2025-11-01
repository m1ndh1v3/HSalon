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

<section class="py-5 fade-in text-center">
  <div class="container">
    <h2 class="fw-bold mb-5"><?php echo $lang['my_account']; ?></h2>

    <div class="row justify-content-center g-4">
      <div class="col-md-4 col-sm-6">
        <a href="profile.php" class="dashboard-card card text-center shadow-sm text-decoration-none">
          <div class="card-body py-4">
            <i class="bi bi-person-circle fs-1 mb-3 icon"></i>
            <h5 class="fw-semibold"><?php echo $lang['profile']; ?></h5>
          </div>
        </a>
      </div>
      <div class="col-md-4 col-sm-6">
        <a href="bookings.php" class="dashboard-card card text-center shadow-sm text-decoration-none">
          <div class="card-body py-4">
            <i class="bi bi-calendar-check fs-1 mb-3 icon"></i>
            <h5 class="fw-semibold"><?php echo $lang['my_bookings']; ?></h5>
          </div>
        </a>
      </div>
    </div>
  </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
