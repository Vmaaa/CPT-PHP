const PROTOCOL_API_URL = API_URL + "/student/status";
const PDF_VIEWER_API = API_URL + "/admin/pdf"; // Ajusta ruta

document.addEventListener("DOMContentLoaded", async () => {
  await loadProtocolStatus();
});

async function loadProtocolStatus() {
  const container = document.getElementById("protocol-container");
  container.innerHTML =
    `<div class="loading"><p>Cargando proyecto...</p></div>`;

  try {
    const response = await CookieManager.fetchWithAuth(PROTOCOL_API_URL);
    if (!response.ok) throw new Error("Error de conexión");

    const result = await response.json();

    if (!result.hasProject) {
      renderNoProject(container);
      return;
    }

    renderProjectStatus(container, result);
  } catch (error) {
    console.error(error);
    container.innerHTML =
      `<p class="error-text">No se pudo cargar la información.</p>`;
  }
}

function renderNoProject(container) {
  container.innerHTML = `
    <div style="text-align:center; padding:50px;">
      <h3>Aún no has registrado tu protocolo</h3>
      <p style="color:#666; margin-bottom:20px;">Sube tu archivo para iniciar el proceso de revisión.</p>
      <a href="/CPT/pages/protocolo_form.php" class="btn btn-primary" style="background:#4f46e5; color:white; padding:10px 20px; text-decoration:none; border-radius:6px;">
        Iniciar Registro
      </a>
    </div>
  `;
}

function renderProjectStatus(container, data) {
  const { project, reviews, completedReviews } = data;

  const statusMap = {
    "PENDING": { text: "Pendiente de Asignación", class: "status-pending" },
    "UNDER_REVIEW": {
      text: "En Proceso de Revisión",
      class: "status-under_review",
    },
    "APPROVED": { text: "Protocolo Aprobado", class: "status-approved" },
    "REJECTED": {
      text: "No Aprobado / Requiere Correcciones",
      class: "status-rejected",
    },
  };
  const st = statusMap[project.status] ||
    { text: project.status, class: "status-pending" };

  let mainAlertHtml = "";

  if (project.status === "REJECTED") {
    mainAlertHtml = `
        <div class="rejected-alert">
            <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
            <div class="alert-content">
                <h4>Tu protocolo no fue aprobado</h4>
                <p>La mayoría de los revisores ha emitido un dictamen negativo. Por favor, revisa los comentarios y sube una nueva versión.</p>
                <a href="/CPT/pages/protocolo_form.php" class="btn-reupload">
                    <i class="fas fa-upload"></i> Subir Protocolo Corregido
                </a>
            </div>
        </div>
      `;
  } else if (project.status === "APPROVED") {
    mainAlertHtml = `
        <div class="approved-alert">
            <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
            <div class="alert-content">
                <h4>¡Felicidades! Protocolo Aprobado</h4>
                <p>Tu proyecto ha cumplido con los criterios de evaluación. Ya puedes continuar con la siguiente etapa de tu Trabajo Terminal.</p>
            </div>
        </div>
      `;
  }

  const reviewsHtml = reviews.length
    ? reviews.map((r) => {
      // Determinar estado individual
      let decisionHtml =
        `<span class="review-decision decision-waiting">Pendiente</span>`;
      let commentHtml =
        `<span style="color:#94a3b8">Esperando revisión...</span>`;
      let pdfBtn = "";

      // Si hay comentario (asumimos que ya revisó)
      if (r.grade !== null) {
        const isApproved = parseInt(r.grade) >= 1;
        decisionHtml = isApproved
          ? `<span class="review-decision decision-approved"><i class="fas fa-check"></i> Aprobado</span>`
          : `<span class="review-decision decision-rejected"><i class="fas fa-times"></i> No Aprobado</span>`;

        commentHtml = r.comment
          ? `"${escapeHtml(r.comment)}"`
          : "Sin comentarios adicionales.";

        if (r.reviewer_pdf_url) {
          pdfBtn = `
                  <div class="review-footer">
                      <button class="btn-dictamen" onclick="openPdf('review', ${project.id_final_project}, ${r.id_professor})">
                          <i class="fas fa-file-pdf"></i> Ver Dictamen Firmado
                      </button>
                  </div>
              `;
        }
      }

      return `
        <li class="review-item">
           <div class="review-header">
               <span class="prof-name">${escapeHtml(r.professor_name)}</span>
               ${decisionHtml}
           </div>
           <div class="review-comment">${commentHtml}</div>
           ${pdfBtn}
        </li>
      `;
    }).join("")
    : `<p style="text-align:center; color:#64748b;">Aún no se han asignado revisores.</p>`;

  container.innerHTML = `
    <div class="student-card">
       <div class="project-header">
           <div class="project-title-row">
               <h2>${escapeHtml(project.title)}</h2>
               <span class="status-badge ${st.class}">${st.text}</span>
           </div>
           
           <button class="btn-my-pdf" onclick="openPdf('student', ${project.id_final_project})">
               <i class="fas fa-eye"></i> Ver mi archivo subido
           </button>
       </div>
        ${mainAlertHtml}
       <div class="reviews-section">
           <div class="reviews-header">
               <h3>Dictámenes de Revisores</h3>
               <span class="progress-pill">${completedReviews} / 3 Completados</span>
           </div>
           
           <ul class="reviews-list">
               ${reviewsHtml}
           </ul>
       </div>
    </div>
  `;
}

window.openPdf = function (type, id, profId = 0) {
  const modal = document.getElementById("pdfModal");
  const viewer = document.getElementById("pdfViewer");

  let url = `${PDF_VIEWER_API}?type=${type}&id=${id}`;
  if (type === "review") url += `&prof_id=${profId}`;

  viewer.src = url;
  modal.style.display = "flex";
};

window.closePdfModal = function () {
  const modal = document.getElementById("pdfModal");
  const viewer = document.getElementById("pdfViewer");
  modal.style.display = "none";
  viewer.src = "";
};

function escapeHtml(text) {
  return text
    ? text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
    : "";
}
