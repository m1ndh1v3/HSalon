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

// ==========================
// Admin Notifications + Theme Toggle moved from header
// ==========================

document.addEventListener("DOMContentLoaded", () => {
  // --- theme toggle ---
  const toggleBtn = document.getElementById("themeToggle");
  if (toggleBtn) {
    const body = document.body;
    const icon = toggleBtn.querySelector("i");
    const savedTheme = localStorage.getItem("theme");
    if (savedTheme && savedTheme !== body.className) {
      body.className = savedTheme;
      icon.className = `bi bi-${savedTheme === "light" ? "moon" : "sun"}`;
    }
    toggleBtn.addEventListener("click", () => {
      const current = body.classList.contains("dark") ? "dark" : "light";
      const next = current === "light" ? "dark" : "light";
      body.classList.remove(current);
      body.classList.add(next);
      icon.className = `bi bi-${next === "light" ? "moon" : "sun"}`;
      localStorage.setItem("theme", next);
      fetch(`?theme=${next}`).catch(() => {});
    });
  }

  // --- admin notifications (if admin) ---
  const notifBtn = document.getElementById("notifBtn");
  if (notifBtn) {
    const bellIcon = notifBtn.querySelector(".bi-bell");
    const pingSound = document.getElementById("notifPing");
    const notifList = document.getElementById("notifList");
    const markAllBtn = document.getElementById("markAllBtn");
    const clearAllBtn = document.getElementById("clearAllBtn");
    const KEY = "notif_last_seen";
    let lastCount = parseInt(notifBtn.dataset.unread || "0", 10);
    if (localStorage.getItem(KEY) === null)
      localStorage.setItem(KEY, String(lastCount));
    const lastSeen = parseInt(localStorage.getItem(KEY) || "0", 10);
    if (lastCount > lastSeen) bellIcon.classList.add("text-warning");

    const loadRecentNotifications = () => {
      if (!notifList) return;
      notifList.innerHTML = '<div class="text-center py-4 text-muted">جارِ التحميل...</div>';
      fetch(`${SITE_URL}/admin/notifications_recent.php`)
        .then(r => r.text())
        .then(html => (notifList.innerHTML = html))
        .catch(
          () =>
            (notifList.innerHTML =
              '<div class="text-center py-4 text-danger">حدث خطأ أثناء تحميل الإشعارات.</div>')
        );
    };

    const updateBadge = () => {
      fetch(`${SITE_URL}/admin/notifications_count.php`)
        .then(r => r.json())
        .then(data => {
          let badge = notifBtn.querySelector(".badge");
          if (!badge && data.count > 0) {
            badge = document.createElement("span");
            badge.className =
              "position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger";
            notifBtn.appendChild(badge);
          }
          if (badge) {
            badge.textContent = data.count > 0 ? data.count : "";
            badge.style.display = data.count > 0 ? "inline" : "none";
          }
          if (data.count > lastCount && pingSound) {
            pingSound.currentTime = 0;
            pingSound.play().catch(() => {});
            bellIcon.classList.add("shake");
            setTimeout(() => bellIcon.classList.remove("shake"), 1200);
          }
          const currentSeen = parseInt(localStorage.getItem(KEY) || "0", 10);
          if (data.count > currentSeen)
            bellIcon.classList.add("text-warning");
          else bellIcon.classList.remove("text-warning");
          lastCount = data.count;
        })
        .catch(() => {});
    };

    notifBtn.addEventListener("click", () => {
      const badgeNow = parseInt(
        (notifBtn.querySelector(".badge")?.textContent || "0"),
        10
      );
      localStorage.setItem(KEY, String(badgeNow));
      bellIcon.classList.remove("text-warning");
      loadRecentNotifications();
    });

    if (markAllBtn)
      markAllBtn.addEventListener("click", () =>
        showThemedConfirm("هل تريد تحديد جميع الإشعارات كمقروءة؟", () => {
          fetch(`${SITE_URL}/admin/notifications_action.php?action=mark_all`)
            .then(() => loadRecentNotifications())
            .then(() => updateBadge());
        })
      );

    if (clearAllBtn)
      clearAllBtn.addEventListener("click", () =>
        showThemedConfirm("هل أنت متأكد أنك تريد مسح جميع الإشعارات؟", () => {
          fetch(`${SITE_URL}/admin/notifications_action.php?action=clear`)
            .then(() => loadRecentNotifications())
            .then(() => updateBadge());
        })
      );

    updateBadge();
    setInterval(updateBadge, 20000);

    // bell shake animation style
    const style = document.createElement("style");
    style.textContent =
      "@keyframes shakeAnim{0%,100%{transform:rotate(0);}20%{transform:rotate(-15deg);}40%{transform:rotate(10deg);}60%{transform:rotate(-10deg);}80%{transform:rotate(5deg);}}.shake{animation:shakeAnim .6s ease;}";
    document.head.appendChild(style);
  }
});

