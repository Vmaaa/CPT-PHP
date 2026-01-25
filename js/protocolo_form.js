const PROTOCOL_API_URL = API_URL + "/student/protocol/";
const CAREER_API_URL = API_URL + "/career/";
const PROFESSOR_API = API_URL + "/professor/advisor";

document.addEventListener("DOMContentLoaded", () => {
  loadCareers();
  loadProfessors();

  document
    .getElementById("protocol-form")
    .addEventListener("submit", submitProtocol);
});

async function loadCareers() {
  const res = await CookieManager.fetchWithAuth(CAREER_API_URL);
  const data = await res.json();

  const select = document.getElementById("career-select");
  data.data.forEach((c) =>
    select.innerHTML += `<option value="${c.id_career}">${c.career}</option>`
  );
}

async function loadProfessors() {
  const res = await CookieManager.fetchWithAuth(PROFESSOR_API);
  const data = await res.json();

  const a1 = document.getElementById("advisor-1");
  const a2 = document.getElementById("advisor-2");

  data.data.forEach((p) => {
    const opt = `<option value="${p.id_professor}">${p.name}</option>`;
    a1.innerHTML += opt;
    a2.innerHTML += opt;
  });
}

async function submitProtocol(e) {
  e.preventDefault();

  const form = e.target;
  const msg = document.getElementById("form-message");
  const data = new FormData(form);

  if (data.get("advisor_1") === data.get("advisor_2")) {
    Swal.fire("Error", "Los asesores deben ser distintos", "error");
    return;
  }

  try {
    const res = await CookieManager.fetchWithAuth(PROTOCOL_API_URL, {
      method: "POST",
      body: data,
    });

    const result = await res.json();

    if (!res.ok) throw result;

    if (result.success) {
      Swal.fire({
        title: "¡Registrado!",
        text: "Tu protocolo se ha subido correctamente.",
        icon: "success",
        confirmButtonText: "Continuar",
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
