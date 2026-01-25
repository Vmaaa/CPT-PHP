const ADMIN_PROJECTS_API = API_URL + "/admin/projects";
const ADMIN_ASSIGN_REVIEWERS_API = API_URL + "/admin/assign_reviewers/";
const PROFESSORS_API = API_URL + "/professor/advisor";

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

  projects.forEach((p) => {
    const card = document.createElement("div");
    card.className = "project-admin-card";

    card.innerHTML = `
      <div class="project-header">
        <h3>${escapeHtml(p.title)}</h3>
        <span class="project-status ${p.status.toLowerCase()}">${p.status}</span>
      </div>

      <p class="project-abstract">${escapeHtml(p.abstract)}</p>

      <div class="project-meta">
        <span><strong>Alumno:</strong> ${escapeHtml(p.student_name)}</span>
        <span><strong>Carrera:</strong> ${escapeHtml(p.career)}</span>
      </div>

      <div class="project-actions">
        ${
      p.file_url ? `<a href="${p.file_url}" target="_blank">Ver PDF</a>` : ""
    }
        <button class="btn-assign" onclick="openAssignModal(${p.id_final_project})">
          Asignar revisores
        </button>
      </div>
    `;

    container.appendChild(card);
  });
}

function openAssignModal(projectId) {
  document.getElementById("modal_project_id").value = projectId;
  document.getElementById("assignModal").style.display = "flex";
  loadProfessors();
}

function closeAssignModal() {
  document.getElementById("assignModal").style.display = "none";
}

async function loadProfessors() {
  const res = await CookieManager.fetchWithAuth(PROFESSORS_API);
  const data = await res.json();

  ["reviewer1", "reviewer2", "reviewer3"].forEach((id) => {
    const select = document.getElementById(id);
    select.innerHTML = `<option value="">Seleccione</option>`;

    data.data.forEach((p) => {
      const opt = document.createElement("option");
      opt.value = p.id_professor;
      opt.textContent = p.name;
      select.appendChild(opt);
    });

    // Escuchar cambios para bloquear duplicados
    select.addEventListener("change", syncReviewerSelects);
  });
}

function syncReviewerSelects() {
  const values = {
    reviewer1: reviewer1.value,
    reviewer2: reviewer2.value,
    reviewer3: reviewer3.value,
  };

  ["reviewer1", "reviewer2", "reviewer3"].forEach((id) => {
    const select = document.getElementById(id);

    [...select.options].forEach((opt) => {
      if (!opt.value) return;

      opt.disabled = Object.entries(values).some(
        ([key, val]) => key !== id && val === opt.value,
      );
    });
  });
}

async function saveReviewers() {
  const r1 = reviewer1.value;
  const r2 = reviewer2.value;
  const r3 = reviewer3.value;
  const projectId = document.getElementById("modal_project_id").value;

  /* Validaciones frontend */
  if (!r1 || !r2 || !r3) {
    alert("Debes seleccionar los 3 revisores");
    return;
  }

  if (new Set([r1, r2, r3]).size !== 3) {
    alert("No puedes seleccionar el mismo revisor más de una vez");
    return;
  }

  const form = new FormData();
  form.append("id_final_project", projectId);
  form.append("reviewer1", r1);
  form.append("reviewer2", r2);
  form.append("reviewer3", r3);

  try {
    const res = await CookieManager.fetchWithAuth(
      ADMIN_ASSIGN_REVIEWERS_API,
      {
        method: "POST",
        body: form,
      },
    );

    const data = await res.json();

    if (!res.ok) throw data;

    closeAssignModal();
    loadAdminProjects();

    if (typeof SwalMessage !== "undefined") {
      SwalMessage({
        title: "Éxito",
        text: "Revisores asignados.",
        icon: "success",
      });
    } else {
      alert("Revisores asignados correctamente");
    }
  } catch (err) {
    console.error(err);
    alert(err.error || "Error al asignar revisores");
  }
}

/* ===== UTIL ===== */
function escapeHtml(text) {
  return text
    ? text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
    : "";
}
