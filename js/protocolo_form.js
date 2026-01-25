const PROTOCOL_API_URL = API_URL + "/student/protocol/";
const STATUS_API_URL = API_URL + "/student/status/"; // Endpoint para checar datos previos
const CAREER_API_URL = API_URL + "/career/";
const PROFESSOR_API = API_URL + "/professor/advisor";

document.addEventListener("DOMContentLoaded", async () => {
  await Promise.all([loadCareers(), loadProfessors()]);
  await checkExistingProject();

  document
    .getElementById("protocol-form")
    .addEventListener("submit", submitProtocol);
});

async function loadCareers() {
  try {
    const res = await CookieManager.fetchWithAuth(CAREER_API_URL);
    const data = await res.json();
    const select = document.getElementById("career-select");
    select.innerHTML =
      '<option value="" disabled selected>Elige tu carrera</option>';
    data.data.forEach((c) =>
      select.innerHTML += `<option value="${c.id_career}">${c.career}</option>`
    );
  } catch (e) {
    console.error("Error cargando carreras", e);
  }
}

async function loadProfessors() {
  try {
    const res = await CookieManager.fetchWithAuth(PROFESSOR_API);
    const data = await res.json();
    const a1 = document.getElementById("advisor-1");
    const a2 = document.getElementById("advisor-2");

    let options = '<option value="" disabled selected>Seleccionar...</option>';
    data.data.forEach((p) => {
      options += `<option value="${p.id_professor}">${p.name}</option>`;
    });

    a1.innerHTML = options;
    a2.innerHTML = options;
  } catch (e) {
    console.error("Error cargando profesores", e);
  }
}

async function checkExistingProject() {
  try {
    const res = await CookieManager.fetchWithAuth(STATUS_API_URL);
    if (!res.ok) return; // Si falla o es 404, asumimos que es nuevo

    const data = await res.json();
    if (data.hasProject && data.project) {
      const p = data.project;

      const titleInput = document.querySelector("input[name='title']");
      const abstractInput = document.querySelector("textarea[name='abstract']");

      if (titleInput) titleInput.value = p.title || "";
      if (abstractInput) abstractInput.value = p.abstract || "";

      const careerSelect = document.getElementById("career-select");
      if (careerSelect && p.id_career) careerSelect.value = p.id_career;

      const submitBtn = document.querySelector("button[type='submit']");
      if (submitBtn) {
        submitBtn.innerHTML =
          "<i class='fas fa-sync'></i> Actualizar Protocolo y Enviar";
      }
    }
  } catch (error) {
    console.log("Usuario nuevo o error al verificar status:", error);
  }
}

async function submitProtocol(e) {
  e.preventDefault();

  const form = e.target;
  const data = new FormData(form);

  if (data.get("advisor_1") === data.get("advisor_2")) {
    Swal.fire("Error", "Los asesores deben ser distintos", "error");
    return;
  }

  Swal.fire({ title: "Subiendo...", didOpen: () => Swal.showLoading() });

  try {
    const res = await CookieManager.fetchWithAuth(PROTOCOL_API_URL, {
      method: "POST",
      body: data,
    });

    const result = await res.json();

    if (!res.ok) throw result;

    if (result.success) {
      Swal.fire({
        title: "¡Listo!",
        text: "Tu protocolo ha sido recibido exitosamente.",
        icon: "success",
        confirmButtonText: "Ver Estado",
        confirmButtonColor: "#1a237e",
      }).then((result) => {
        if (result.isConfirmed || result.isDismissed) {
          window.location.href = "/CPT/pages/projects_student.php";
        }
      });
    }
  } catch (err) {
    console.error(err);
    Swal.fire("Error", err.error || "Error al registrar protocolo", "error");
  }
}
