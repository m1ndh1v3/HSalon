<?php
// ==========================
// /admin/notifications_recent.php
// ==========================
require_once __DIR__ . '/../config.php';
header('Content-Type: text/html; charset=utf-8');

$stmt = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$rows): ?>
  <div class="text-center py-4 text-muted">لا توجد إشعارات حالياً</div>
<?php else: ?>
  <ul class="list-group list-group-flush text-end" dir="rtl">
    <?php foreach ($rows as $n): ?>
      <li class="list-group-item d-flex justify-content-between align-items-center <?php echo $n['is_read'] ? '' : 'list-group-item-warning'; ?>">
        <div class="flex-grow-1 pe-2 text-end">
          <div class="fw-bold mb-1"><?php echo htmlspecialchars($n['type']); ?></div>
          <div class="small text-muted mb-1"><?php echo htmlspecialchars($n['message']); ?></div>
          <small class="text-secondary"><?php echo date('Y-m-d H:i', strtotime($n['created_at'])); ?></small>
        </div>
        <div class="d-flex flex-column align-items-center gap-1 ms-2">
          <?php if (!$n['is_read']): ?>
            <button class="btn btn-success btn-sm" onclick="notifAction('read', <?php echo $n['id']; ?>)">
              <i class="bi bi-check2-circle"></i>
            </button>
          <?php endif; ?>
          <button class="btn btn-danger btn-sm" onclick="notifAction('delete', <?php echo $n['id']; ?>)">
            <i class="bi bi-trash"></i>
          </button>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>
