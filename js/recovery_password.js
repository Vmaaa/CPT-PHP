const recoveryBtn = document.getElementById("recovery-btn");
const emailInput = document.getElementById("email");

if (recoveryBtn && emailInput) {
  const showMessage = (opts) => {
    SwalMessage(opts);
  };

  recoveryBtn.addEventListener("click", async function () {
    const email = emailInput.value.trim();
    if (!email) {
      showMessage({
        title: "Datos incompletos",
        text: "Por favor completa todos los campos",
        icon: "warning",
      });
      return;
    }
    recoveryBtn.disabled = true;
    recoveryBtn.innerHTML =
      '<i class="fas fa-spinner fa-spin"></i> Enviando correo...';
    try {
      const formData = new FormData();
      formData.append("acco_email", email);
      const response = await fetch(
        `${window.APP_BASE_URL}/api/v1/account/recovery/`,
        { method: "POST", body: formData },
      );
      const data = await response.json();
      if (response.ok && data.success) {
        showMessage({
          title: "Éxito",
          text:
            "Se envió un correo al correo asociado a tu cuenta, sigue los pasos para cambiar de contraseña",
          icon: "success",
        });
        window.location.href = `${window.APP_BASE_URL}/pages/dashboard.php`;
      } else {
        showMessage({
          title: "Error de recuperación",
          text: data.error || "Error al momento de recuperar la cuenta",
          icon: "error",
        });
      }
    } catch (e) {
      console.error("Error:", e);
      showMessage({
        title: "Error de conexión",
        text: "Error de conexión con el servidor",
        icon: "error",
      });
    } finally {
      recoveryBtn.disabled = false;
      recoveryBtn.innerHTML =
        '<i class="fas fa-sign-in-alt"></i> Enviar correo de recuperación';
    }
  });

  emailInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter") recoveryBtn.click();
  });
}
