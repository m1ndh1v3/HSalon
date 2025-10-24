// ==========================
// /assets/js/main.js
// ==========================

document.addEventListener("DOMContentLoaded", function () {
  // Theme persistence
  const body = document.body;
  const saved = localStorage.getItem("theme");
  if (saved) body.className = saved;

  document.querySelectorAll('a[href*="?theme="]').forEach(link => {
    link.addEventListener("click", function (e) {
      const next = this.href.split("theme=")[1];
      localStorage.setItem("theme", next);
    });
  });

  // Language toggle persistence (optional future use)
  document.querySelectorAll('a[href*="?lang="]').forEach(link => {
    link.addEventListener("click", function () {
      localStorage.setItem("lang", this.href.split("lang=")[1]);
    });
  });

  // Smooth scroll for anchors

});
document.addEventListener("DOMContentLoaded", () => {
  const datePicker = document.getElementById('datePicker');
  const timeSelect = document.getElementById('timeSelect');
  if (!datePicker || !timeSelect) return; // prevent null access

  datePicker.addEventListener('change', async () => {
    const dateVal = datePicker.value;
    timeSelect.innerHTML = '<option>جارِ التحميل...</option>';
    timeSelect.disabled = true;

    const res = await fetch('get_available_times.php?date=' + encodeURIComponent(dateVal));
    const data = await res.json();

    timeSelect.innerHTML = '';
    if (!data.success) {
      timeSelect.innerHTML = '<option value="">المكان مغلق في هذا اليوم</option>';
      return;
    }
    if (data.times.length === 0) {
      timeSelect.innerHTML = '<option value="">لا توجد مواعيد متاحة</option>';
      return;
    }
    data.times.forEach(t => {
      const opt = document.createElement('option');
      opt.value = t;
      opt.textContent = t;
      timeSelect.appendChild(opt);
    });
    timeSelect.disabled = false;
  });
});


document.addEventListener("DOMContentLoaded", () => {
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(el => new bootstrap.Tooltip(el));
  document.querySelectorAll('.calendar-month td[data-date]').forEach(td => {
    td.addEventListener('click', async () => {
      const d = td.dataset.date;
      const body = document.getElementById('dayBookings');
      body.innerHTML = 'جارِ التحميل...';
      const res = await fetch('calendar_day_details.php?date=' + d);
      const html = await res.text();
      body.innerHTML = html;
      new bootstrap.Modal(document.getElementById('dayModal')).show();
    });
  });
});

document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll('.booking-card').forEach(c => {
    c.addEventListener('click', async () => {
      const id = c.dataset.id;
      const modalBody = document.getElementById('bookingDetails');
      modalBody.innerHTML = 'جارِ التحميل...';
      const res = await fetch('calendar_booking_details.php?id=' + id);
      const html = await res.text();
      modalBody.innerHTML = html;
      new bootstrap.Modal(document.getElementById('bookingModal')).show();
    });
  });
});

// ==========================
// Dashboard Bookings Chart
// ==========================
document.addEventListener("DOMContentLoaded", () => {
  const chartEl = document.getElementById("bookingsChart");
  if (!chartEl) return;

  const approved = parseInt(chartEl.dataset.approved || 0);
  const pending = parseInt(chartEl.dataset.pending || 0);
  const cancelled = parseInt(chartEl.dataset.cancelled || 0);

  new Chart(chartEl, {
    type: "pie",
    data: {
      labels: ["موافقة", "معلقة", "ملغاة"],
      datasets: [{
        data: [approved, pending, cancelled],
        backgroundColor: ["#28a745", "#ffc107", "#dc3545"],
      }]
    },
    options: {
      aspectRatio: 1.3,
      plugins: { legend: { position: "bottom" } }
    }
  });
});


document.querySelectorAll('.theme-toggle').forEach(btn=>{
  btn.addEventListener('click', e=>{
    e.preventDefault();
    document.body.classList.toggle('dark');
    document.body.classList.toggle('light');
    localStorage.setItem('theme', document.body.className);
  });
});

window.showThemedConfirm = (msg, onConfirm) => {
  const modal = document.createElement("div");
  modal.className = "modal fade";
  modal.innerHTML = `
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content text-center p-3">
        <div class="modal-body"><p class="mb-3 fs-5">${msg}</p></div>
        <div class="modal-footer border-0 justify-content-center gap-2">
          <button class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
          <button class="btn btn-danger confirm-btn">تأكيد</button>
        </div>
      </div>
    </div>`;
  document.body.appendChild(modal);
  const bsModal = new bootstrap.Modal(modal);
  modal.querySelector(".confirm-btn").addEventListener("click", () => {
    bsModal.hide();
    onConfirm?.();
  });
  modal.addEventListener("hidden.bs.modal", () => modal.remove());
  bsModal.show();
};

window.notifAction = (action, id = null) => {
  const url = `${SITE_URL}/admin/notifications_action.php?action=${action}${id ? `&id=${id}` : ""}`;
  const run = () => {
    fetch(url)
      .then(() => {
        if (typeof loadRecentNotifications === "function") loadRecentNotifications();
      })
      .catch(() => console.error("Failed to perform notification action:", action));
  };

  if (action === "delete" || action === "clear") {
    showThemedConfirm("هل أنت متأكد من الحذف؟", run);
  } else run();
};

document.addEventListener("DOMContentLoaded", () => {
  const langBtn = document.getElementById("langToggle");
  if (!langBtn) return;
  langBtn.addEventListener("click", () => {
    fetch(`${SITE_URL}/includes/lang_switch_action.php`, { cache: "no-store" })
      .then(() => window.location.reload(true))
      .catch(() => console.error("Language switch failed"));
  });
});
