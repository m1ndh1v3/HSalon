<?php
// ==========================
// /admin/index.php (Admin Login)
// ==========================
require_once __DIR__ . '/../config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// If already logged in, go to bookings
if (!empty($_SESSION['admin_id'])) {
    header("Location: bookings.php");
    exit;
}

include_once __DIR__ . '/../includes/header.php';

// Handle login submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($_POST['email'] ?? '');
    $pass  = clean($_POST['password'] ?? '');

    if ($email && $pass) {
        $stmt = $pdo->prepare("SELECT id, name, email, password FROM admins WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($pass, $admin['password'])) {
            $_SESSION['admin_id']    = $admin['id'];
            $_SESSION['admin_name']  = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];

            log_debug("Admin logged in: ".$admin['email']);
            header("Location: bookings.php");
            exit;
        } else {
            echo '<div class="alert alert-danger text-center">فشل تسجيل الدخول، يرجى التحقق من البيانات.</div>';
        }
    } else {
        echo '<div class="alert alert-warning text-center">يرجى إدخال البريد الإلكتروني وكلمة المرور.</div>';
    }
}
?>

<h2 class="text-center mb-4">تسجيل دخول المدير</h2>

<form method="POST" class="col-md-6 mx-auto card p-4 shadow-sm text-end" dir="rtl">
  <div class="mb-3">
    <label class="form-label">البريد الإلكتروني</label>
    <input type="email" name="email" class="form-control text-end" required>
  </div>
  <div class="mb-3">
    <label class="form-label">كلمة المرور</label>
    <input type="password" name="password" class="form-control text-end" required>
  </div>
  <button type="submit" class="btn btn-primary w-100">تسجيل الدخول</button>
</form>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
