<?php
// ==========================
// /admin/services.php (Enhanced admin CRUD + toggle + modals + pagination + search)
// ==========================
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}
include_once __DIR__ . '/../includes/header.php';

// === Ensure "status" column exists ===
$checkCol = $pdo->query("SHOW COLUMNS FROM services LIKE 'status'")->fetch();
if (!$checkCol) {
    $pdo->exec("ALTER TABLE services ADD COLUMN status ENUM('active','inactive') DEFAULT 'active'");
    log_debug("Column 'status' added to services table");
}

// === Handle Add / Edit / Delete / Toggle ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = intval($_POST['id'] ?? 0);
    $name = clean($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $duration = intval($_POST['duration'] ?? 0);
    $status = $_POST['status'] ?? 'active';

    if ($action === 'add' && $name && $price && $duration) {
        $stmt = $pdo->prepare("INSERT INTO services (name, price, duration, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $price, $duration, $status]);
        log_debug("Admin added service: $name");
    }
    if ($action === 'edit' && $id) {
        $stmt = $pdo->prepare("UPDATE services SET name=?, price=?, duration=?, status=? WHERE id=?");
        $stmt->execute([$name, $price, $duration, $status, $id]);
        log_debug("Admin edited service ID=$id");
    }
}
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM services WHERE id=?")->execute([$id]);
    log_debug("Admin deleted service ID=$id");
}
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $pdo->prepare("UPDATE services SET status = IF(status='active','inactive','active') WHERE id=?")->execute([$id]);
    log_debug("Admin toggled service status ID=$id");
}

// === Filtering & Search ===
$search = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? '';
$query = "SELECT * FROM services WHERE 1";
$params = [];
if ($statusFilter && in_array($statusFilter, ['active','inactive'])) {
    $query .= " AND status = ?";
    $params[] = $statusFilter;
}
if ($search !== '') {
    $query .= " AND (name LIKE ? OR price LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
}
$query .= " ORDER BY id DESC";

// === Pagination ===
$perPage = 10;
$page = max(1, intval($_GET['page'] ?? 1));
$totalQuery = "SELECT COUNT(*) FROM services WHERE 1";
$totalParams = [];
if ($statusFilter && in_array($statusFilter, ['active','inactive'])) {
    $totalQuery .= " AND status = ?";
    $totalParams[] = $statusFilter;
}
if ($search !== '') {
    $totalQuery .= " AND (name LIKE ? OR price LIKE ?)";
    $like = "%$search%";
    $totalParams[] = $like;
    $totalParams[] = $like;
}
$totalStmt = $pdo->prepare($totalQuery);
$totalStmt->execute($totalParams);
$totalRows = $totalStmt->fetchColumn();
$totalPages = max(1, ceil($totalRows / $perPage));
$offset = ($page - 1) * $perPage;
$query .= " LIMIT $perPage OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="text-center mb-4">إدارة الخدمات</h2>

<!-- Add Service Button -->
<div class="text-end mb-3">
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addServiceModal">
    <i class="bi bi-plus-circle"></i> إضافة خدمة جديدة
  </button>
</div>

<!-- Filters -->
<form method="get" class="row mb-4 text-end" dir="rtl">
  <div class="col-md-4 mb-2">
    <label class="form-label">تصفية حسب الحالة</label>
    <select name="status" class="form-select text-end">
      <option value="">كل الحالات</option>
      <option value="active" <?php if($statusFilter=='active') echo 'selected'; ?>>نشطة</option>
      <option value="inactive" <?php if($statusFilter=='inactive') echo 'selected'; ?>>غير نشطة</option>
    </select>
  </div>
  <div class="col-md-6 mb-2">
    <label class="form-label">بحث</label>
    <input type="text" name="search" class="form-control text-end" placeholder="ابحث بالاسم أو السعر" value="<?= clean($search) ?>">
  </div>
  <div class="col-md-2 mb-2 align-self-end">
    <button type="submit" class="btn btn-primary w-100">تصفية</button>
  </div>
</form>

