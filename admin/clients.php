<?php
// ==========================
// /admin/clients.php (Admin dashboard - Manage Clients)
// ==========================
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}
include_once __DIR__ . '/../includes/header.php';

// === Handle add/edit/delete ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = clean($_POST['name'] ?? '');
    $phone = clean($_POST['phone'] ?? '');
    $email = clean($_POST['email'] ?? '');

    if ($action === 'add' && $name && $email) {
        $stmt = $pdo->prepare("INSERT INTO clients (name, phone, email) VALUES (?, ?, ?)");
        $stmt->execute([$name, $phone, $email]);
        log_debug("Admin added client: $name <$email>");
    }
    if ($action === 'edit' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("UPDATE clients SET name=?, phone=?, email=? WHERE id=?");
        $stmt->execute([$name, $phone, $email, $id]);
        log_debug("Admin edited client ID=$id");
    }
}
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM clients WHERE id=?")->execute([$id]);
    log_debug("Admin deleted client ID=$id");
}

// === Search & Pagination ===
$search = trim($_GET['search'] ?? '');
$where = "WHERE 1";
$params = [];
if ($search !== '') {
    $where .= " AND (name LIKE ? OR phone LIKE ? OR email LIKE ?)";
    $like = "%$search%";
    $params = [$like, $like, $like];
}

$perPage = 10;
$page = max(1, intval($_GET['page'] ?? 1));
$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM clients $where");
$totalStmt->execute($params);
$totalRows = $totalStmt->fetchColumn();
$totalPages = max(1, ceil($totalRows / $perPage));
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("SELECT * FROM clients $where ORDER BY id DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="text-center mb-4">إدارة الزبائن</h2>

<form method="get" class="row mb-3 text-end" dir="rtl">
  <div class="col-md-4 mb-2">
    <label class="form-label">بحث</label>
    <input type="text" name="search" value="<?= clean($search) ?>" class="form-control text-end" placeholder="ابحث بالاسم أو الهاتف أو البريد الإلكتروني">
  </div>
  <div class="col-md-2 mb-2 align-self-end">
    <button type="submit" class="btn btn-primary w-100">بحث</button>
  </div>
  <div class="col-md-2 mb-2 align-self-end">
    <a href="clients.php" class="btn btn-secondary w-100">إعادة</a>
  </div>
  <div class="col-md-4 mb-2 align-self-end text-start">
    <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#addModal">
      <i class="bi bi-person-plus"></i> إضافة زبون جديد
    </button>
  </div>
</form>

<div class="table-responsive">
<table class="table table-bordered table-striped text-center align-middle" dir="rtl">
  <thead class="table-light">
    <tr>
      <th>ID</th>
      <th>الاسم</th>
      <th>الهاتف</th>
      <th>البريد الإلكتروني</th>
      <th>تاريخ التسجيل</th>
      <th>الإجراءات</th>
    </tr>
  </thead>
  <tbody>
  <?php if ($rows): ?>
    <?php foreach ($rows as $r): ?>
      <?php
        $bookingCount = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE client_id=?");
        $bookingCount->execute([$r['id']]);
        $count = $bookingCount->fetchColumn();
      ?>
      <tr>
        <td><?= $r['id'] ?></td>
        <td><?= clean($r['name']) ?></td>
        <td>
          <?php if ($r['phone']): ?>
            <a href="<?= send_whatsapp_message($r['phone'], 'مرحباً، كيف يمكنني مساعدتك؟') ?>" target="_blank" class="text-success">
              <i class="bi bi-whatsapp"></i> <?= clean($r['phone']) ?>
            </a>
          <?php else: ?>
            <span class="text-muted">—</span>
          <?php endif; ?>
        </td>
        <td>
          <a href="mailto:<?= clean($r['email']) ?>" class="text-decoration-none">
            <i class="bi bi-envelope"></i> <?= clean($r['email']) ?>
          </a>
        </td>
        <td><?= format_date($r['created_at'], true) ?></td>
        <td>
          <a href="bookings.php?client_id=<?= $r['id'] ?>" class="btn btn-info btn-sm text-white">
            <i class="bi bi-calendar-event"></i> (<?= $count ?>) المواعيد
          </a>
          <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $r['id'] ?>">
            <i class="bi bi-pencil"></i>
          </button>
          <a href="?delete=<?= $r['id'] ?>" onclick="return confirm('هل أنت متأكد من حذف هذا الزبون؟');" class="btn btn-danger btn-sm">
            <i class="bi bi-trash"></i>
          </a>
        </td>
      </tr>

      <!-- Edit Modal -->
      <div class="modal fade" id="editModal<?= $r['id'] ?>" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content text-end" dir="rtl">
            <div class="modal-header">
              <h5 class="modal-title">تعديل الزبون</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
              <input type="hidden" name="action" value="edit">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <div class="modal-body">
                <div class="mb-3">
                  <label class="form-label">الاسم</label>
                  <input type="text" name="name" class="form-control text-end" value="<?= clean($r['name']) ?>" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">الهاتف</label>
                  <input type="text" name="phone" class="form-control text-end" value="<?= clean($r['phone']) ?>">
                </div>
                <div class="mb-3">
                  <label class="form-label">البريد الإلكتروني</label>
                  <input type="email" name="email" class="form-control text-end" value="<?= clean($r['email']) ?>" required>
                </div>
              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-primary">حفظ</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <tr><td colspan="6" class="text-muted">لا يوجد عملاء حالياً.</td></tr>
  <?php endif; ?>
  </tbody>
</table>
</div>

<?php if ($totalPages > 1): ?>
<nav aria-label="Clients pagination">
  <ul class="pagination justify-content-center mt-4" dir="rtl">
    <?php
      $qs = $_GET;
      $prev = $page - 1;
      $next = $page + 1;
    ?>
    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
      <?php $qs['page'] = $prev; ?>
      <a class="page-link" href="?<?= http_build_query($qs); ?>">السابق</a>
    </li>

    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <?php $qs['page'] = $i; ?>
      <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
        <a class="page-link" href="?<?= http_build_query($qs); ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>

    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
      <?php $qs['page'] = $next; ?>
      <a class="page-link" href="?<?= http_build_query($qs); ?>">التالي</a>
    </li>
  </ul>
</nav>
<?php endif; ?>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content text-end" dir="rtl">
      <div class="modal-header">
        <h5 class="modal-title">إضافة زبون جديد</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <input type="hidden" name="action" value="add">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">الاسم</label>
            <input type="text" name="name" class="form-control text-end" required>
          </div>
          <div class="mb-3">
            <label class="form-label">الهاتف</label>
            <input type="text" name="phone" class="form-control text-end">
          </div>
          <div class="mb-3">
            <label class="form-label">البريد الإلكتروني</label>
            <input type="email" name="email" class="form-control text-end" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">إضافة</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