// ==========================
// Bookings Page AJAX + Toast Feedback
// ==========================
document.addEventListener("DOMContentLoaded", () => {
  if (!window.initBookingsPage) return;

  const toastEl = document.getElementById("toastAlert");
  const toastMsg = document.getElementById("toastMsg");
  const toast = toastEl ? new bootstrap.Toast(toastEl, { delay: 2000 }) : null;

  document.querySelectorAll(".action-btn").forEach(btn => {
    btn.addEventListener("click", () => {
      const id = btn.dataset.id;
      const action = btn.dataset.action;
      if (!confirm(action === "approve" ? "تأكيد الموافقة؟" : "تأكيد الإلغاء؟")) return;

      fetch(`bookings_action.php?action=${action}&id=${id}`)
        .then(r => r.json())
        .then(d => {
          if (d.success) {
            const row = document.getElementById(`row-${id}`);
            const cell = row.querySelector(".status-cell");
            if (d.status === "approved")
              cell.innerHTML = '<span class="badge bg-success">تمت الموافقة</span>';
            else if (d.status === "cancelled")
              cell.innerHTML = '<span class="badge bg-danger">تم الإلغاء</span>';
            row.querySelectorAll(".action-btn").forEach(b => b.remove());
            if (typeof playNotifSound === "function") playNotifSound();

            if (toastEl && toastMsg) {
              toastEl.classList.remove("bg-success", "bg-danger");
              toastEl.classList.add(d.status === "approved" ? "bg-success" : "bg-danger");
              toastMsg.textContent =
                d.status === "approved" ? "تمت الموافقة بنجاح" : "تم الإلغاء بنجاح";
              toast.show();
            }
          } else alert(d.msg);
        })
        .catch(e => alert("حدث خطأ: " + e));
    });
  });
});

document.addEventListener("click", function (e) {
  const target = e.target.closest('[data-bs-toggle="lightbox"]');
  if (target) {
    e.preventDefault();
    $(target).ekkoLightbox();
  }
});


// --- show signup success toast after redirect ---
document.addEventListener("DOMContentLoaded", () => {
  const toastContainer = document.createElement("div");
  toastContainer.className = "position-fixed bottom-0 end-0 p-3";
  toastContainer.style.zIndex = "9999";
  document.body.appendChild(toastContainer);
  toastContainer.innerHTML = `
    <div id="signupToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body" id="signupToastMsg">تم إنشاء الحساب بنجاح! الرجاء تسجيل الدخول</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>`;
  const toastEl = document.getElementById("signupToast");
  const toast = new bootstrap.Toast(toastEl, { delay: 3500 });

  const params = new URLSearchParams(window.location.search);
  if (params.get("registered") === "1") {
    const lang = localStorage.getItem("lang") || "ar";
    document.getElementById("signupToastMsg").textContent =
      lang === "ar" ? "تم إنشاء الحساب بنجاح! الرجاء تسجيل الدخول" : "Account created successfully! Please log in";
    toast.show();
    params.delete("registered");
    window.history.replaceState({}, document.title, window.location.pathname);
  }
});

document.addEventListener('DOMContentLoaded', () => {
  const track = document.querySelector('.gallery-track');
  const lang = document.documentElement.lang || 'en';
  const isRTL = lang.startsWith('ar');
  const speed = 60; // slower movement
  const resumeDelay = 500; // 0.5s after leaving

  const anim = isRTL ? 'galleryScrollRTL' : 'galleryScrollLTR';
  track.style.animation = `${anim} ${speed}s linear infinite`;

  let paused = false;
  let timeout;

  const pause = () => {
    if (!paused) {
      track.style.animationPlayState = 'paused';
      paused = true;
    }
  };
  const resume = () => {
    if (paused) {
      track.style.animationPlayState = 'running';
      paused = false;
    }
  };

  document.querySelectorAll('.gallery-item').forEach(item => {
    item.addEventListener('mouseenter', () => {
      clearTimeout(timeout);
      pause();
    });
    item.addEventListener('mouseleave', () => {
      clearTimeout(timeout);
      timeout = setTimeout(resume, resumeDelay);
    });
    item.addEventListener('click', pause);
  });
});