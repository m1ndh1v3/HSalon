<?php
// ==========================
// /admin/schedule.php
// ==========================
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}
include_once __DIR__ . '/../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $open  = clean($_POST['open'] ?? '');
    $close = clean($_POST['close'] ?? '');
    $stmt = $pdo->prepare("UPDATE settings SET open_hour=?, close_hour=? WHERE id=1");
    $stmt->execute([$open, $close]);
    log_debug("Admin updated schedule: $open - $close");
}
$settings = $pdo->query("SELECT open_hour, close_hour FROM settings WHERE id=1")->fetch(PDO::FETCH_ASSOC);
?>

<h2 class="text-center mb-4"><?php echo $lang['manage_schedule']; ?></h2>

<form method="POST" class="col-md-6 mx-auto card p-4 shadow-sm">
  <div class="row mb-3">
    <div class="col-md-6">
      <label class="form-label">Open</label>
      <input type="time" name="open" class="form-control" value="<?php echo $settings['open_hour']; ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Close</label>
      <input type="time" name="close" class="form-control" value="<?php echo $settings['close_hour']; ?>">
    </div>
  </div>
  <button type="submit" class="btn btn-primary w-100"><?php echo $lang['save_changes']; ?></button>
</form>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
