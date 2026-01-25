const PROFESSOR_PROJECTS_API = API_URL + "/professor/reviews";
const SUBMIT_REVIEW_API = API_URL + "/professor/submit_review/";

document.addEventListener("DOMContentLoaded", loadProfessorProjects);

async function loadProfessorProjects() {
  const container = document.getElementById("projects-container");
  container.innerHTML = "<p>Cargando...</p>";

  const res = await CookieManager.fetchWithAuth(PROFESSOR_PROJECTS_API);
  const data = await res.json();

  container.innerHTML = "";

  if (!data.data?.length) {
    container.innerHTML = "<p>No tienes proyectos asignados</p>";
    return;
  }

  data.data.forEach((p) => {
    const card = document.createElement("div");
    card.className = "professor-project-card";

    card.innerHTML = `
      <h3>${escapeHtml(p.title)}</h3>
      <p><b>Alumno:</b> ${escapeHtml(p.student_name)}</p>
      <p><b>Carrera:</b> ${escapeHtml(p.career)}</p>
      <p><b>Estado:</b> ${p.status}</p>

      <a href="${p.file_url}" target="_blank">Ver PDF</a>

      <select id="decision_${p.id_fp_change_review}">
        <option value="">Selecciona dictamen</option>
        <option value="APPROVED">Aprobado</option>
        <option value="REJECTED">No aprobado</option>
      </select>

      <textarea id="comments_${p.id_fp_change_review}" placeholder="Comentarios">${
      p.comments || ""
    }</textarea>

      <button onclick="submitReview(${p.id_fp_change_review})">
        Enviar dictamen
      </button>
    `;

    container.appendChild(card);
  });
}

async function submitReview(id) {
  const decision = document.getElementById(`decision_${id}`).value;
  const comments = document.getElementById(`comments_${id}`).value;

  if (!decision) return alert("Selecciona un dictamen");

  const form = new FormData();
  form.append("id_fp_change_review", id);
  form.append("decision", decision);
  form.append("comments", comments);

  const res = await CookieManager.fetchWithAuth(SUBMIT_REVIEW_API, {
    method: "POST",
    body: form,
  });

  const data = await res.json();

  if (!res.ok) return alert(data.error);

  alert("Dictamen enviado");
  loadProfessorProjects();
}

function escapeHtml(t) {
  return t
    ? t.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
    : "";
}
