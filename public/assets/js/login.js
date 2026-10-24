document.addEventListener("DOMContentLoaded", () => {
  console.log("LOGIN JS BERJALAN");
  initLogin();
});

function initLogin() {
  showPassword();

  loginValidation();

  loginButtonEffect();

  autoFocus();

  keyboardShortcut();
}

function showPassword() {
  const password = document.getElementById("password");

  const button = document.querySelector(".toggle-password");

  const icon = button.querySelector("i");

  if (!password || !button) return;

  button.addEventListener("click", () => {
    password.type = password.type === "password" ? "text" : "password";

    icon.classList.toggle("fa-eye");

    icon.classList.toggle("fa-eye-slash");
  });
}

function autoFocus() {
  const username = document.querySelector("input[name='username']");

  if (username) {
    username.focus();
  }
}

function keyboardShortcut() {
  document.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
      document.querySelector("form").requestSubmit();
    }
  });
}

function loginValidation() {
  const form = document.querySelector("form");

  form.addEventListener("submit", (e) => {
     console.log("FORM SUBMIT");
    e.preventDefault();

    validate(form);
  });
}

function validate(form) {
  const username = form.username;

  const password = form.password;

  if (username.value.trim() === "") {
    showError(username, "Username wajib diisi");

    return;
  }

  if (password.value.trim() === "") {
    showError(password, "Password wajib diisi");

    return;
  }

  loadingLogin(form);
}

function showError(input, message) {
  input.focus();

  input.classList.add("shake");

  input.placeholder = message;

  setTimeout(() => {
    input.classList.remove("shake");
  }, 500);
}

function loadingLogin(form) {
  const button = form.querySelector("button[type='submit']");

  button.disabled = true;

  button.innerHTML = `

        <i class="fa-solid fa-spinner fa-spin"></i>

        Signing In...

    `;

  setTimeout(() => {
    form.submit();

    button.innerHTML = `

        <i class="fa-solid fa-check"></i>

        Success

        `;
  }, 1200);
}

function loginButtonEffect() {
  const button = document.querySelector(".btn-primary");

  if (!button) return;

  button.addEventListener("click", function (e) {
    const circle = document.createElement("span");

    circle.classList.add("ripple");

    const rect = this.getBoundingClientRect();

    circle.style.left = e.clientX - rect.left + "px";

    circle.style.top = e.clientY - rect.top + "px";

    this.appendChild(circle);

    setTimeout(() => {
      circle.remove();
    }, 700);
  });
}
