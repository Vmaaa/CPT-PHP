document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".modal-backdrop").forEach((backdrop) => {
    if (backdrop.id) lockBackdrop(backdrop);
  });

  const loginBtn = document.getElementById("login-btn");
  if (loginBtn) {
    const params = new URLSearchParams(window.location.search);
    const show_alert = params.get("show_alert") === "true";
    if (show_alert) {
      SwalMessage(
        {
          title: "Cuenta inactiva",
          text:
            "Tu cuenta fue desactivada. Por favor, contacta al administrador.",
          icon: "error",
        },
      );
      const url = new URL(window.location.href);
      url.search = "";
      window.history.replaceState({}, document.title, url.toString());
    }

    loginBtn.addEventListener("click", async function () {
      const email = document.getElementById("email").value.trim();
      const password = document.getElementById("password").value;
      if (!email || !password) {
        SwalMessage(
          {
            title: "Campos incompletos",
            text: "Por favor, completa todos los campos.",
            icon: "warning",
          },
        );
        return;
      }
      loginBtn.disabled = true;
      loginBtn.innerHTML =
        '<i class="fas fa-spinner fa-spin"></i> Iniciando...';
      try {
        const formData = new FormData();
        formData.append("acco_email", email);
        formData.append("acco_password", password);
        const response = await fetch(
          `${window.APP_BASE_URL || ""}/api/v1/account/login/`,
          { method: "POST", body: formData },
        );
        const data = await response.json();
        console.log(data);
        if (response.ok && data.success) {
          window.location.href = `${
            window.APP_BASE_URL || ""
          }/pages/dashboard.php`;
        } else {
          SwalMessage(
            {
              title: "Error de inicio de sesión",
              text: data.error ||
                "Credenciales inválidas. Por favor, intenta nuevamente.",
              icon: "error",
            },
          );
        }
      } catch (_e) {
        SwalMessage(
          {
            title: "Error de conexión",
            text:
              "No se pudo conectar con el servidor. Por favor, intenta más tarde.",
            icon: "error",
          },
        );
      } finally {
        loginBtn.disabled = false;
        loginBtn.innerHTML =
          '<i class="fas fa-sign-in-alt"></i> Iniciar Sesión';
      }
    });

    const pwdInput = document.getElementById("password");
    if (pwdInput) {
      pwdInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") loginBtn.click();
      });
    }
  }
});
