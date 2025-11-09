<?php
// ==========================
// /includes/header.php — unified dynamic header (admin + member + guest)
// ==========================

if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_GET['theme'])) {
    $theme = ($_GET['theme'] === 'dark') ? 'dark' : 'light';
    $_SESSION['theme'] = $theme;
    setcookie('theme', $theme, time() + (86400 * 30), "/");
    header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

if (!isset($_SESSION['theme'])) {
    $_SESSION['theme'] = $_COOKIE['theme'] ?? 'light';
}
$theme = $_SESSION['theme'];
?>
<!DOCTYPE html>
<html lang="<?php echo $lang_code; ?>" dir="<?php echo ($lang_code == 'ar' ? 'rtl' : 'ltr'); ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo SITE_NAME; ?></title>
  <script>
    const SITE_URL = "<?php echo SITE_URL; ?>";
  </script>  
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css?v=<?php echo time(); ?>">
  <link rel="icon" href="<?php echo SITE_URL; ?>/assets/img/hsalon_logo.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="<?php echo SITE_URL; ?>/assets/js/main.js?v=<?php echo time(); ?>"></script>


</head>
<body class="<?php echo $theme; ?> theme-body">

<?php
$unread_count = 0;
if (isset($_SESSION['admin_id'])) {
  try {
      $unread_count = $pdo->query("SELECT COUNT(*) FROM notifications WHERE is_read=0")->fetchColumn();
  } catch (Exception $e) {
      $unread_count = 0;
  }
}
?>

<nav id="mainNavbar" class="navbar navbar-expand-lg border-bottom shadow-sm fixed-top">
  <div class="container-fluid px-3">
    <a class="navbar-brand d-flex align-items-center fw-bold" href="<?php echo SITE_URL; ?>/index.php">
      <img src="<?php echo SITE_URL; ?>/assets/img/hsalon_logo.png" alt="Logo" height="45" class="me-2">
      <span><?php echo SITE_NAME; ?></span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu"
      aria-controls="navbarMenu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarMenu">
      <ul class="navbar-nav mx-auto text-center gap-2 justify-content-center flex-grow-1" style="min-width:0; flex-basis:auto;">      
        <li class="nav-item">
          <li class="nav-item dropdown profile-toggle">
            <a class="nav-link icon-only" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle fs-5"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <?php if (isset($_SESSION['admin_id'])): ?>
                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/dashboard.php">لوحة الإدارة</a></li>
                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/logout.php">تسجيل الخروج</a></li>
                <?php elseif (isset($_SESSION['client_id'])): ?>
                  <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/member/index.php"><?php echo $lang['my_account']; ?></a></li>
                  <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/logout.php"><?php echo $lang['logout']; ?></a></li>
                  <?php else: ?>
                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/login.php"><?php echo $lang['login']; ?></a></li>
                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/register.php"><?php echo $lang['register']; ?></a></li>
                    <?php endif; ?>
                  </ul>
                </li>
              </li>
              <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/index.php"><?php echo $lang['home']; ?></a></li>
              <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/services.php"><?php echo $lang['services']; ?></a></li>
              <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/gallery.php"><?php echo $lang['gallery']; ?></a></li>
              <a href="<?php echo SITE_URL; ?>/booking.php" class="btn btn-primary px-3"><?php echo $lang['book_now']; ?></a>
      </ul>

      <ul class="navbar-nav icon-row flex-row justify-content-center gap-3 mt-3 mt-lg-0 <?php echo ($lang_code === 'ar') ? 'flex-row-reverse' : ''; ?>">
        <li class="nav-item">
          <button id="themeToggle" class="nav-link icon-only" type="button" title="Toggle Theme">
            <i class="bi bi-<?php echo ($theme == 'light') ? 'moon' : 'sun'; ?>"></i>
          </button>
        </li>
        <?php include __DIR__ . '/lang_switch.php'; ?>
        <?php if (isset($_SESSION['admin_id'])): ?>
          <li class="nav-item">
            <a href="<?php echo SITE_URL; ?>/admin/settings.php" class="nav-link icon-only"><i class="bi bi-gear"></i></a>
          </li>
          <li class="nav-item">
            <button id="notifBtn" class="nav-link icon-only position-relative" data-bs-toggle="modal" data-bs-target="#notifModal">
              <i class="bi bi-bell"></i>
              <?php if ($unread_count > 0): ?>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?php echo $unread_count; ?></span>
              <?php endif; ?>
            </button>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>


<main class="container mt-4">


<?php if (isset($_SESSION['admin_id'])): ?>

<!-- ==========================
     Notifications Modal (Admin only) — Final Polished (LTR/RTL + Dark/Light)
========================== -->
<div class="modal fade" id="notifModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered notif-dialog">
    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden notif-modal">

      <div class="modal-header notif-modal-header px-4 py-3 
           <?php echo ($lang_code == 'ar') ? 'rtl-header' : 'ltr-header'; ?>">
        <h5 class="modal-title fw-semibold d-flex align-items-center gap-2 m-0">
          <i class="bi bi-bell-fill text-danger fs-5"></i>
          <span>آخر الإشعارات</span>
        </h5>
        <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-0" id="notifList">
        <div class="text-center py-5 text-muted">جارِ تحميل الإشعارات...</div>
      </div>

      <div class="modal-footer notif-modal-footer d-flex flex-wrap justify-content-between gap-2 px-4 py-3">
        <div class="d-flex gap-2">
          <button id="markAllBtn" class="btn btn-success btn-sm px-3">
            <i class="bi bi-check2-all"></i> تحديد الكل كمقروء
          </button>
          <button id="clearAllBtn" class="btn btn-danger btn-sm px-3">
            <i class="bi bi-x-circle"></i> مسح الكل
          </button>
        </div>
        <a href="<?php echo SITE_URL; ?>/admin/notifications.php" class="btn btn-outline-primary btn-sm px-3">
          عرض جميع الإشعارات
        </a>
      </div>

    </div>
  </div>
</div>
  

<audio id="notifPing" src="<?php echo SITE_URL; ?>/assets/sound/ping-82822.mp3" preload="auto"></audio>

<?php endif; ?>
