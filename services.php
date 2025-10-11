<?php
// ==========================
// /services.php
// ==========================
require_once __DIR__ . '/config.php';
include_once __DIR__ . '/includes/header.php';
?>

<h2 class="text-center mb-4"><?php echo $lang['services']; ?></h2>

<div class="row justify-content-center">
<?php
try {
    $stmt = $pdo->query("SELECT * FROM services ORDER BY id ASC");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($services) {
        foreach ($services as $srv) {
            echo '<div class="col-md-4 col-sm-6 mb-4">';
            echo '<div class="card shadow-sm h-100">';
            echo '<div class="card-body text-center">';
            echo '<h5 class="card-title fw-bold">' . clean($srv['name']) . '</h5>';
            echo '<p class="card-text text-muted">' . clean($srv['duration']) . ' ' . $lang['service_duration'] . '</p>';
            echo '<p class="fw-bold mb-3">' . clean($srv['price']) . '₪</p>';
            echo '<a href="booking.php?service=' . $srv['id'] . '" class="btn btn-primary">';
            echo '<i class="bi bi-calendar-check"></i> ' . $lang['book_now'];
            echo '</a>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<p class="text-center text-muted">(' . $lang['services'] . ' ...)</p>';
    }
} catch (Exception $e) {
    log_debug("Error loading services.php: " . $e->getMessage());
    echo '<p class="text-danger text-center">Error loading services.</p>';
}
?>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
