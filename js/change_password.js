(() => {
  const passwordInput = document.getElementById("password");
  const passwordConfirmInput = document.getElementById("password-confirm");
  const submitBtn = document.getElementById("change-password-btn");
  const backBtn = document.getElementById("back-btn");
  if (!(passwordInput && passwordConfirmInput && submitBtn)) return;

  const params = new URLSearchParams(window.location.search);
  const recovery_token = params.get("recovery_token");
  const show_alert = params.get("show_alert") === "true";

  const showMessage = (opts) => {
    SwalMessage(opts);
  };

  if (!is_logged_in && !recovery_token) {
    showMessage({
      icon: "warning",
      title: "Sin permiso",
      text: "No tienes permiso para estar aquí.",
    });
    setTimeout(() => {
      window.location.href = `${window.APP_BASE_URL}/index.php`;
    }, 3000);
    return;
  }
  if (show_alert === true) {
    showMessage({
      icon: "info",
      title: "Cambia tu contraseña",
      text: "Es tu primer inicio de sesión",
    });
  }

  if (backBtn && !recovery_token) {
    backBtn.style.display = "inline-block";
  }

  const rulesEls = {
    len: document.querySelector('[data-rule="len"]'),
    digit: document.querySelector('[data-rule="digit"]'),
    upper: document.querySelector('[data-rule="upper"]'),
    lower: document.querySelector('[data-rule="lower"]'),
    symbol: document.querySelector('[data-rule="symbol"]'),
    match: document.querySelector('[data-rule="match"]'),
  };

  function getPolicyStatus(p, c) {
    return {
      len: p.length >= 8,
      digit: /[0-9]/.test(p),
      upper: /[A-Z]/.test(p),
      lower: /[a-z]/.test(p),
      symbol: /[\W_]/.test(p),
      match: p.length > 0 && c.length > 0 && p === c,
    };
  }

  function renderPolicy(p, c) {
    const st = getPolicyStatus(p, c);
    Object.entries(st).forEach(([k, ok]) => {
      rulesEls[k]?.classList.toggle("ok", !!ok);
      rulesEls[k]?.setAttribute("aria-checked", ok ? "true" : "false");
    });
  }

  const repaint = () =>
    renderPolicy(passwordInput.value, passwordConfirmInput.value);
  passwordInput.addEventListener("input", repaint);
  passwordConfirmInput.addEventListener("input", repaint);
  repaint();
  document.addEventListener("click", (e) => {
    const eye = e.target.closest(".input-eye");
    if (!eye) return;
    const sel = eye.getAttribute("data-toggle");
    const input = document.querySelector(sel);
    if (!input) return;
    const isPwd = input.type === "password";
    input.type = isPwd ? "text" : "password";
    eye.classList.toggle("fa-eye");
    eye.classList.toggle("fa-eye-slash");
  });

  submitBtn.addEventListener("click", async function () {
    const p = passwordInput.value.trim();
    const c = passwordConfirmInput.value.trim();
    if (!p || !c) {
      showMessage({
        icon: "info",
        title: "Por favor completa todos los campos.",
      });
      return;
    }
    if (p !== c) {
      showMessage({
        icon: "error",
        title: "Las contraseñas no coinciden",
      });
      return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

    try {
      const formData = new FormData();
      formData.append("new_password", p);
      if (recovery_token) formData.append("recovery_token", recovery_token);
      const response = await CookieManager.fetchWithAuth(
        `${window.APP_BASE_URL}/api/v1/account/change_password/`,
        {
          method: "POST",
          body: formData,
        },
      );
      const raw = await response.text();
      let data;
      try {
        data = JSON.parse(raw);
      } catch {
        data = { error: raw };
      }

      if (response.ok && data.success) {
        showMessage({
          icon: "success",
          title: "Contraseña cambiada",
          text:
            "Ahora puedes iniciar sesión. Serás redirigido a la página de inicio.",
        });
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Contraseña guardada';
        setTimeout(() => {
          window.location.href = `${window.APP_BASE_URL}/index.php?`;
        }, 2000);
      } else {
        const msg = data.error || "No se pudo cambiar la contraseña.";
        showMessage({
          icon: "error",
          title: "Error",
          text: msg,
        });
        submitBtn.disabled = false;
        submitBtn.innerHTML =
          '<i class="fas fa-save"></i> Guardar nueva contraseña';
      }
    } catch (e) {
      console.error("Error:", e);
      showMessage({
        icon: "error",
        title: "Error de conexión",
        text: "No fue posible contactar al servidor.",
      });
      submitBtn.disabled = false;
      submitBtn.innerHTML =
        '<i class="fas fa-save"></i> Guardar nueva contraseña';
    }
  });
})();
