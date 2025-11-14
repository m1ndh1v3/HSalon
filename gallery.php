<?php
// ==========================
// /gallery.php — compact masonry layout version
// ==========================
require_once __DIR__ . '/config.php';
include_once __DIR__ . '/includes/header.php';
?>

  <section class="container text-center">
    <div class="container">
      <h2 class="fw-bold mb-4"><?php echo $lang['gallery']; ?></h2>

      <div class="masonry-grid">
        <?php
        $galleryDir = __DIR__ . '/assets/img/gallery/';
        $images = glob($galleryDir . '*.{jpg,jpeg,png,webp}', GLOB_BRACE);

        if ($images) {
            foreach ($images as $img) {
                $basename = basename($img);
                echo '<a href="assets/img/gallery/' . $basename . '" data-bs-toggle="lightbox" data-gallery="hsalon-gallery">';
                echo '<img src="assets/img/gallery/' . $basename . '" alt="gallery">';
                echo '</a>';
            }
        } else {
            echo '<p class="text-center text-muted">(' . $lang['gallery'] . ' ...)</p>';
        }
        ?>
      </div>

  </section>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
