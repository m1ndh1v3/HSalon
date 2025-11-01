<?php
// ==========================
// /member/profile.php
// ==========================
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['client_id'])) {
    header("Location: ../login.php");
    exit;
}

include_once __DIR__ . '/../includes/header.php';

$cid = $_SESSION['client_id'];

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = clean($_POST['name'] ?? '');
    $phone = normalize_phone($_POST['phone'] ?? '');
    $email = clean($_POST['email'] ?? '');

    if ($name && $email) {
        $stmt = $pdo->prepare("UPDATE clients SET name=?, phone=?, email=? WHERE id=?");
        $stmt->execute([$name, $phone, $email, $cid]);
        $_SESSION['client_name'] = $name;
        echo '<div class="alert alert-success text-center">'.$lang['update_profile'].'</div>';
        log_debug("Client updated profile: ID=$cid");
    }
}

// Load profile data
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id=?");
$stmt->execute([$cid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<h2 class="text-center mb-4"><?php echo $lang['profile']; ?></h2>

<form method="POST" class="col-md-6 mx-auto card p-4 shadow-sm">
  <div class="mb-3">
    <label class="form-label"><?php echo $lang['name']; ?></label>
    <input type="text" name="name" class="form-control" disabled value="<?php echo clean($user['name']); ?>">
  </div>
  <div class="mb-3">
    <label class="form-label"><?php echo $lang['phone']; ?></label>
    <input type="text" name="phone" class="form-control" value="<?php echo clean($user['phone']); ?>">
  </div>
  <div class="mb-3">
    <label class="form-label"><?php echo $lang['email']; ?></label>
    <input type="email" name="email" class="form-control" required value="<?php echo clean($user['email']); ?>">
  </div>
  <button type="submit" class="btn btn-primary w-100"><?php echo $lang['save_changes']; ?></button>
</form>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
