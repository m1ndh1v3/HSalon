<?php
// ==========================
// /index.php — elevated landing page version (modern hero + polished sections)
// ==========================
require_once __DIR__ . '/config.php';
include_once __DIR__ . '/includes/header.php';
?>
<!-- Hero Section -->
<section class="hero-landing d-flex align-items-center text-center position-relative">
  <div class="hero-logo-bg">
    <img src="assets/img/hsalon_logo.png" alt="Logo Watermark">
  </div>

  <div class="container fade-in position-relative">
    <h1 class="fw-bold display-4 mb-3"><?php echo $lang['welcome']; ?></h1>

    <div class="mt-4">
      <a href="booking.php" class="btn btn-lg btn-primary px-5 py-3 rounded-pill me-2">
        <i class="bi bi-calendar-check"></i> <?php echo $lang['book_now']; ?>
      </a>
      <?php
      if (isset($_SESSION['admin_id']) || isset($_SESSION['client_id'])) {
        $btnHref = isset($_SESSION['admin_id']) ? SITE_URL . '/admin/dashboard.php' : SITE_URL . '/member/index.php';
        $btnLabel = $lang['dashboard'] ?? 'جدول المواعيد';
      } else {
        $btnHref = SITE_URL . '/login.php';
        $btnLabel = $lang['login_signup'] ?? 'تسجيل / إنشاء حساب';
      }
      ?>
      <a href="<?php echo $btnHref; ?>" class="btn btn-lg btn-outline-light px-5 py-3 rounded-pill">
        <?php echo $btnLabel; ?>
      </a>
    </div>
  </div>

  <div class="hero-overlay"></div>
</section>

<!-- About Section -->
<!-- <section class="about-highlight py-5 text-center">
  <div class="container">
    <h2 class="fw-bold mb-4"><?php echo $lang['about_title'] ?? 'عن الصالون'; ?></h2> -->
    <!-- <p class="lead mx-auto mb-4" style="max-width: 700px;">
      <?php //echo $lang['about_text'] ?? 'صالوننا يجمع بين الخبرة والذوق الرفيع لتقديم تجربة جمال متكاملة. من العناية بالشعر إلى خدمات المكياج والتجميل، نحن نهتم بكل تفصيلة تُبرز أناقتك.'; ?>
    </p> -->
    <!-- <a href="about.php" class="btn btn-outline-secondary rounded-pill px-4"><?php echo $lang['learn_more'] ?? 'المزيد عنا'; ?></a>
  </div>
</section> -->

<!-- Services Section -->
<section class="services-preview services-highlight py-5">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-bold"><?php echo $lang['services']; ?></h2>
    </div>
    <div class="row justify-content-center">
      <?php
      try {
        $stmt = $pdo->query("SELECT * FROM services ORDER BY id DESC LIMIT 6");
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $langKey = $_SESSION['lang'] ?? 'ar';
        if ($services) {
          foreach ($services as $srv) {
            $srvName = $srv['name_' . $langKey] ?? $srv['name'];
            echo '<div class="col-lg-4 col-md-6 mb-4">';
            echo '  <div class="service-card card border-0 shadow-sm h-100 text-center rounded-4 overflow-hidden">';
            echo '    <div class="card-body">';
            echo '      <div class="icon-wrapper mb-3"><i class="bi bi-brush"></i></div>';
            echo '      <h5 class="card-title fw-bold">' . clean($srvName) . '</h5>';
            echo '      <p class="card-text text-muted mb-2">' . clean($srv['duration']) . ' ' . $lang['service_duration'] . '</p>';
            echo '      <p class="fw-semibold fs-5">' . clean($srv['price']) . '₪</p>';
            echo '    </div>';
            echo '  </div>';
            echo '</div>';
          }
        } else {
          echo '<p class="text-center text-muted">(' . $lang['services'] . ' ...)</p>';
        }
      } catch (Exception $e) {
        log_debug("Error loading services on index: " . $e->getMessage());
        echo '<p class="text-danger text-center">Error loading services list.</p>';
      }
      ?>
    </div>
    <div class="text-center">
      <a href="services.php" class="btn btn-outline-secondary rounded-pill px-4"><?php echo $lang['view_more_services']; ?></a>
    </div>
  </div>
</section>

<!-- Gallery Section -->
<section class="gallery-showcase py-5 text-center">
  <div class="container">
    <h2 class="fw-bold mb-4"><?php echo $lang['gallery']; ?></h2>
    <div class="masonry-grid">
      <?php
      $galleryDir = __DIR__ . '/assets/img/gallery/';
      $images = glob($galleryDir . '*.{jpg,jpeg,png,webp}', GLOB_BRACE);
      if ($images) {
        foreach (array_slice($images, 0, 8) as $img) {
          $basename = basename($img);
          echo '<a href="assets/img/gallery/' . $basename . '" data-bs-toggle="lightbox" data-gallery="index-gallery">';
          echo '<img src="assets/img/gallery/' . $basename . '" alt="gallery">';
          echo '</a>';
        }
      } else {
        echo '<p class="text-muted">(' . $lang['gallery'] . ' ...)</p>';
      }
      ?>
    </div>
    <a href="gallery.php" class="btn btn-outline-secondary mt-4 rounded-pill px-4"><?php echo $lang['view_more_gallery']; ?></a>
  </div>
</section>

<!-- Working Hours -->
<?php
try {
  $stmt = $pdo->query("SELECT * FROM work_hours ORDER BY FIELD(day_name,'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday')");
  $hours = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  $hours = [];
}
if ($hours):
?>
<section class="work-hours-section py-5 text-center">
  <div class="container">
    <h2 class="fw-bold mb-4"><i class="bi bi-clock"></i> <?php echo $lang['working_hours']; ?></h2>
    <div class="d-flex flex-wrap justify-content-center gap-3">
      <?php foreach ($hours as $h): 
        $open  = substr($h['open_time'], 0, 5);
        $close = substr($h['close_time'], 0, 5);
        $break = ($h['break_start'] && $h['break_end']) ? sprintf($lang['break_time'], $h['break_start'], $h['break_end']) : '';
        $isOpen = $h['is_open'];
        $dayKey = strtolower($h['day_name']);
        $dayLabel = $lang[$dayKey] ?? $h['day_name'];
      ?>
      <div class="card hour-card shadow-sm border-0 rounded-4 px-3 py-2 text-center flex-fill">
        <div class="fw-semibold"><?php echo $dayLabel; ?></div>
        <?php if ($isOpen): ?>
          <div class="small text-muted"><?php echo sprintf($lang['open_from_to'], $open, $close); ?></div>
          <?php if ($break): ?><div class="small text-muted"><?php echo $break; ?></div><?php endif; ?>
        <?php else: ?>
          <div class="text-danger fw-bold"><?php echo $lang['closed']; ?></div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
