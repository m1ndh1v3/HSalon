<?php
// ==========================
// /includes/header.php — unified dynamic header (admin + member + guest)
// ==========================

// Start session safely only if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// === Handle theme toggle ===
if (isset($_GET['theme'])) {
    $theme = ($_GET['theme'] === 'dark') ? 'dark' : 'light';
    $_SESSION['theme'] = $theme;
    setcookie('theme', $theme, time() + (86400 * 30), "/"); // store for 30 days
    header("Location: " . strtok($_SERVER['REQUEST_URI'], '?')); // reload without query
    exit;
}

// === Default theme ===
if (!isset($_SESSION['theme'])) {
    if (isset($_COOKIE['theme'])) {
        $_SESSION['theme'] = $_COOKIE['theme'];
    } else {
        $_SESSION['theme'] = 'light';
    }
}
$theme = $_SESSION['theme'];

?>
<!DOCTYPE html>
<html lang="<?php echo $lang_code; ?>" dir="<?php echo ($lang_code == 'ar' ? 'rtl' : 'ltr'); ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo SITE_NAME; ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css?v=<?php echo time(); ?>">
  <link rel="icon" href="<?php echo SITE_URL; ?>/assets/img/hsalon_logo.png">
</head>
<body class="<?php echo ($_SESSION['theme'] ?? 'light'); ?> theme-body">

<?php
// ==========================
// Notifications (admin only)
// ==========================
$unread_count = 0;
if (isset($_SESSION['admin_id'])) {
  try {
      $unread_count = $pdo->query("SELECT COUNT(*) FROM notifications WHERE is_read=0")->fetchColumn();
  } catch (Exception $e) {
      $unread_count = 0;
  }
}
?>

<!-- ==========================
     Main Navbar
