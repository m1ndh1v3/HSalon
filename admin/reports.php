<?php
// ==========================
// /admin/reports.php — Booking Reports (CSV + Printable PDF)
// ==========================
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['admin_id'])) {
  header("Location: index.php");
  exit;
}

// === Handle export requests before any output ===
if (isset($_GET['export'])) {
    $format = $_GET['export'];
    $status = $_GET['status'] ?? '';
    $from   = $_GET['from'] ?? '';
    $to     = $_GET['to'] ?? '';
    $client = intval($_GET['client_id'] ?? 0);
    $service = intval($_GET['service_id'] ?? 0);
    $created_from = $_GET['created_from'] ?? '';
    $created_to   = $_GET['created_to'] ?? '';

    $query = "SELECT b.*, c.name AS client_name, s.name AS service_name
              FROM bookings b
              LEFT JOIN clients c ON b.client_id=c.id
              LEFT JOIN services s ON b.service_id=s.id
              WHERE 1";
    $params = [];

    if ($status && in_array($status, ['pending','approved','cancelled'])) {
        $query .= " AND b.status=?";
        $params[] = $status;
    }
    if ($client > 0) { $query .= " AND b.client_id=?"; $params[] = $client; }
    if ($service > 0) { $query .= " AND b.service_id=?"; $params[] = $service; }
    if ($from) { $query .= " AND b.date >= ?"; $params[] = $from; }
    if ($to)   { $query .= " AND b.date <= ?"; $params[] = $to; }
    if ($created_from) { $query .= " AND b.created_at >= ?"; $params[] = $created_from . " 00:00:00"; }
    if ($created_to)   { $query .= " AND b.created_at <= ?"; $params[] = $created_to . " 23:59:59"; }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // === CSV export with UTF-8 BOM ===
    if ($format === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="bookings_report.csv"');
        echo "\xEF\xBB\xBF"; // UTF-8 BOM for Excel Arabic support
        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID','العميل','الخدمة','تاريخ الموعد','الوقت','الحالة','تاريخ الإنشاء']);
        foreach ($rows as $r) {
            fputcsv($out, [$r['id'],$r['client_name'],$r['service_name'],$r['date'],$r['time'],$r['status'],$r['created_at']]);
        }
        fclose($out);
        exit;
    }

    // === Enhanced printable HTML report (save as PDF manually) ===
    if ($format === 'pdf') {
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: inline; filename="bookings_report.html"');
        ?>
        <!DOCTYPE html>
        <html lang="ar" dir="rtl">
        <head>
          <meta charset="utf-8">
          <title>تقرير الحجوزات</title>
          <style>
            @page { size: A4 landscape; margin: 20mm; }
            body { font-family: 'DejaVu Sans', 'Amiri', Arial, sans-serif; direction: rtl; background:#fff; color:#000; }
            h2 { text-align:center; margin:0; }
            .header { text-align:center; margin-bottom:25px; }
            .header img { height:60px; margin-bottom:10px; }
            .date-range { font-size:14px; color:#555; }
            table { width:100%; border-collapse:collapse; margin-top:15px; }
            th, td { border:1px solid #999; padding:7px; text-align:center; font-size:13px; }
            th { background:#e9ecef; font-weight:bold; }
            tr:nth-child(even) { background:#f8f9fa; }
            .footer { margin-top:40px; text-align:center; font-size:13px; color:#666; }
            .signature { margin-top:60px; text-align:left; font-size:13px; }
            @media print { body { margin:0; } }
          </style>
        </head>
        <body onload="window.print()">
          <div class="header">
            <img src="<?php echo SITE_URL; ?>/assets/img/hsalon_logo.png" alt="Logo">
            <h2><?php echo SITE_NAME; ?> - تقرير الحجوزات</h2>
            <div class="date-range">تاريخ الإنشاء: <?= date('Y-m-d H:i') ?></div>
          </div>
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>العميل</th>
                <th>الخدمة</th>
                <th>تاريخ الموعد</th>
                <th>الوقت</th>
                <th>الحالة</th>
                <th>تاريخ الإنشاء</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $r): ?>
                <tr>
                  <td><?= $r['id'] ?></td>
                  <td><?= htmlspecialchars($r['client_name']) ?></td>
                  <td><?= htmlspecialchars($r['service_name']) ?></td>
                  <td><?= $r['date'] ?></td>
                  <td><?= $r['time'] ?></td>
                  <td><?= match($r['status']) {
                    'approved' => 'موافقة',
                    'cancelled' => 'ملغاة',
                    default => 'معلقة'
                  }; ?></td>
                  <td><?= $r['created_at'] ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <div class="footer">
            <div>إجمالي الحجوزات: <?= count($rows) ?></div>
            <div class="signature">التوقيع: ______________________</div>
          </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// === Fetch dropdown data ===
$clients = $pdo->query("SELECT id,name FROM clients ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$services = $pdo->query("SELECT id,name FROM services ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// === Summary totals ===
$total_all = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$total_approved = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status='approved'")->fetchColumn();
$total_pending  = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status='pending'")->fetchColumn();
$total_cancelled = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status='cancelled'")->fetchColumn();

include_once __DIR__ . '/../includes/header.php';
?>

<h2 class="text-center mb-4">تقارير الحجوزات</h2>

<form method="get" class="row g-3 mb-4 text-end" dir="rtl">
  <div class="col-md-2">
    <label class="form-label">من تاريخ الموعد</label>
    <input type="date" name="from" class="form-control" value="<?= clean($_GET['from'] ?? '') ?>">
  </div>
  <div class="col-md-2">
    <label class="form-label">إلى تاريخ الموعد</label>
    <input type="date" name="to" class="form-control" value="<?= clean($_GET['to'] ?? '') ?>">
  </div>
  <div class="col-md-2">
    <label class="form-label">من تاريخ الإنشاء</label>
    <input type="date" name="created_from" class="form-control" value="<?= clean($_GET['created_from'] ?? '') ?>">
  </div>
  <div class="col-md-2">
    <label class="form-label">إلى تاريخ الإنشاء</label>
    <input type="date" name="created_to" class="form-control" value="<?= clean($_GET['created_to'] ?? '') ?>">
  </div>
  <div class="col-md-2">
    <label class="form-label">العميل</label>
    <select name="client_id" class="form-select">
      <option value="">الكل</option>
      <?php foreach ($clients as $c): ?>
        <option value="<?= $c['id'] ?>" <?= ($_GET['client_id'] ?? '')==$c['id']?'selected':''; ?>>
          <?= htmlspecialchars($c['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-2">
    <label class="form-label">الخدمة</label>
    <select name="service_id" class="form-select">
      <option value="">الكل</option>
      <?php foreach ($services as $s): ?>
        <option value="<?= $s['id'] ?>" <?= ($_GET['service_id'] ?? '')==$s['id']?'selected':''; ?>>
          <?= htmlspecialchars($s['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-2">
    <label class="form-label">الحالة</label>
    <select name="status" class="form-select">
      <option value="">الكل</option>
      <option value="pending"   <?= ($_GET['status'] ?? '')==='pending'?'selected':''; ?>>معلقة</option>
      <option value="approved"  <?= ($_GET['status'] ?? '')==='approved'?'selected':''; ?>>موافقة</option>
      <option value="cancelled" <?= ($_GET['status'] ?? '')==='cancelled'?'selected':''; ?>>ملغاة</option>
    </select>
  </div>
  <div class="col-md-2 d-flex align-items-end">
    <button type="submit" class="btn btn-primary w-100">عرض التقرير</button>
  </div>
</form>

<div class="row text-center mb-4" dir="rtl">
  <div class="col-md-3 mb-2"><span class="badge bg-secondary fs-6">إجمالي: <?= $total_all ?></span></div>
  <div class="col-md-3 mb-2"><span class="badge bg-success fs-6">موافقة: <?= $total_approved ?></span></div>
  <div class="col-md-3 mb-2"><span class="badge bg-warning text-dark fs-6">معلقة: <?= $total_pending ?></span></div>
  <div class="col-md-3 mb-2"><span class="badge bg-danger fs-6">ملغاة: <?= $total_cancelled ?></span></div>
</div>

<div class="text-center mb-4" dir="rtl">
  <a href="?<?= http_build_query(array_merge($_GET, ['export'=>'csv'])) ?>" class="btn btn-outline-primary me-2">
    <i class="bi bi-file-earmark-spreadsheet"></i> تصدير CSV
  </a>
  <a href="?<?= http_build_query(array_merge($_GET, ['export'=>'pdf'])) ?>" class="btn btn-outline-danger">
    <i class="bi bi-file-earmark-pdf"></i> تصدير PDF
  </a>
</div>

<?php
// === Display filtered results ===
$status = $_GET['status'] ?? '';
$from   = $_GET['from'] ?? '';
$to     = $_GET['to'] ?? '';
$client = intval($_GET['client_id'] ?? 0);
$service = intval($_GET['service_id'] ?? 0);
$created_from = $_GET['created_from'] ?? '';
$created_to   = $_GET['created_to'] ?? '';

$query = "SELECT b.*, c.name AS client_name, s.name AS service_name
          FROM bookings b
          LEFT JOIN clients c ON b.client_id=c.id
          LEFT JOIN services s ON b.service_id=s.id
          WHERE 1";
$params = [];

if ($status && in_array($status, ['pending','approved','cancelled'])) {
    $query .= " AND b.status=?";
    $params[] = $status;
}
if ($client > 0) { $query .= " AND b.client_id=?"; $params[] = $client; }
if ($service > 0) { $query .= " AND b.service_id=?"; $params[] = $service; }
if ($from) { $query .= " AND b.date >= ?"; $params[] = $from; }
if ($to)   { $query .= " AND b.date <= ?"; $params[] = $to; }
if ($created_from) { $query .= " AND b.created_at >= ?"; $params[] = $created_from . " 00:00:00"; }
if ($created_to)   { $query .= " AND b.created_at <= ?"; $params[] = $created_to . " 23:59:59"; }
$query .= " ORDER BY b.date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="table-responsive">
<table class="table table-bordered table-striped text-center align-middle" dir="rtl">
  <thead class="table-light">
    <tr>
      <th>ID</th>
      <th>العميل</th>
      <th>الخدمة</th>
      <th>تاريخ الموعد</th>
      <th>الوقت</th>
      <th>الحالة</th>
      <th>تاريخ الإنشاء</th>
    </tr>
  </thead>
  <tbody>
    <?php if ($rows): foreach ($rows as $r): ?>
      <tr>
        <td><?= $r['id'] ?></td>
        <td><?= clean($r['client_name']) ?></td>
        <td><?= clean($r['service_name']) ?></td>
        <td><?= $r['date'] ?></td>
        <td><?= $r['time'] ?></td>
        <td>
          <?= match($r['status']) {
            'approved' => '<span class="badge bg-success">موافقة</span>',
            'cancelled' => '<span class="badge bg-danger">ملغاة</span>',
            default => '<span class="badge bg-warning text-dark">معلقة</span>'
          }; ?>
        </td>
        <td><?= format_date($r['created_at'], true) ?></td>
      </tr>
    <?php endforeach; else: ?>
      <tr><td colspan="7" class="text-muted">لا توجد نتائج مطابقة للبحث.</td></tr>
    <?php endif; ?>
  </tbody>
</table>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
