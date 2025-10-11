<?php
// ==========================
// /index.php
// ==========================
require_once __DIR__ . '/config.php';
include_once __DIR__ . '/includes/header.php';
?>

<div class="text-center py-5">
  <h1 class="fw-bold mb-3"><?php echo $lang['welcome']; ?></h1>
  <p class="lead mb-4"><?php echo SITE_NAME; ?> — <?php echo $lang['book_now']; ?> 💇‍♀️</p>
  <a href="booking.php" class="btn btn-lg btn-primary px-4 py-2">
    <i class="bi bi-calendar-check"></i> <?php echo $lang['book_now']; ?>
  </a>
</div>

<section class="my-5">
  <h2 class="text-center mb-4"><?php echo $lang['services']; ?></h2>
  <div class="row justify-content-center">
    <?php
    try {
        $stmt = $pdo->query("SELECT * FROM services ORDER BY id DESC LIMIT 6");
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($services) {
            foreach ($services as $srv) {
                echo '<div class="col-md-4 col-sm-6 mb-4">';
                echo '<div class="card shadow-sm h-100">';
                echo '<div class="card-body text-center">';
                echo '<h5 class="card-title">' . clean($srv['name']) . '</h5>';
                echo '<p class="card-text text-muted">' . clean($srv['duration']) . ' ' . $lang['service_duration'] . '</p>';
                echo '<p class="fw-bold">' . clean($srv['price']) . '₪</p>';
                echo '</div>';
                echo '</div>';
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
    <a href="services.php" class="btn btn-outline-secondary"><?php echo $lang['services']; ?></a>
  </div>
</section>

<section class="my-5 text-center">
  <h2 class="mb-4"><?php echo $lang['gallery']; ?></h2>
  <div class="row justify-content-center">
    <?php
    $images = glob(__DIR__ . '/assets/img/sample*.jpg');
    if ($images) {
      foreach (array_slice($images, 0, 6) as $img) {
        $basename = basename($img);
        echo '<div class="col-md-3 col-sm-4 mb-3">';
        echo '<img src="assets/img/' . $basename . '" class="img-fluid rounded shadow-sm" alt="gallery">';
        echo '</div>';
      }
    } else {
      echo '<p class="text-muted">(' . $lang['gallery'] . ' ...)</p>';
    }
    ?>
  </div>
  <a href="gallery.php" class="btn btn-outline-secondary mt-3"><?php echo $lang['gallery']; ?></a>
</section>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
