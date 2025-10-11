<?php
// ==========================
// /admin/notifications.php
// ==========================
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

// ==========================
// Handle actions before output
// ==========================
if (isset($_GET['read'])) {
    $id = intval($_GET['read']);
    $pdo->prepare("UPDATE notifications SET is_read=1 WHERE id=?")->execute([$id]);
    header("Location: notifications.php");
    exit;
}
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $pdo->prepare("DELETE FROM notifications WHERE id=?")->execute([$id]);
    header("Location: notifications.php");
    exit;
}
if (isset($_GET['clear'])) {
    $pdo->exec("TRUNCATE TABLE notifications");
    add_notification('system', 'تم مسح جميع الإشعارات.');
    header("Location: notifications.php");
    exit;
}
if (isset($_GET['mark_all_read'])) {
    $pdo->exec("UPDATE notifications SET is_read=1");
    header("Location: notifications.php");
    exit;
}

// ==========================
// Fetch all notifications
// ==========================
$stmt = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// === Only now start output ===
include_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h3>🔔 الإشعارات</h3>
  <div class="d-flex gap-2">
    <?php if (!empty($rows)): ?>
      <a href="?mark_all_read=1" class="btn btn-success btn-sm"
         onclick="return confirm('هل تريد تحديد جميع الإشعارات كمقروءة؟');">
         <i class="bi bi-check2-all"></i> تحديد الكل كمقروء
      </a>
      <a href="?clear=1" class="btn btn-danger btn-sm"
         onclick="return confirm('هل أنت متأكد أنك تريد مسح جميع الإشعارات؟');">
         <i class="bi bi-x-circle"></i> مسح الكل
      </a>
    <?php endif; ?>
    <a href="dashboard.php" class="btn btn-secondary btn-sm">
      <i class="bi bi-arrow-left"></i> رجوع
    </a>
  </div>
</div>

<div class="table-responsive">
  <table class="table table-bordered table-hover align-middle">
    <thead class="table-light">
      <tr>
        <th>#</th>
        <th>النوع</th>
        <th>الرسالة</th>
        <th>التاريخ</th>
        <th>الإجراءات</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr><td colspan="5" class="text-center text-muted">لا توجد إشعارات حالياً</td></tr>
      <?php else: ?>
        <?php foreach ($rows as $n): ?>
          <tr class="<?php echo $n['is_read'] ? '' : 'table-warning'; ?>">
            <td><?php echo $n['id']; ?></td>
            <td><?php echo htmlspecialchars($n['type']); ?></td>
            <td><?php echo htmlspecialchars($n['message']); ?></td>
            <td><?php echo date('Y-m-d H:i', strtotime($n['created_at'])); ?></td>
            <td>
              <div class="btn-group">
                <?php if (!$n['is_read']): ?>
                  <a href="?read=<?php echo $n['id']; ?>" class="btn btn-success btn-sm">
                    <i class="bi bi-check2-circle"></i> تم القراءة
                  </a>
                <?php endif; ?>
                <a href="?delete=<?php echo $n['id']; ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('هل أنت متأكد من الحذف؟');">
                   <i class="bi bi-trash"></i>
                </a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
