<?php
// ==========================
// /admin/bookings.php — unified with view navigation toggle + runtime expired handling
// ==========================
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $pdo->prepare("UPDATE bookings SET status='approved' WHERE id=?")->execute([$id]);
    add_notification('booking', "تمت الموافقة على الموعد رقم #$id");
    header("Location: bookings.php?msg=approved");
    exit;
}

if (isset($_GET['cancel'])) {
    $id = intval($_GET['cancel']);
    $pdo->prepare("UPDATE bookings SET status='cancelled' WHERE id=?")->execute([$id]);
    add_notification('booking', "تم إلغاء الموعد رقم #$id");
    header("Location: bookings.php?msg=cancelled");
    exit;
}

include_once __DIR__ . '/../includes/header.php';

$count_all       = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$count_approved  = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status='approved'")->fetchColumn();
$count_pending   = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status='pending'")->fetchColumn();
$count_cancelled = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status='cancelled'")->fetchColumn();

// runtime expired count (no schema change)
$count_expired_stmt = $pdo->query("
    SELECT COUNT(*) FROM bookings
    WHERE status='approved'
      AND (date < CURDATE() OR (date = CURDATE() AND time < CURTIME()))
");
$count_expired = $count_expired_stmt->fetchColumn();

$statusFilter = $_GET['status'] ?? '';
$dateFilter   = $_GET['date']   ?? '';
$search       = trim($_GET['search'] ?? '');
$clientId     = intval($_GET['client_id'] ?? 0);

$allowedSort = ['id','client_name','service_name','date','created_at','status'];
$sort = $_GET['sort'] ?? 'created_at';
$sort = in_array($sort, $allowedSort) ? $sort : 'created_at';
$order = ($_GET['order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

$query = "SELECT b.*, c.name AS client_name, s.name AS service_name
          FROM bookings b
          LEFT JOIN clients c ON b.client_id=c.id
          LEFT JOIN services s ON b.service_id=s.id
          WHERE 1";
$params = [];

if ($clientId > 0) {
    $query .= " AND b.client_id = ?";
    $params[] = $clientId;
}

// status filter with special handling for "expired"
if ($statusFilter) {
    if (in_array($statusFilter, ['pending','approved','cancelled'])) {
        $query .= " AND b.status = ?";
        $params[] = $statusFilter;
    } elseif ($statusFilter === 'expired') {
        $query .= " AND b.status='approved' AND (b.date < CURDATE() OR (b.date = CURDATE() AND b.time < CURTIME()))";
    }
}

if ($dateFilter) {
    $query .= " AND b.date = ?";
    $params[] = $dateFilter;
}
if ($search !== '') {
    $query .= " AND (c.name LIKE ? OR s.name LIKE ? OR b.id LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
$query .= " ORDER BY $sort $order";

$perPage = 10;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$totalQuery = "SELECT COUNT(*) FROM bookings b
               LEFT JOIN clients c ON b.client_id=c.id
               LEFT JOIN services s ON b.service_id=s.id
               WHERE 1";
$totalParams = [];

if ($clientId > 0) {
    $totalQuery .= " AND b.client_id = ?";
    $totalParams[] = $clientId;
}

if ($statusFilter) {
    if (in_array($statusFilter, ['pending','approved','cancelled'])) {
        $totalQuery .= " AND b.status = ?";
        $totalParams[] = $statusFilter;
    } elseif ($statusFilter === 'expired') {
        $totalQuery .= " AND b.status='approved' AND (b.date < CURDATE() OR (b.date = CURDATE() AND b.time < CURTIME()))";
    }
}

if ($dateFilter) {
    $totalQuery .= " AND b.date = ?";
    $totalParams[] = $dateFilter;
}
if ($search !== '') {
    $totalQuery .= " AND (c.name LIKE ? OR s.name LIKE ? OR b.id LIKE ?)";
    $like = "%$search%";
    $totalParams[] = $like;
    $totalParams[] = $like;
    $totalParams[] = $like;
}

$totalStmt = $pdo->prepare($totalQuery);
$totalStmt->execute($totalParams);
$totalRows = $totalStmt->fetchColumn();
$totalPages = max(1, ceil($totalRows / $perPage));

$query .= " LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

function sort_link($column, $label, $currentSort, $currentOrder) {
    $newOrder = ($currentSort === $column && $currentOrder === 'asc') ? 'desc' : 'asc';
    $arrow = ($currentSort === $column) ? ($currentOrder === 'asc' ? '↑' : '↓') : '';
    $qs = $_GET;
    $qs['sort'] = $column;
    $qs['order'] = $newOrder;
    $queryString = http_build_query($qs);
    return "<a href=\"?{$queryString}\" class=\"text-decoration-none\">{$label} {$arrow}</a>";
}
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex gap-2">
      <a href="bookings.php" class="btn btn-primary" data-bs-toggle="tooltip" title="عرض الجدول"><i class="bi bi-clipboard-data"></i></a>
      <a href="calendar.php" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="العرض الأسبوعي"><i class="bi bi-calendar-week"></i></a>
      <a href="calendar_month.php" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="العرض الشهري"><i class="bi bi-calendar-month"></i></a>
    </div>
    <h4 class="fw-bold m-0">إدارة المواعيد</h4>
  </div>

  <div class="row mb-4 text-center">
    <div class="col-md-2 mb-2"><span class="badge bg-secondary fs-6">إجمالي: <?= $count_all ?></span></div>
    <div class="col-md-2 mb-2"><span class="badge bg-success fs-6">تمت الموافقة: <?= $count_approved ?></span></div>
    <div class="col-md-2 mb-2"><span class="badge bg-warning text-dark fs-6">معلقة: <?= $count_pending ?></span></div>
    <div class="col-md-2 mb-2"><span class="badge bg-danger fs-6">ملغاة: <?= $count_cancelled ?></span></div>
    <div class="col-md-2 mb-2"><span class="badge bg-secondary fs-6">منتهية: <?= $count_expired ?></span></div>
  </div>

<?php if ($clientId > 0): ?>
  <div class="alert alert-info text-center" dir="rtl">
    عرض المواعيد الخاصة بالزبون رقم <strong><?= $clientId ?></strong>
    <a href="bookings.php" class="btn btn-sm btn-outline-secondary ms-2">عرض الكل</a>
  </div>
<?php endif; ?>

<form method="get" class="row mb-3 text-end" dir="rtl">
  <div class="col-md-3 mb-2">
    <label class="form-label">ترتيب حسب الحالة</label>
    <select name="status" class="form-select text-end">
      <option value="">كل الحالات</option>
      <option value="pending"   <?php if($statusFilter=='pending')   echo 'selected'; ?>>معلقة</option>
      <option value="approved"  <?php if($statusFilter=='approved')  echo 'selected'; ?>>تمت الموافقة</option>
      <option value="cancelled" <?php if($statusFilter=='cancelled') echo 'selected'; ?>>تم الإلغاء</option>
      <option value="expired"   <?php if($statusFilter=='expired')   echo 'selected'; ?>>منتهية</option>
    </select>
  </div>
  <div class="col-md-3 mb-2">
    <label class="form-label">ترتيب حسب التاريخ</label>
    <input type="date" name="date" value="<?php echo clean($dateFilter); ?>" class="form-control text-end">
  </div>
  <div class="col-md-3 mb-2">
    <label class="form-label">بحث</label>
    <input type="text" name="search" value="<?php echo clean($search); ?>" class="form-control text-end" placeholder="ابحث بالاسم أو الخدمة أو رقم الموعد">
  </div>
  <div class="col-md-2 mb-2 align-self-end">
    <button type="submit" class="btn btn-primary w-100">ترتيب</button>
  </div>
  <div class="col-md-1 mb-2 align-self-end">
    <a href="bookings.php" class="btn btn-secondary w-100">إعادة</a>
  </div>
</form>

<?php
$tableThemeClass = (($_SESSION['theme'] ?? 'light') === 'dark')
    ? 'table-dark table-striped-columns'
    : 'table-light';
?>

<div class="table-responsive">
<table class="table table-bordered table-striped text-center align-middle" dir="rtl">
  <thead class="<?= $tableThemeClass ?>">
    <tr>
      <th><?= sort_link('id','ID',$sort,$order) ?></th>
      <th><?= sort_link('client_name','اسم الزبون',$sort,$order) ?></th>
      <th><?= sort_link('service_name','الخدمة',$sort,$order) ?></th>
      <th><?= sort_link('date','تاريخ ووقت الموعد',$sort,$order) ?></th>
      <th><?= sort_link('created_at','تاريخ الموعد',$sort,$order) ?></th>
      <th><?= sort_link('status','الحالة',$sort,$order) ?></th>
      <th>الإجراءات</th>
    </tr>
  </thead>
  <tbody>
  <?php if ($rows): ?>
    <?php foreach ($rows as $r): ?>
      <?php
        $isExpired = ($r['status'] === 'approved') && (strtotime($r['date'].' '.$r['time']) < time());
        $statusLabel = $isExpired
          ? '<span class="badge bg-secondary">منتهي</span>'
          : match($r['status']) {
              'approved'  => '<span class="badge bg-success">تمت الموافقة</span>',
              'cancelled' => '<span class="badge bg-danger">تم الإلغاء</span>',
              default     => '<span class="badge bg-warning text-dark">معلقة</span>',
            };
      ?>
      <tr class="<?= $isExpired ? 'table-secondary' : '' ?>">
        <td><?= $r['id'] ?></td>
        <td><?= clean($r['client_name'] ?? $r['name']) ?></td>
        <td><?= clean($r['service_name']) ?></td>
        <td><?= clean(format_date($r['date'], false) . ' ' . $r['time']) ?></td>
        <td><?= clean(format_date($r['created_at'], true)) ?></td>
        <td><?= $statusLabel ?></td>
        <td>
          <?php if ($isExpired): ?>
            <span class="text-muted">—</span>
          <?php else: ?>
            <?php if ($r['status'] !== 'approved'): ?>
              <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#confirmApprove<?= $r['id'] ?>">موافقة</button>
            <?php endif; ?>
            <?php if ($r['status'] !== 'cancelled'): ?>
              <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmCancel<?= $r['id'] ?>">إلغاء</button>
            <?php endif; ?>
          <?php endif; ?>
        </td>
      </tr>

      <div class="modal fade" id="confirmApprove<?= $r['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header bg-success text-white">
              <h5 class="modal-title">تأكيد الموافقة</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
              هل أنت متأكد أنك تريد الموافقة على هذا الموعد (#<?= $r['id'] ?>)؟
            </div>
            <div class="modal-footer justify-content-center">
              <a href="?approve=<?= $r['id'] ?>" class="btn btn-success">نعم</a>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="confirmCancel<?= $r['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header bg-danger text-white">
              <h5 class="modal-title">تأكيد الإلغاء</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
              هل أنت متأكد أنك تريد إلغاء هذا الموعد (#<?= $r['id'] ?>)؟
            </div>
            <div class="modal-footer justify-content-center">
              <a href="?cancel=<?= $r['id'] ?>" class="btn btn-danger">نعم</a>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <tr><td colspan="7" class="text-muted">لا توجد مواعيد مطابقة للمعايير.</td></tr>
  <?php endif; ?>
  </tbody>
</table>
</div>

<?php if ($totalPages > 1): ?>
<nav aria-label="Bookings pagination">
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

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
