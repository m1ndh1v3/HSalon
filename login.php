<?php
// ==========================
// /login.php
// ==========================
require_once __DIR__ . '/config.php';
include_once __DIR__ . '/includes/header.php';

if (isset($_SESSION['client_id'])) {
    header("Location: member/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($_POST['email'] ?? '');
    $pass  = clean($_POST['password'] ?? '');
    if ($email && $pass) {
        $stmt = $pdo->prepare("SELECT * FROM clients WHERE email=? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($pass, $user['password'])) {

            // store all needed client info for linking and booking
            $_SESSION['client_id']    = $user['id'];
            $_SESSION['client_name']  = $user['name'];
            $_SESSION['client_email'] = $user['email'];
            $_SESSION['client_phone'] = $user['phone'] ?? ''; // ensure defined

            log_debug("Client logged in: ".$user['email']);

            // redirect to member dashboard
            header("Location: member/bookings.php");
            exit;
        } else {
            echo '<div class="alert alert-danger text-center">'.$lang['login'].' failed</div>';
        }
    }
}
?>
<h2 class="text-center mb-4"><?php echo $lang['login']; ?></h2>
<form method="POST" class="col-md-6 mx-auto card p-4 shadow-sm">
  <div class="mb-3">
    <label class="form-label"><?php echo $lang['email']; ?></label>
    <input type="email" name="email" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label"><?php echo $lang['password']; ?></label>
    <input type="password" name="password" class="form-control" required>
  </div>
  <button type="submit" class="btn btn-primary w-100"><?php echo $lang['login']; ?></button>
  <p class="text-center mt-3"><a href="register.php"><?php echo $lang['register']; ?></a></p>
</form>
<?php include_once __DIR__ . '/includes/footer.php'; ?>
