<?php
// ==========================
// /includes/navbar.php
// ==========================
?>
<nav class="navbar navbar-expand-lg navbar-light bg-light px-3 border-bottom">
  <a class="navbar-brand fw-bold" href="<?php echo SITE_URL; ?>/index.php">
    <img src="<?php echo SITE_URL; ?>/assets/img/hsalon_logo.png" alt="logo" height="120" class="me-2">
    <?php echo SITE_NAME; ?>
  </a>
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="mainNav">
    <ul class="navbar-nav <?php echo ($lang_code == 'ar' ? 'ms-auto' : 'me-auto'); ?>">
      <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/index.php"><?php echo $lang['home']; ?></a></li>
      <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/services.php"><?php echo $lang['services']; ?></a></li>
      <li class="nav-item"><a class="nav-link" href="<?php echo SITE_URL; ?>/gallery.php"><?php echo $lang['gallery']; ?></a></li>
      <li class="nav-item"><a class="nav-link btn btn-primary text-white px-3" href="<?php echo SITE_URL; ?>/booking.php"><?php echo $lang['book_now']; ?></a></li>
    </ul>

    <ul class="navbar-nav ms-auto">
      <?php include __DIR__ . '/lang_switch.php'; ?>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
          <i class="bi bi-person-circle"></i>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <?php if (isset($_SESSION['client_id'])): ?>
            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/member/index.php"><?php echo $lang['my_account']; ?></a></li>
            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/logout.php"><?php echo $lang['logout']; ?></a></li>
          <?php else: ?>
            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/login.php"><?php echo $lang['login']; ?></a></li>
            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/register.php"><?php echo $lang['register']; ?></a></li>
          <?php endif; ?>
        </ul>
      </li>
      <li class="nav-item">
        <a href="?theme=<?php echo (($_SESSION['theme'] ?? 'light') == 'light') ? 'dark' : 'light'; ?>" class="nav-link" title="Toggle theme">
          <i class="bi bi-<?php echo (($_SESSION['theme'] ?? 'light') == 'light') ? 'moon' : 'sun'; ?>"></i>
        </a>
      </li>
    </ul>
  </div>
</nav>
