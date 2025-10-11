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
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener("click", function (e) {
      const target = document.querySelector(this.getAttribute("href"));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: "smooth" });
      }
    });
  });
});
