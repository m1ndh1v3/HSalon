<?php
// ==========================
// /admin/profile.php (Admin profile & preferences)
// ==========================
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['admin_id'])) {
  header("Location: index.php");
  exit;
}
include_once __DIR__ . '/../includes/header.php';

// === Fetch admin data ===
$stmt = $pdo->prepare("SELECT id, name, email, password FROM admins WHERE id=? LIMIT 1");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// === Handle updates ===
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  if ($action === 'update_info') {
    $name = clean($_POST['name'] ?? '');
    $email = clean($_POST['email'] ?? '');
    if ($name && $email) {
      $stmt = $pdo->prepare("UPDATE admins SET name=?, email=? WHERE id=?");
      $stmt->execute([$name, $email, $_SESSION['admin_id']]);
      $_SESSION['admin_name'] = $name;
      $_SESSION['admin_email'] = $email;
      $message = '<div class="alert alert-success text-center">تم تحديث المعلومات بنجاح.</div>';
      log_debug("Admin updated profile info");
    }
  }

  if ($action === 'change_pass') {
    $old = $_POST['old_pass'] ?? '';
    $new = $_POST['new_pass'] ?? '';
    $confirm = $_POST['confirm_pass'] ?? '';
    if ($old && $new && $confirm) {
      if (!password_verify($old, $admin['password'])) {
        $message = '<div class="alert alert-danger text-center">كلمة المرور القديمة غير صحيحة.</div>';
      } elseif ($new !== $confirm) {
        $message = '<div class="alert alert-warning text-center">كلمتا المرور غير متطابقتين.</div>';
      } else {
        $hash = password_hash($new, PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE admins SET password=? WHERE id=?")->execute([$hash, $_SESSION['admin_id']]);
        $message = '<div class="alert alert-success text-center">تم تحديث كلمة المرور بنجاح.</div>';
        log_debug("Admin changed password");
      }
    }
  }

  if ($action === 'toggle_theme') {
    $_SESSION['theme'] = ($_SESSION['theme'] ?? 'light') === 'light' ? 'dark' : 'light';
    $message = '<div class="alert alert-info text-center">تم تبديل المظهر.</div>';
  }
}
?>

<h2 class="text-center mb-4">الملف الشخصي</h2>

<?= $message ?>

<div class="col-md-6 mx-auto" dir="rtl">

  <!-- Profile info -->
  <div class="card mb-4 shadow-sm">
    <div class="card-header bg-primary text-white text-center">المعلومات الشخصية</div>
    <div class="card-body">
      <form method="POST">
        <input type="hidden" name="action" value="update_info">
        <div class="mb-3">
          <label class="form-label">الاسم</label>
          <input type="text" name="name" class="form-control text-end" value="<?= clean($admin['name']) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">البريد الإلكتروني</label>
          <input type="email" name="email" class="form-control text-end" value="<?= clean($admin['email']) ?>" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">حفظ التغييرات</button>
      </form>
    </div>
  </div>

  <!-- Password change -->
  <div class="card mb-4 shadow-sm">
    <div class="card-header bg-warning text-dark text-center">تغيير كلمة المرور</div>
    <div class="card-body">
      <form method="POST">
        <input type="hidden" name="action" value="change_pass">
        <div class="mb-3">
          <label class="form-label">كلمة المرور الحالية</label>
          <input type="password" name="old_pass" class="form-control text-end" required>
        </div>
        <div class="mb-3">
          <label class="form-label">كلمة المرور الجديدة</label>
          <input type="password" name="new_pass" class="form-control text-end" required>
        </div>
        <div class="mb-3">
          <label class="form-label">تأكيد كلمة المرور</label>
          <input type="password" name="confirm_pass" class="form-control text-end" required>
        </div>
        <button type="submit" class="btn btn-warning w-100">تحديث كلمة المرور</button>
      </form>
    </div>
  </div>

  <!-- Theme toggle -->
  <div class="card mb-4 shadow-sm">
    <div class="card-header bg-dark text-white text-center">مظهر الواجهة</div>
    <div class="card-body text-center">
      <form method="POST">
        <input type="hidden" name="action" value="toggle_theme">
        <p>الوضع الحالي: <strong><?= ($_SESSION['theme'] ?? 'light') === 'light' ? 'فاتح' : 'داكن' ?></strong></p>
        <button type="submit" class="btn btn-secondary">تبديل المظهر</button>
      </form>
    </div>
  </div>

</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
