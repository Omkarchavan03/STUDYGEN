/* =========================
   SAFE DOM READY WRAPPER
========================= */
document.addEventListener("DOMContentLoaded", () => {

  /* =========================
     DARK MODE
  ========================= */

  const toggleThemeBtn = document.getElementById("toggleTheme");

  // Apply saved theme early
  const savedTheme = localStorage.getItem("theme");
  if (savedTheme === "dark") {
    document.body.classList.add("dark-theme");
  }

  if (toggleThemeBtn) {
    const icon = toggleThemeBtn.querySelector("i");

    // Fix icon on load
    if (document.body.classList.contains("dark-theme")) {
      icon.classList.remove("fa-moon");
      icon.classList.add("fa-sun");
    }

    toggleThemeBtn.addEventListener("click", () => {
      document.body.classList.toggle("dark-theme");

      const isDark = document.body.classList.contains("dark-theme");

      icon.classList.toggle("fa-moon", !isDark);
      icon.classList.toggle("fa-sun", isDark);

      localStorage.setItem("theme", isDark ? "dark" : "light");
    });
  }

  /* =========================
     SEARCH (Debounced)
  ========================= */

  const searchInput = document.getElementById("searchInput");

  if (searchInput) {
    let debounceTimer;

    searchInput.addEventListener("input", () => {
      clearTimeout(debounceTimer);

      debounceTimer = setTimeout(() => {
        const query = searchInput.value.toLowerCase().trim();
        const videoCards = document.querySelectorAll(".video-card");

        videoCards.forEach(card => {
          const titleEl = card.querySelector(".video-title");
          const descEl  = card.querySelector(".video-desc");

          const title = titleEl ? titleEl.textContent.toLowerCase() : "";
          const desc  = descEl ? descEl.textContent.toLowerCase() : "";

          const match = title.includes(query) || desc.includes(query);

          card.style.display = match ? "" : "none";
        });

      }, 200); // 200ms debounce
    });
  }

  /* =========================
     FOOTER ACTIVE LINK
  ========================= */

  const currentPage = window.location.pathname.split("/").pop();

  document.querySelectorAll(".footer-nav a").forEach(link => {
    const href = link.getAttribute("href");

    if (href === currentPage) {
      link.parentElement.classList.add("active");
    }
  });

  /* =========================
     DOWNLOAD BUTTON
  ========================= */

  const downloadBtn = document.getElementById("downloadBtn");

  if (downloadBtn) {
    downloadBtn.addEventListener("click", () => {
      alert("Download feature coming soon.");
    });
  }

  /* =========================
     NOTIFICATIONS
  ========================= */

  const notificationBtn = document.getElementById("notificationBtn");
  const notificationPanel = document.getElementById("notificationPanel");

  if (notificationBtn && notificationPanel) {

    notificationBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      notificationPanel.classList.toggle("show");
    });

    notificationPanel.addEventListener("click", (e) => {
      e.stopPropagation();
    });

    document.addEventListener("click", () => {
      notificationPanel.classList.remove("show");
    });
  }

});
