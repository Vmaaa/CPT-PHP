const ADMIN_PROJECTS_API = API_URL + "/admin/projects";
const ADMIN_ASSIGN_REVIEWERS_API = API_URL + "/admin/assign_reviewers/";
const PROFESSORS_API = API_URL + "/professor/advisor";
const ADMIN_PDF_API = API_URL + "/admin/pdf";

let cachedProfessors = null;

document.addEventListener("DOMContentLoaded", loadAdminProjects);

async function loadAdminProjects() {
  const container = document.getElementById("projects-container");
  container.innerHTML = "<p>Cargando proyectos...</p>";

  try {
    const res = await CookieManager.fetchWithAuth(ADMIN_PROJECTS_API);
    const data = await res.json();

    if (!res.ok) throw data;
    renderAdminProjects(data.data || []);
  } catch (err) {
    console.error(err);
    container.innerHTML = "<p>Error al cargar proyectos</p>";
  }
}

function renderAdminProjects(projects) {
  const container = document.getElementById("projects-container");
  container.innerHTML = "";

  if (!projects.length) {
    container.innerHTML = "<p>No hay proyectos registrados</p>";
    return;
  }

  const statusMap = {
    "PENDING": { text: "Pendiente", class: "status-pending" },
    "UNDER_REVIEW": { text: "En Revisión", class: "status-under_review" },
    "APPROVED": { text: "Aprobado", class: "status-approved" },
    "REJECTED": { text: "Rechazado", class: "status-rejected" },
  };

  projects.forEach((p) => {
    const card = document.createElement("div");
    card.className = "project-admin-card";

    let r1 = null, r2 = null, r3 = null;
    let hasReviewers = false;

    if (p.reviewers_ids) {
      const ids = p.reviewers_ids.split(",");
      r1 = ids[0] || null;
      r2 = ids[1] || null;
      r3 = ids[2] || null;
      if (r1 || r2 || r3) hasReviewers = true;
    }

    const btnText = hasReviewers ? "Editar revisores" : "Asignar revisores";
    const btnClass = hasReviewers
      ? "btn-action-secondary"
      : "btn-action-primary";
    const statusInfo = statusMap[p.status] ||
      { text: p.status, class: "status-pending" };

    card.innerHTML = `
      <div class="project-header">
        <h3>${escapeHtml(p.title)}</h3>
        <span class="project-status ${statusInfo.class}">${statusInfo.text}</span>
      </div>

      <p class="project-abstract">${escapeHtml(p.abstract)}</p>

      <div class="project-meta">
        <span><strong>Alumno:</strong> ${escapeHtml(p.student_name)}</span>
        <span><strong>Carrera:</strong> ${escapeHtml(p.career)}</span>
      </div>

      <div class="project-actions">
        ${
      p.file_url
        ? `<button onclick="openPdfModal(${p.id_final_project})" 
                 style="background:none; border:none; color:#2563eb; cursor:pointer; font-weight:500; font-size:0.9rem; padding:0; display:flex; align-items:center; gap:5px;">
                 <i class="fas fa-eye"></i> Ver PDF
               </button>`
        : "<span></span>"
    }
        <button class="${btnClass}" onclick="openAssignModal(${p.id_final_project}, '${r1}', '${r2}', '${r3}')">
          ${btnText}
        </button>
      </div>
    `;

    container.appendChild(card);
  });
}

function openPdfModal(projectId) {
  const modal = document.getElementById("pdfModal");
  const viewer = document.getElementById("pdfViewer");

  viewer.src = `${ADMIN_PDF_API}?id=${projectId}`;
  modal.style.display = "flex";
}

function closePdfModal() {
  const modal = document.getElementById("pdfModal");
  const viewer = document.getElementById("pdfViewer");

  modal.style.display = "none";
  viewer.src = "";
}

async function openAssignModal(projectId, r1, r2, r3) {
  document.getElementById("modal_project_id").value = projectId;
  const modal = document.getElementById("assignModal");
  modal.style.display = "flex";

  await loadProfessors();

  document.getElementById("reviewer1").value = (r1 && r1 !== "null") ? r1 : "";
  document.getElementById("reviewer2").value = (r2 && r2 !== "null") ? r2 : "";
  document.getElementById("reviewer3").value = (r3 && r3 !== "null") ? r3 : "";

  syncReviewerSelects();
}

function closeAssignModal() {
  document.getElementById("assignModal").style.display = "none";
  ["reviewer1", "reviewer2", "reviewer3"].forEach((id) => {
    document.getElementById(id).value = "";
  });
}

async function loadProfessors() {
  if (cachedProfessors) {
    renderProfessorOptions(cachedProfessors);
    return;
  }

  try {
    const res = await CookieManager.fetchWithAuth(PROFESSORS_API);
    const data = await res.json();
    cachedProfessors = data.data;
    renderProfessorOptions(cachedProfessors);
  } catch (e) {
    console.error("Error cargando profesores", e);
  }
}

function renderProfessorOptions(professors) {
  const select1 = document.getElementById("reviewer1");
  if (select1.options.length > 1) return;

  ["reviewer1", "reviewer2", "reviewer3"].forEach((id) => {
    const select = document.getElementById(id);
    const currentVal = select.value;

    select.innerHTML = `<option value="">Seleccione</option>`;

    professors.forEach((p) => {
      const opt = document.createElement("option");
      opt.value = p.id_professor;
      opt.textContent = p.name;
      select.appendChild(opt);
    });

    select.addEventListener("change", syncReviewerSelects);
    if (currentVal) select.value = currentVal;
  });
}

function syncReviewerSelects() {
  const r1 = document.getElementById("reviewer1");
  const r2 = document.getElementById("reviewer2");
  const r3 = document.getElementById("reviewer3");

  const values = [r1.value, r2.value, r3.value].filter((v) => v);

  [r1, r2, r3].forEach((select) => {
    const currentVal = select.value;

    [...select.options].forEach((opt) => {
      if (!opt.value) return;
      opt.disabled = false;
      if (values.includes(opt.value) && opt.value !== currentVal) {
        opt.disabled = true;
      }
    });
  });
}

async function saveReviewers() {
  const r1 = document.getElementById("reviewer1").value;
  const r2 = document.getElementById("reviewer2").value;
  const r3 = document.getElementById("reviewer3").value;
  const projectId = document.getElementById("modal_project_id").value;

  if (!r1 || !r2 || !r3) {
    Swal.fire("Atención", "Debes asignar los 3 revisores.", "warning");
    return;
  }

  if (new Set([r1, r2, r3]).size !== 3) {
    Swal.fire("Error", "No puedes repetir revisores.", "error");
    return;
  }

  const form = new FormData();
  form.append("id_final_project", projectId);
  form.append("reviewer1", r1);
  form.append("reviewer2", r2);
  form.append("reviewer3", r3);

  try {
    const res = await CookieManager.fetchWithAuth(ADMIN_ASSIGN_REVIEWERS_API, {
      method: "POST",
      body: form,
    });

    const data = await res.json();
    if (!res.ok) throw data;

    closeAssignModal();

    Swal.fire({
      title: "¡Asignados!",
      text: "Revisores guardados correctamente.",
      icon: "success",
      confirmButtonColor: "#3085d6",
    }).then(() => {
      loadAdminProjects();
    });
  } catch (err) {
    console.error(err);
    Swal.fire(
      "Error",
      err.error || "Error al conectar con el servidor",
      "error",
    );
  }
}

function escapeHtml(text) {
  return text
    ? text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
    : "";
}
