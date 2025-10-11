<?php
// ==========================
// /register.php
// ==========================
require_once __DIR__ . '/config.php';
include_once __DIR__ . '/includes/header.php';

if (isset($_SESSION['client_id'])) {
    header("Location: member/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = clean($_POST['name'] ?? '');
    $phone = clean($_POST['phone'] ?? '');
    $email = clean($_POST['email'] ?? '');
    $pass  = clean($_POST['password'] ?? '');
    $confirm = clean($_POST['confirm_password'] ?? '');

    if ($name && $email && $pass && $confirm && $pass === $confirm) {
        try {
            $exists = $pdo->prepare("SELECT COUNT(*) FROM clients WHERE email=?");
            $exists->execute([$email]);
            if ($exists->fetchColumn()) {
                echo '<div class="alert alert-warning text-center">'.$lang['email'].' exists</div>';
            } else {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO clients (name, phone, email, password, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $phone, $email, $hash]);
                log_debug("New client registered: ".$email);
                echo '<div class="alert alert-success text-center">'.$lang['register'].' success</div>';
            }
        } catch (Exception $e) {
            log_debug("Register failed: ".$e->getMessage());
            echo '<div class="alert alert-danger text-center">Error registering account</div>';
        }
    } else {
        echo '<div class="alert alert-warning text-center">Check your inputs</div>';
    }
}
?>
<h2 class="text-center mb-4"><?php echo $lang['register']; ?></h2>
<form method="POST" class="col-md-6 mx-auto card p-4 shadow-sm">
  <div class="mb-3">
    <label class="form-label"><?php echo $lang['name']; ?></label>
    <input type="text" name="name" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label"><?php echo $lang['phone']; ?></label>
    <input type="text" name="phone" class="form-control">
  </div>
  <div class="mb-3">
    <label class="form-label"><?php echo $lang['email']; ?></label>
    <input type="email" name="email" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label"><?php echo $lang['password']; ?></label>
    <input type="password" name="password" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label"><?php echo $lang['confirm_password']; ?></label>
    <input type="password" name="confirm_password" class="form-control" required>
  </div>
  <button type="submit" class="btn btn-primary w-100"><?php echo $lang['register']; ?></button>
  <p class="text-center mt-3"><a href="login.php"><?php echo $lang['login']; ?></a></p>
</form>
<?php include_once __DIR__ . '/includes/footer.php'; ?>
