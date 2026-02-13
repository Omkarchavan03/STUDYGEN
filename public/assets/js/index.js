/* DARK MODE */
const toggleThemeBtn = document.getElementById("toggleTheme");

toggleThemeBtn.addEventListener("click", () => {
  document.body.classList.toggle("dark-theme");

  const icon = toggleThemeBtn.querySelector("i");
  icon.classList.toggle("fa-moon");
  icon.classList.toggle("fa-sun");

  localStorage.setItem(
    "theme",
    document.body.classList.contains("dark-theme") ? "dark" : "light"
  );
});

if (localStorage.getItem("theme") === "dark") {
  document.body.classList.add("dark-theme");
  toggleThemeBtn.querySelector("i").classList.replace("fa-moon", "fa-sun");
}

/* SEARCH */
const searchInput = document.getElementById("searchInput");

searchInput.addEventListener("input", () => {
  const query = searchInput.value.toLowerCase();
  const videoCards = document.querySelectorAll(".video-card");

  videoCards.forEach(card => {
    const title = card.querySelector(".video-title").innerText.toLowerCase();
    const desc  = card.querySelector(".video-desc").innerText.toLowerCase();

    card.style.display =
      title.includes(query) || desc.includes(query)
      ? "block"
      : "none";
  });
});

/* WATCH PAGE */
function openWatch(id) {
  window.location.href = "watch.php?id=" + id;
}

/* FOOTER ACTIVE */
document.querySelectorAll(".footer-nav .nav-item").forEach(item => {
  item.addEventListener("click", () => {
    document.querySelectorAll(".footer-nav .nav-item")
      .forEach(i => i.classList.remove("active"));
    item.classList.add("active");
  });
});

/* DOWNLOAD */
document.getElementById("downloadBtn")
.addEventListener("click", () => {
  alert("Download feature coming soon.");
});

/* NOTIFICATIONS */
const notificationBtn = document.getElementById("notificationBtn");
const notificationPanel = document.getElementById("notificationPanel");

notificationBtn.addEventListener("click", (e) => {
  e.stopPropagation();
  notificationPanel.classList.toggle("show");
});

document.addEventListener("click", () => {
  notificationPanel.classList.remove("show");
});