<!-- Services Table -->
<div class="table-responsive">
<table class="table table-bordered table-striped text-center align-middle" dir="rtl">
  <thead class="table-light">
    <tr>
      <th>ID</th>
      <th>اسم الخدمة</th>
      <th>السعر</th>
      <th>المدة (دقيقة)</th>
      <th>الحالة</th>
      <th>تم الإنشاء</th>
      <th>الإجراءات</th>
    </tr>
  </thead>
  <tbody>
  <?php if ($rows): foreach ($rows as $r): ?>
    <tr>
      <td><?= $r['id'] ?></td>
      <td><?= clean($r['name']) ?></td>
      <td><?= clean($r['price']) ?> ₪</td>
      <td><?= clean($r['duration']) ?></td>
      <td>
        <a href="?toggle=<?= $r['id'] ?>" class="badge bg-<?= $r['status']=='active'?'success':'secondary' ?>">
          <?= $r['status']=='active'?'نشطة':'غير نشطة' ?>
        </a>
      </td>
      <td><?= format_date($r['created_at'], true) ?></td>
      <td>
        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editService<?= $r['id'] ?>">تعديل</button>
        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteService<?= $r['id'] ?>">حذف</button>
      </td>
    </tr>

    <!-- Edit Modal -->
    <div class="modal fade" id="editService<?= $r['id'] ?>" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <form method="POST">
            <div class="modal-header bg-warning">
              <h5 class="modal-title">تعديل الخدمة</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-end">
              <input type="hidden" name="action" value="edit">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <div class="mb-3"><label class="form-label">اسم الخدمة</label><input type="text" name="name" class="form-control text-end" value="<?= clean($r['name']) ?>" required></div>
              <div class="mb-3"><label class="form-label">السعر</label><input type="number" step="0.1" name="price" class="form-control text-end" value="<?= clean($r['price']) ?>" required></div>
              <div class="mb-3"><label class="form-label">المدة (دقيقة)</label><input type="number" name="duration" class="form-control text-end" value="<?= clean($r['duration']) ?>" required></div>
              <div class="mb-3">
                <label class="form-label">الحالة</label>
                <select name="status" class="form-select text-end">
                  <option value="active" <?= $r['status']=='active'?'selected':'' ?>>نشطة</option>
                  <option value="inactive" <?= $r['status']=='inactive'?'selected':'' ?>>غير نشطة</option>
                </select>
              </div>
            </div>
            <div class="modal-footer justify-content-center">
              <button type="submit" class="btn btn-warning">حفظ التعديلات</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteService<?= $r['id'] ?>" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title">تأكيد الحذف</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body text-center">
            هل أنت متأكد أنك تريد حذف الخدمة "<strong><?= clean($r['name']) ?></strong>"؟
          </div>
          <div class="modal-footer justify-content-center">
            <a href="?delete=<?= $r['id'] ?>" class="btn btn-danger">نعم</a>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
          </div>
        </div>
      </div>
    </div>

  <?php endforeach; else: ?>
    <tr><td colspan="7" class="text-muted">لا توجد خدمات مسجلة.</td></tr>
  <?php endif; ?>
  </tbody>
</table>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">إضافة خدمة جديدة</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-end">
          <input type="hidden" name="action" value="add">
          <div class="mb-3"><label class="form-label">اسم الخدمة</label><input type="text" name="name" class="form-control text-end" required></div>
          <div class="mb-3"><label class="form-label">السعر</label><input type="number" step="0.1" name="price" class="form-control text-end" required></div>
          <div class="mb-3"><label class="form-label">المدة (دقيقة)</label><input type="number" name="duration" class="form-control text-end" required></div>
          <div class="mb-3">
            <label class="form-label">الحالة</label>
            <select name="status" class="form-select text-end">
              <option value="active" selected>نشطة</option>
              <option value="inactive">غير نشطة</option>
            </select>
          </div>
        </div>
        <div class="modal-footer justify-content-center">
          <button type="submit" class="btn btn-primary">إضافة</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<nav aria-label="Services pagination">
  <ul class="pagination justify-content-center mt-4" dir="rtl">
    <?php
      $qs = $_GET;
      $prev = $page - 1;
      $next = $page + 1;
    ?>
    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
      <?php $qs['page'] = $prev; ?>
      <a class="page-link" href="?<?php echo http_build_query($qs); ?>">السابق</a>
    </li>
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <?php $qs['page'] = $i; ?>
      <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
        <a class="page-link" href="?<?php echo http_build_query($qs); ?>"><?php echo $i; ?></a>
      </li>
    <?php endfor; ?>
    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
      <?php $qs['page'] = $next; ?>
      <a class="page-link" href="?<?php echo http_build_query($qs); ?>">التالي</a>
    </li>
  </ul>
</nav>
<?php endif; ?>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
