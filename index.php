<?php
// ==========================
// /index.php — elevated landing page version (modern hero + polished sections)
// ==========================
require_once __DIR__ . '/config.php';
include_once __DIR__ . '/includes/header.php';
?>

<section class="hero-landing position-relative d-flex align-items-center">
    <div class="container-fluid hero-split px-0">
        <div class="row g-0 align-items-center">

            <!-- LEFT COLUMN — LOGO (1/3 WIDTH) -->
            <div class="col-lg-4 hero-left d-flex align-items-center justify-content-center">
                <img src="assets/img/hsalon_logo.png" 
                     alt="HSALON Logo"
                     class="hero-logo-img">
            </div>

            <!-- RIGHT COLUMN — ORIGINAL CONTENT (2/3 WIDTH) -->
            <div class="col-lg-8 hero-right text-center px-4 fade-in">

                <h1 class="fw-bold display-4 mb-3"><?php echo $lang['welcome']; ?></h1>

                <div class="mt-4">
                    <a href="booking.php" class="btn btn-lg btn-primary px-5 py-3 rounded-pill me-2">
                        <i class="bi bi-calendar-check"></i> <?php echo $lang['book_now']; ?>
                    </a>

                    <?php
                    if (isset($_SESSION['admin_id']) || isset($_SESSION['client_id'])) {
                        $btnHref = isset($_SESSION['admin_id']) ? SITE_URL . '/admin/dashboard.php' : SITE_URL . '/member/index.php';
                        $btnLabel = $lang['dashboard'] ?? 'جدول المواعيد';
                        $btnIcon = 'bi-person-circle';
                    } else {
                        $btnHref = SITE_URL . '/login.php';
                        $btnLabel = $lang['register'] ?? 'تسجيل / إنشاء حساب';
                        $btnIcon = 'bi-person-plus';
                    }
                    ?>

                    <a href="<?php echo $btnHref; ?>" class="btn btn-lg btn-outline-light px-5 py-3 rounded-pill">
                        <i class="bi <?php echo $btnIcon; ?>"></i> <?php echo $btnLabel; ?>
                    </a>
                </div>

                <!-- CONTACT ICONS -->
                <div class="contact-icons mt-5 d-flex justify-content-center flex-wrap gap-3">
                    <a href="tel:+972501234567" class="btn btn-light rounded-circle p-3 fs-3">
                        <img src="assets/img/icons/phonecall.svg" width="30" height="30">
                    </a>
                    <a href="https://wa.me/972501234567" target="_blank" class="btn btn-success rounded-circle p-3 fs-3">
                        <img src="assets/img/icons/whatsapp.svg" width="30" height="30">
                    </a>
                    <a href="https://www.instagram.com/yourpage" target="_blank" class="btn btn-light rounded-circle p-3 fs-3">
                        <img src="assets/img/icons/instagram2.svg" width="30" height="30">
                    </a>
                    <a href="https://maps.google.com/?q=31.771959,35.217018" target="_blank" class="btn btn-light rounded-circle p-3 fs-3">
                        <img src="assets/img/icons/google-maps.svg" width="30" height="30">
                    </a>
                    <a href="https://waze.com/ul?ll=31.771959,35.217018&navigate=yes" target="_blank" class="btn btn-light rounded-circle p-3 fs-3">
                        <img src="assets/img/icons/waze3.svg" width="30" height="30">
                    </a>
                </div>

            </div>
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
    <div class="auto-scroll-gallery">
      <div class="gallery-track">
        <?php
        $galleryDir = __DIR__ . '/assets/img/gallery/';
        $images = glob($galleryDir . '*.{jpg,jpeg,png,webp}', GLOB_BRACE);
        if ($images) {
          $slice = array_slice($images, 0, 18);
          $imagesLoop = array_merge($slice, $slice);
          foreach ($imagesLoop as $img) {
            $basename = basename($img);
            echo '<div class="gallery-item"><img src="assets/img/gallery/' . $basename . '" alt="gallery"></div>';
          }
        } else {
          echo '<p class="text-muted">(' . $lang['gallery'] . ' ...)</p>';
        }
        ?>
      </div>
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

