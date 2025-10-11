<?php
// ==========================
// /gallery.php
// ==========================
require_once __DIR__ . '/config.php';
include_once __DIR__ . '/includes/header.php';
?>

<h2 class="text-center mb-4"><?php echo $lang['gallery']; ?></h2>

<div class="row justify-content-center">
<?php
$images = glob(__DIR__ . '/assets/img/sample*.jpg');
if ($images) {
    foreach ($images as $img) {
        $basename = basename($img);
        echo '<div class="col-md-3 col-sm-4 mb-4">';
        echo '<div class="card shadow-sm">';
        echo '<img src="assets/img/' . $basename . '" class="card-img-top rounded" alt="gallery">';
        echo '</div>';
        echo '</div>';
    }
} else {
    echo '<p class="text-center text-muted">(' . $lang['gallery'] . ' ...)</p>';
}
?>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
