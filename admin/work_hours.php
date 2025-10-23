<?php
// ==========================
// /admin/work_hours.php
// ==========================
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}
include_once __DIR__ . '/../includes/header.php';

// === Initialize defaults if table empty ===
$days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
$stmt = $pdo->query("SELECT COUNT(*) FROM work_hours");
if ($stmt->fetchColumn() == 0) {
    $insert = $pdo->prepare("INSERT INTO work_hours (day_name,is_open,open_time,close_time) VALUES (?,?,?,?)");
    foreach ($days as $day) $insert->execute([$day,1,'09:00:00','18:00:00']);
}

// === Fetch all ===
$rows = $pdo->query("SELECT * FROM work_hours ORDER BY FIELD(day_name,'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday')")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container py-4">
  <h3 class="mb-4 text-center fw-bold">🕒 إعدادات أيام وساعات العمل</h3>

  <div class="table-responsive">
    <table class="table align-middle table-bordered text-center">
      <thead class="table-light">
        <tr>
          <th>اليوم</th>
          <th>مفتوح؟</th>
          <th>من</th>
          <th>إلى</th>
          <th>استراحة من</th>
          <th>استراحة إلى</th>
        </tr>
      </thead>
      <tbody id="workHoursBody">
        <?php foreach ($rows as $r): ?>
        <tr data-id="<?php echo $r['id']; ?>">
          <td class="fw-semibold"><?php echo $r['day_name']; ?></td>
          <td>
            <div class="form-check form-switch d-flex justify-content-center">
              <input class="form-check-input open-toggle" type="checkbox" <?php echo ($r['is_open']?'checked':''); ?>>
            </div>
          </td>
          <td><input type="time" class="form-control form-control-sm open-time" value="<?php echo substr($r['open_time'],0,5); ?>"></td>
          <td><input type="time" class="form-control form-control-sm close-time" value="<?php echo substr($r['close_time'],0,5); ?>"></td>
          <td><input type="time" class="form-control form-control-sm break-start" value="<?php echo substr($r['break_start'],0,5); ?>"></td>
          <td><input type="time" class="form-control form-control-sm break-end" value="<?php echo substr($r['break_end'],0,5); ?>"></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="text-center mt-3">
    <button id="saveBtn" class="btn btn-success px-4"><i class="bi bi-save"></i> حفظ التغييرات</button>
  </div>

  <div id="saveStatus" class="text-center mt-3"></div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const saveBtn = document.getElementById('saveBtn');
  const status = document.getElementById('saveStatus');

  saveBtn.addEventListener('click', () => {
    const rows = [...document.querySelectorAll('#workHoursBody tr')];
    const data = rows.map(r => ({
      id: r.dataset.id,
      is_open: r.querySelector('.open-toggle').checked ? 1 : 0,
      open_time: r.querySelector('.open-time').value || null,
      close_time: r.querySelector('.close-time').value || null,
      break_start: r.querySelector('.break-start').value || null,
      break_end: r.querySelector('.break-end').value || null
    }));
    status.innerHTML = '<div class="text-info">جارِ الحفظ...</div>';
    fetch('work_hours_save.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify(data)
    })
    .then(res=>res.json())
    .then(resp=>{
      if(resp.success){
        status.innerHTML = '<div class="text-success">تم حفظ التغييرات بنجاح ✅</div>';
      } else {
        status.innerHTML = '<div class="text-danger">خطأ أثناء الحفظ</div>';
      }
    })
    .catch(()=>{
      status.innerHTML = '<div class="text-danger">فشل الاتصال بالخادم</div>';
    });
  });
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