<!-- Reviews Section -->
<section class="reviews-section py-5 text-center">
  <div class="container">
    <h2 class="fw-bold mb-4"><?php echo $lang['client_reviews'] ?? 'تقييمات الزبائن'; ?></h2>

    <?php if (isset($_SESSION['client_id'])): ?>
      <?php
      $clientId = $_SESSION['client_id'];
      $existing = $pdo->prepare("SELECT * FROM reviews WHERE client_id=? LIMIT 1");
      $existing->execute([$clientId]);
      $review = $existing->fetch(PDO::FETCH_ASSOC);
      ?>

      <?php if ($review): ?>
        <form action="submit_review.php" method="POST" class="mb-5">
          <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
          <div class="star-rating mb-3">
            <?php for ($i = 5; $i >= 1; $i--): ?>
              <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" <?php echo ($review['rating'] == $i) ? 'checked' : ''; ?>>
              <label for="star<?php echo $i; ?>"><i class="bi bi-star-fill"></i></label>
            <?php endfor; ?>
          </div>

          <?php
          $srvStmt = $pdo->prepare("SELECT DISTINCT s.id, s.name FROM bookings b JOIN services s ON b.service_id=s.id WHERE b.client_id=? AND b.status='approved'");
          $srvStmt->execute([$clientId]);
          $userServices = $srvStmt->fetchAll(PDO::FETCH_ASSOC);
          ?>
          <?php if ($userServices): ?>
            <select name="service_id" class="form-select mb-3">
              <option value=""><?php echo $lang['select_service'] ?? 'الخدمة (اختياري)'; ?></option>
              <?php foreach ($userServices as $s): ?>
                <option value="<?php echo $s['id']; ?>" <?php echo ($review['service_id'] == $s['id']) ? 'selected' : ''; ?>>
                  <?php echo clean($s['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          <?php endif; ?>

          <textarea name="comment" class="form-control mb-3" rows="3"><?php echo clean($review['comment']); ?></textarea>
          <button type="submit" class="btn btn-primary rounded-pill px-5"><?php echo $lang['update_review'] ?? 'تحديث التقييم'; ?></button>
        </form>
      <?php else: ?>
        <form action="submit_review.php" method="POST" class="mb-5">
          <div class="star-rating mb-3">
            <?php for ($i = 5; $i >= 1; $i--): ?>
              <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required>
              <label for="star<?php echo $i; ?>"><i class="bi bi-star-fill"></i></label>
            <?php endfor; ?>
          </div>

          <?php
          $srvStmt = $pdo->prepare("SELECT DISTINCT s.id, s.name FROM bookings b JOIN services s ON b.service_id=s.id WHERE b.client_id=? AND b.status='approved'");
          $srvStmt->execute([$clientId]);
          $userServices = $srvStmt->fetchAll(PDO::FETCH_ASSOC);
          ?>
          <?php if ($userServices): ?>
            <select name="service_id" class="form-select mb-3">
              <option value=""><?php echo $lang['select_service'] ?? 'اختر الخدمة (اختياري)'; ?></option>
              <?php foreach ($userServices as $s): ?>
                <option value="<?php echo $s['id']; ?>"><?php echo clean($s['name']); ?></option>
              <?php endforeach; ?>
            </select>
          <?php endif; ?>

          <textarea name="comment" class="form-control mb-3" rows="3" placeholder="<?php echo $lang['write_review'] ?? 'اكتب رأيك هنا...'; ?>"></textarea>
          <button type="submit" class="btn btn-primary rounded-pill px-5"><?php echo $lang['submit_review'] ?? 'إرسال التقييم'; ?></button>
        </form>
      <?php endif; ?>
    <?php else: ?>
      <div class="reviews-display">
        <?php
        try {
          $stmt = $pdo->query("SELECT r.rating, r.comment, c.name, s.name AS service_name FROM reviews r LEFT JOIN clients c ON r.client_id=c.id LEFT JOIN services s ON r.service_id=s.id ORDER BY r.created_at DESC LIMIT 6");
          $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
          if ($reviews) {
            echo '<div class="row justify-content-center">';
            foreach ($reviews as $rev) {
              echo '<div class="col-md-4 mb-4">';
              echo '<div class="card border-0 shadow-sm rounded-4 h-100">';
              echo '<div class="card-body">';
              echo '<div class="mb-2 text-warning">';
              for ($i = 1; $i <= 5; $i++) echo $i <= $rev['rating'] ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
              echo '</div>';
              echo '<p class="text-muted small mb-2">"' . clean($rev['comment']) . '"</p>';
              if (!empty($rev['service_name'])) echo '<div class="small text-rose">' . ($lang['service_label'] ?? 'الخدمة: ') . clean($rev['service_name']) . '</div>';
              echo '<div class="fw-semibold mt-2">' . clean($rev['name']) . '</div>';
              echo '</div></div></div>';
            }
            echo '</div>';
          } else {
            echo '<p class="text-muted">(' . ($lang['no_reviews'] ?? 'لا توجد تقييمات بعد') . ')</p>';
          }
        } catch (Exception $e) {
          echo '<p class="text-danger text-center">Error loading reviews.</p>';
        }
        ?>
      </div>
    <?php endif; ?>
  </div>
</section>





<?php include_once __DIR__ . '/includes/footer.php'; ?>