========================== -->
<nav class="navbar navbar-expand-lg border-bottom shadow-sm py-2 sticky-top" id="mainNavbar">
  <div class="container-fluid px-3">
    <a class="navbar-brand d-flex align-items-center fw-bold" href="<?php echo SITE_URL; ?>/index.php">
      <img src="<?php echo SITE_URL; ?>/assets/img/hsalon_logo.png" alt="logo" height="45" class="me-2">
      <span><?php echo SITE_NAME; ?></span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-between" id="mainNav">
      <ul class="navbar-nav mx-auto">
        <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/index.php"><?php echo $lang['home']; ?></a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/services.php"><?php echo $lang['services']; ?></a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/gallery.php"><?php echo $lang['gallery']; ?></a></li>
        <li class="nav-item"><a class="btn btn-primary text-white px-3 ms-2" href="<?php echo SITE_URL; ?>/booking.php"><?php echo $lang['book_now']; ?></a></li>
      </ul>

      <ul class="navbar-nav align-items-center gap-2">
        <?php include __DIR__ . '/lang_switch.php'; ?>

        <?php if (isset($_SESSION['admin_id'])): ?>
          <!-- Admin icons -->
          <li class="nav-item">
            <button type="button" class="btn btn-outline-light position-relative" id="notifBtn" data-bs-toggle="modal" data-bs-target="#notifModal">
              <i class="bi bi-bell"></i>
              <?php if ($unread_count > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                  <?php echo $unread_count; ?>
                </span>
              <?php endif; ?>
            </button>
          </li>
          <li class="nav-item">
            <a href="<?php echo SITE_URL; ?>/admin/settings.php" class="btn btn-outline-light">
              <i class="bi bi-gear"></i>
            </a>
          </li>
        <?php endif; ?>

        <!-- Account dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown profile-toggle" href="#" role="button" data-bs-toggle="dropdown">
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

        <!-- Theme toggle -->
        <li class="nav-item">
          <button id="themeToggle" class="nav-link text-reset icon-only" title="Toggle theme" type="button">
            <i class="bi bi-<?php echo ($theme == 'light') ? 'moon' : 'sun'; ?>"></i>
          </button>
        </li>
      </ul>
    </div>
  </div>
</nav>

<main class="container mt-4">

<?php if (isset($_SESSION['admin_id'])): ?>
<!-- ==========================
     Notifications Modal (Admin only)
========================== -->
<div class="modal fade" id="notifModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title"><i class="bi bi-bell"></i> آخر الإشعارات</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0" id="notifList">
        <div class="text-center py-4 text-muted">جارِ تحميل الإشعارات...</div>
      </div>
      <div class="modal-footer flex-wrap justify-content-between gap-2">
        <div class="d-flex gap-2">
          <button id="markAllBtn" class="btn btn-success btn-sm">
            <i class="bi bi-check2-all"></i> تحديد الكل كمقروء
          </button>
          <button id="clearAllBtn" class="btn btn-danger btn-sm">
            <i class="bi bi-x-circle"></i> مسح الكل
          </button>
        </div>
        <a href="<?php echo SITE_URL; ?>/admin/notifications.php" class="btn btn-outline-primary btn-sm">
          عرض جميع الإشعارات
        </a>
      </div>
    </div>
  </div>
</div>

<audio id="notifPing" src="<?php echo SITE_URL; ?>/assets/sound/ping.mp3" preload="auto"></audio>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const notifBtn = document.getElementById('notifBtn');
  const bellIcon = notifBtn.querySelector('.bi-bell');
  const pingSound = document.getElementById('notifPing');
  const notifList = document.getElementById('notifList');
  const markAllBtn = document.getElementById('markAllBtn');
  const clearAllBtn = document.getElementById('clearAllBtn');
  let lastCount = <?php echo (int)$unread_count; ?>;
  const KEY = 'notif_last_seen';

  if (localStorage.getItem(KEY) === null) localStorage.setItem(KEY, String(lastCount));
  const lastSeen = parseInt(localStorage.getItem(KEY) || '0', 10);
  if (lastCount > lastSeen) bellIcon.classList.add('text-warning');

  notifBtn.addEventListener('click', () => {
    const badgeNow = parseInt((notifBtn.querySelector('.badge')?.textContent || '0'), 10);
    localStorage.setItem(KEY, String(badgeNow));
    bellIcon.classList.remove('text-warning');
    loadRecentNotifications();
  });

  const updateBadge = () => {
    fetch("<?php echo SITE_URL; ?>/admin/notifications_count.php")
      .then(res => res.json())
      .then(data => {
        let badge = notifBtn.querySelector('.badge');
        if (!badge && data.count > 0) {
          badge = document.createElement('span');
          badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
          badge.textContent = data.count;
          notifBtn.appendChild(badge);
        } else if (badge) {
          badge.textContent = data.count > 0 ? data.count : '';
          badge.style.display = data.count > 0 ? 'inline' : 'none';
        }
        if (data.count > lastCount) {
          pingSound.currentTime = 0;
          pingSound.play().catch(()=>{});
          bellIcon.classList.add('shake');
          setTimeout(() => bellIcon.classList.remove('shake'), 1200);
        }
        const currentLastSeen = parseInt(localStorage.getItem(KEY) || '0', 10);
        if (data.count > currentLastSeen) bellIcon.classList.add('text-warning');
        else bellIcon.classList.remove('text-warning');
        lastCount = data.count;
      })
      .catch(()=>{});
  };

  const loadRecentNotifications = () => {
    notifList.innerHTML = '<div class="text-center py-4 text-muted">جارِ التحميل...</div>';
    fetch("<?php echo SITE_URL; ?>/admin/notifications_recent.php")
      .then(res => res.text())
      .then(html => notifList.innerHTML = html)
      .catch(() => notifList.innerHTML = '<div class="text-center py-4 text-danger">حدث خطأ أثناء تحميل الإشعارات.</div>');
  };

  markAllBtn.addEventListener('click', () => {
    if (!confirm('هل تريد تحديد جميع الإشعارات كمقروءة؟')) return;
    fetch("<?php echo SITE_URL; ?>/admin/notifications_action.php?action=mark_all")
      .then(() => loadRecentNotifications())
      .then(() => updateBadge());
  });
  clearAllBtn.addEventListener('click', () => {
    if (!confirm('هل أنت متأكد أنك تريد مسح جميع الإشعارات؟')) return;
    fetch("<?php echo SITE_URL; ?>/admin/notifications_action.php?action=clear")
      .then(() => loadRecentNotifications())
      .then(() => updateBadge());
  });
  updateBadge();
  setInterval(updateBadge, 20000);
});
const style = document.createElement('style');
style.textContent = `@keyframes shakeAnim{0%,100%{transform:rotate(0);}20%{transform:rotate(-15deg);}40%{transform:rotate(10deg);}60%{transform:rotate(-10deg);}80%{transform:rotate(5deg);}}.shake{animation:shakeAnim .6s ease;}`;
document.head.appendChild(style);
</script>

<?php endif; ?>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const toggleBtn = document.getElementById("themeToggle");
  const body = document.body;
  const icon = toggleBtn.querySelector("i");

  // Load from localStorage if available
  const savedTheme = localStorage.getItem("theme");
  if (savedTheme && savedTheme !== body.className) {
    body.className = savedTheme;
    icon.className = `bi bi-${savedTheme === "light" ? "moon" : "sun"}`;
  }

  toggleBtn.addEventListener("click", () => {
    const current = body.classList.contains("dark") ? "dark" : "light";
    const next = current === "light" ? "dark" : "light";
    body.classList.remove(current);
    body.classList.add(next);
    icon.className = `bi bi-${next === "light" ? "moon" : "sun"}`;
    localStorage.setItem("theme", next);

    // Sync with PHP session & cookie silently
    fetch(`?theme=${next}`).catch(()=>{});
  });
});
</script>