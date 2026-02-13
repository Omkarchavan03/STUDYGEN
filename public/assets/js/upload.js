/* ================= TAB SWITCH ================= */
function switchTab(tabId) {
  document.querySelectorAll(".tab").forEach(tab => {
    tab.classList.remove("active");
  });

  document.querySelectorAll(".form").forEach(form => {
    form.classList.remove("active");
  });

  document.querySelector(`[onclick*="${tabId}"]`).classList.add("active");
  document.getElementById(tabId + "Form").classList.add("active");
}

/* ================= THUMBNAIL TOGGLE ================= */
function toggleThumb(checkbox) {
  const thumbInput = document.getElementById("thumbInput");
  if (!thumbInput) return;

  thumbInput.style.display = checkbox.checked ? "block" : "none";
}

/* ================= RESET UI (FORM-SCOPED) ================= */
function resetProgress(form) {
  const progress = form.querySelector("progress");
  const msg = form.querySelector("#msg");

  if (progress) progress.value = 0;
  if (msg) msg.innerHTML = "";
}

/* ================= AJAX UPLOAD ================= */
function handleUpload(form) {
  const msg = form.querySelector("#msg");
  const progress = form.querySelector("progress");

  resetProgress(form);

  const formData = new FormData(form);
  const xhr = new XMLHttpRequest();

  xhr.open("POST", "upload.php", true);

  /* progress */
  xhr.upload.onprogress = function (e) {
    if (e.lengthComputable && progress) {
      progress.value = Math.round((e.loaded / e.total) * 100);
    }
  };

  /* response */
  xhr.onload = function () {
    if (xhr.status === 200) {
      if (xhr.responseText.trim() === "OK") {
        msg.textContent = "✅ Upload successful";
        msg.style.color = "green";

        form.reset();
        if (progress) progress.value = 0;

        // hide thumbnail input after upload
        const thumb = document.getElementById("thumbInput");
        if (thumb) thumb.style.display = "none";

        setTimeout(() => {
          window.location.href = "profile.php";
        }, 1200);

      } else {
        msg.textContent = "❌ " + xhr.responseText;
        msg.style.color = "red";
      }
    } else {
      msg.textContent = "❌ Upload failed";
      msg.style.color = "red";
    }
  };

  xhr.onerror = function () {
    msg.textContent = "❌ Network error";
    msg.style.color = "red";
  };

  xhr.send(formData);
}

/* ================= FORM BINDINGS ================= */
document.addEventListener("DOMContentLoaded", () => {

  const videoForm = document.getElementById("videoForm");
  const econtentForm = document.getElementById("econtentForm");

  if (videoForm) {
    videoForm.addEventListener("submit", function (e) {
      e.preventDefault();
      handleUpload(this);
    });
  }

  if (econtentForm) {
    econtentForm.addEventListener("submit", function (e) {
      e.preventDefault();
      handleUpload(this);
    });
  }

});
