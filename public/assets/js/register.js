document.addEventListener("DOMContentLoaded", () => {
  initPasswordToggle();
  initPhotoUpload();
  initConfirmPassword();
  // kalau memang sudah ada
  if (typeof initPasswordStrength === "function") {
    initPasswordStrength();
  }

  if (typeof initValidation === "function") {
    initValidation();
  }

  if (typeof initRegisterSubmit === "function") {
    initRegisterSubmit();
  }
});

/* ==========================================
   PASSWORD TOGGLE
========================================== */

function initPasswordToggle() {
  const buttons = document.querySelectorAll(".toggle-password");

  buttons.forEach((button) => {
    button.addEventListener("click", () => {
      const target = document.getElementById(button.dataset.target);

      const icon = button.querySelector("i");

      if (!target) return;

      if (target.type === "password") {
        target.type = "text";

        icon.classList.replace("fa-eye", "fa-eye-slash");
      } else {
        target.type = "password";

        icon.classList.replace("fa-eye-slash", "fa-eye");
      }
    });
  });
}

/* ==========================================
   PHOTO UPLOAD
========================================== */

function initPhotoUpload() {
  const input = document.getElementById("foto");
  const uploadArea = document.getElementById("uploadArea");

  const uploadContent = document.getElementById("uploadContent");
  const uploadPreview = document.getElementById("uploadPreview");

  const previewImage = document.getElementById("previewImage");
  const fileName = document.getElementById("fileName");
  const fileSize = document.getElementById("fileSize");

  const removePhoto = document.getElementById("removePhoto");

  if (!input || !uploadArea) return;

  /* ==============================
       PILIH FILE
    ============================== */

  input.addEventListener("change", () => {
    if (!input.files.length) return;

    loadFile(input.files[0]);
  });

  /* ==============================
       DRAG OVER
    ============================== */

  uploadArea.addEventListener("dragover", (e) => {
    e.preventDefault();

    uploadArea.classList.add("dragover");
  });

  uploadArea.addEventListener("dragleave", () => {
    uploadArea.classList.remove("dragover");
  });

  /* ==============================
       DROP
    ============================== */

  uploadArea.addEventListener("drop", (e) => {
    e.preventDefault();

    uploadArea.classList.remove("dragover");

    const file = e.dataTransfer.files[0];

    if (!file) return;

    const dataTransfer = new DataTransfer();

    dataTransfer.items.add(file);

    input.files = dataTransfer.files;

    loadFile(file);
  });

  /* ==============================
       HAPUS FOTO
    ============================== */

  removePhoto.addEventListener("click", (e) => {
    e.preventDefault();

    input.value = "";

    previewImage.src = "";

    fileName.textContent = "";

    fileSize.textContent = "";

    uploadPreview.style.display = "none";

    uploadContent.style.display = "flex";
  });

  /* ==============================
       LOAD FILE
    ============================== */

  function loadFile(file) {
    const allowed = ["image/jpeg", "image/png"];

    if (!allowed.includes(file.type)) {
      alert("Format file harus JPG, JPEG atau PNG.");

      input.value = "";

      return;
    }

    if (file.size > 2 * 1024 * 1024) {
      alert("Ukuran file maksimal 2 MB.");

      input.value = "";

      return;
    }

    const reader = new FileReader();

    reader.onload = function (e) {
      previewImage.src = e.target.result;
    };

    reader.readAsDataURL(file);

    fileName.textContent = file.name;

    if (file.size >= 1024 * 1024) {
      fileSize.textContent = (file.size / (1024 * 1024)).toFixed(2) + " MB";
    } else {
      fileSize.textContent = (file.size / 1024).toFixed(1) + " KB";
    }

    uploadContent.style.display = "none";

    uploadPreview.style.display = "flex";
  }
}

/* ==========================================
   PASSWORD STRENGTH
========================================== */

function initPasswordStrength() {
  const password = document.getElementById("password");

  const progress = document.getElementById("strengthProgress");

  const label = document.getElementById("strengthLabel");

  password.addEventListener("input", updateStrength);

  function updateStrength() {
    const value = password.value;

    let score = 0;

    const rules = {
      ruleLength: value.length >= 8,

      ruleUpper: /[A-Z]/.test(value),

      ruleLower: /[a-z]/.test(value),

      ruleNumber: /[0-9]/.test(value),

      ruleSymbol: /[^A-Za-z0-9]/.test(value),
    };

    Object.keys(rules).forEach((id) => {
      toggleRule(id, rules[id]);

      if (rules[id]) score++;
    });

    const percent = score * 20;

    progress.style.width = percent + "%";

    if (value.length === 0) {
      progress.style.width = "0%";

      progress.style.background = "#E2E8F0";

      label.innerHTML = "Password belum diisi";

      return;
    }

    if (score <= 2) {
      progress.style.background = "#EF4444";

      label.innerHTML = "Weak Password";
    } else if (score == 3) {
      progress.style.background = "#F59E0B";

      label.innerHTML = "Fair Password";
    } else if (score == 4) {
      progress.style.background = "#0EA5E9";

      label.innerHTML = "Good Password";
    } else {
      progress.style.background = "#16A34A";

      label.innerHTML = "Strong Password";
    }
  }
}

/* ==========================================
   TOGGLE PASSWORD RULE
========================================== */

function toggleRule(id, valid) {
  const item = document.getElementById(id);

  if (!item) return;

  const text = item.textContent.substring(2);

  item.classList.remove("valid", "invalid");

  item.classList.add(valid ? "valid" : "invalid");

  item.innerHTML = (valid ? "✔ " : "✖ ") + text;
}

/* ==========================================
   CONFIRM PASSWORD
========================================== */

function initConfirmPassword() {
  const password = document.getElementById("password");

  const confirm = document.getElementById("confirm_password");

  const message = document.getElementById("confirmMessage");

  function checkPassword() {
    if (confirm.value === "") {
      message.innerHTML = "";

      message.className = "confirm-message";

      return;
    }

    if (confirm.value === password.value) {
      message.innerHTML = "✔ Password cocok";

      message.className = "confirm-message confirm-success";
    } else {
      message.innerHTML = "✖ Password tidak cocok";

      message.className = "confirm-message confirm-error";
    }
  }

  password.addEventListener("input", checkPassword);

  confirm.addEventListener("input", checkPassword);
}

/* ==========================================
   DISABLE DOUBLE SUBMIT
========================================== */

function initRegisterSubmit() {
  const form = document.getElementById("registerForm");

  const button = form.querySelector("button[type='submit']");

  form.addEventListener("submit", () => {
    button.disabled = true;

    button.innerHTML = `
            <i class="fa-solid fa-spinner fa-spin"></i>
            Creating Account...
        `;
  });
}
