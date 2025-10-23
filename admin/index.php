<?php
// ==========================
// /admin/index.php (Admin Login) — fixed redirect handling
// ==========================
require_once __DIR__ . '/../config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Redirect if already logged in (before output)
if (!empty($_SESSION['admin_id'])) {
    header("Location: bookings.php");
    exit;
}

// Handle login submit (before output)
$login_error = '';
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
            $login_error = 'فشل تسجيل الدخول، يرجى التحقق من البيانات.';
        }
    } else {
        $login_error = 'يرجى إدخال البريد الإلكتروني وكلمة المرور.';
    }
}

include_once __DIR__ . '/../includes/header.php';
?>

<h2 class="text-center mb-4">تسجيل دخول المدير</h2>

<?php if ($login_error): ?>
  <div class="alert alert-danger text-center"><?php echo $login_error; ?></div>
<?php endif; ?>

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
