<?php
// ==========================
// /admin/calendar_month.php — highlight current day + unified nav bar
// ==========================
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}
include_once __DIR__ . '/../includes/header.php';

$monthOffset = intval($_GET['m'] ?? 0);
$currentMonth = strtotime("first day of this month $monthOffset month");
$year = date('Y', $currentMonth);
$month = date('n', $currentMonth);
$daysInMonth = date('t', $currentMonth);
$firstDayWeek = date('w', strtotime("$year-$month-01"));
$startDate = date('Y-m-d', strtotime("-$firstDayWeek day", strtotime("$year-$month-01")));
$endDate = date('Y-m-d', strtotime("+41 day", strtotime($startDate)));

try {
    $stmt = $pdo->prepare("SELECT b.date, COUNT(*) AS total,
                                  SUM(CASE WHEN b.status='approved' THEN 1 ELSE 0 END) AS approved
                           FROM bookings b
                           WHERE b.date BETWEEN ? AND ?
                           GROUP BY b.date");
    $stmt->execute([$startDate, $endDate]);
    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $stats = [];
}
$map = [];
foreach ($stats as $s) $map[$s['date']] = $s;
$arabicMonths = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
$today = date('Y-m-d');
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex gap-2">
      <a href="bookings.php" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="عرض الجدول"><i class="bi bi-clipboard-data"></i></a>
      <a href="calendar.php" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="العرض الأسبوعي"><i class="bi bi-calendar-week"></i></a>
      <a href="calendar_month.php" class="btn btn-primary" data-bs-toggle="tooltip" title="العرض الشهري"><i class="bi bi-calendar-month"></i></a>
    </div>
    <div class="fw-bold fs-5"><?php echo $arabicMonths[$month-1].' '.$year; ?></div>
    <div class="d-flex gap-2">
      <a href="?m=<?php echo $monthOffset-1; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-chevron-right"></i></a>
      <a href="?m=<?php echo $monthOffset+1; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-chevron-left"></i></a>
    </div>
  </div>

  <div class="table-responsive border rounded shadow-sm">
    <table class="table mb-0 calendar-month text-center align-middle">
      <thead class="table-light">
        <tr>
          <th>الأحد</th><th>الاثنين</th><th>الثلاثاء</th><th>الأربعاء</th><th>الخميس</th><th>الجمعة</th><th>السبت</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $dayPointer = strtotime($startDate);
        for ($row=0; $row<6; $row++):
            echo "<tr>";
            for ($col=0; $col<7; $col++):
                $d = date('Y-m-d', $dayPointer);
                $inMonth = date('n', $dayPointer) == $month;
                $todayMark = ($d == $today) ? 'border border-2 border-primary bg-primary bg-opacity-10' : '';
                $count = $map[$d]['total'] ?? 0;
                $approved = $map[$d]['approved'] ?? 0;
                $busyClass = $count ? ($approved ? 'bg-success bg-opacity-10' : 'bg-warning bg-opacity-10') : '';
        ?>
        <td class="<?php echo trim(($inMonth?'':'text-muted').' '.$busyClass.' '.$todayMark); ?>" style="height:110px; cursor:pointer;" data-date="<?php echo $d; ?>">
          <div class="fw-semibold <?php echo ($d == $today)?'text-primary fw-bold':''; ?>"><?php echo date('j', $dayPointer); ?></div>
          <?php if($count): ?>
            <div class="small mt-1"><?php echo $count; ?> <?php echo ($count == 1 ? 'موعد' : 'مواعيد'); ?></div>
            <?php if($approved>0): ?><div class="text-success small">✔ <?php echo $approved; ?></div><?php endif; ?>
          <?php endif; ?>
        </td>
        <?php
                $dayPointer = strtotime('+1 day', $dayPointer);
            endfor;
            echo "</tr>";
        endfor;
        ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="dayModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title">مواعيد اليوم</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-end" id="dayBookings">جارِ التحميل...</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إغلاق</button>
      </div>
    </div>
  </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
