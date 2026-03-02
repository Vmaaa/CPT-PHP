const PROTOCOL_API_URL = API_URL + "/student/status";
const PDF_VIEWER_API = API_URL + "/admin/pdf"; // Ajusta ruta

document.addEventListener("DOMContentLoaded", async () => {
  await loadProtocolStatus();
});

async function loadProtocolStatus() {
  const container = document.getElementById("protocol-container");
  container.innerHTML = `<div class="loading"><p>Cargando información...</p></div>`;

  try {
    const response = await CookieManager.fetchWithAuth(PROTOCOL_API_URL);
    if (!response.ok) throw new Error("Error de conexión");

    const result = await response.json();

    if (!result.hasProject) {
        if (result.needsConfiguration) {
            container.innerHTML = `
              <div style="text-align:center; padding:50px;">
                <h3>Configura tu perfil</h3>
                <p style="color:#666;">Debes agregar al menos un estudiante a tu cuenta en el apartado "Mi Cuenta" antes de subir un protocolo.</p>
              </div>`;
            return;
        }
        if (result.isUploadStageActive) {
            renderNoProject(container);
        } else {
            renderUploadStageClosed(container);
        }
        return;
    }

    renderProjectStatus(container, result);
  } catch (error) {
    console.error(error);
    container.innerHTML = `<p class="error-text">No se pudo cargar la información.</p>`;
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

function renderUploadStageClosed(container) {
    container.innerHTML = `
      <div style="text-align:center; padding:50px;">
        <i class="fas fa-calendar-times" style="color: #64748b; font-size: 3em; margin-bottom: 20px;"></i>
        <h3 style="color: #334155;">Periodo Cerrado</h3>
        <p style="color:#64748b; max-width: 400px; margin: 10px auto;">
          Actualmente no hay un periodo de registro de protocolos abierto para tu carrera y generación. 
          Por favor, mantente atento a los avisos de la coordinación o consulta el calendario académico.
        </p>
      </div>
    `;
}

function renderProjectStatus(container, data) {
  const { project, reviews, completedReviews, isSecondUploadStageActive, isFinalUploadStageActive } = data;

  const statusMap = {
    "PENDING": { text: "Pendiente de Asignación", class: "status-pending" },
    "UNDER_REVIEW": { text: "En Proceso de Revisión", class: "status-under_review" },
    "APPROVED": { text: "Protocolo Aprobado", class: "status-approved" },
    "REJECTED": { text: "No Aprobado / Requiere Correcciones", class: "status-rejected" },
    "FINAL_UNDER_REVIEW": { text: "En Revisión Final", class: "status-under_review" }, // <-- NUEVO
    "READY_TO_PRESENT": { text: "Listo para Presentar", class: "status-approved" }    // <-- NUEVO
  };
  
  const st = statusMap[project.status] || { text: project.status, class: "status-pending" };

  let mainAlertHtml = "";

  if (project.status === "REJECTED") {
    let reuploadActionHtml = "";
    if (isSecondUploadStageActive) {
        reuploadActionHtml = `
            <a href="/CPT/pages/protocolo_form.php" class="btn-reupload">
                <i class="fas fa-upload"></i> Subir Protocolo Corregido
            </a>
        `;
    } else {
        reuploadActionHtml = `
            <div style="margin-top: 15px; padding: 12px; background-color: #fef2f2; border: 1px solid #fca5a5; border-radius: 6px; color: #991b1b; font-size: 0.9em;">
                <i class="fas fa-calendar-times"></i> <strong>Periodo Cerrado:</strong> El sistema actualmente no está recibiendo correcciones de protocolos. Espera a que se habilite la etapa de "Segunda subida".
            </div>
        `;
    }

    mainAlertHtml = `
        <div class="rejected-alert">
            <div class="alert-icon"><i class="fas fa-exclamation-circle"></i></div>
            <div class="alert-content">
                <h4>Tu protocolo no fue aprobado</h4>
                <p>La mayoría de los revisores ha emitido un dictamen negativo. Por favor, revisa los comentarios y prepara tu nueva versión.</p>
                ${reuploadActionHtml}
            </div>
        </div>
      `;
  } else if (project.status === "APPROVED") {
    let finalUploadActionHtml = "";
    if (isFinalUploadStageActive) {
        finalUploadActionHtml = `
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #bbf7d0;">
                <p style="color: #166534; font-weight: 600; margin-bottom: 10px; font-size: 0.95em;">
                    El periodo para subir tu Trabajo Terminal Final está abierto.
                </p>
                <a href="/CPT/pages/documento_final_form.php" class="btn-primary" style="background-color: #16a34a; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; display: inline-block; font-size: 0.9em;">
                    <i class="fas fa-file-upload"></i> Subir Documento Final
                </a>
            </div>
        `;
    } else {
        finalUploadActionHtml = `
            <div style="margin-top: 15px; padding: 12px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; color: #475569; font-size: 0.85em;">
                <i class="fas fa-clock"></i> <strong>Periodo Cerrado:</strong> Aún no inicia el periodo para subir el Trabajo Terminal final. Mantente atento al calendario.
            </div>
        `;
    }

    mainAlertHtml = `
        <div class="approved-alert">
            <div class="alert-icon"><i class="fas fa-check-circle"></i></div>
            <div class="alert-content">
                <h4>¡Felicidades! Protocolo Aprobado</h4>
                <p>Tu proyecto ha cumplido con los criterios de evaluación. Ya puedes continuar con el desarrollo de tu Trabajo Terminal.</p>
                ${finalUploadActionHtml}
            </div>
        </div>
      `;
  } 
  else if (project.status === "FINAL_UNDER_REVIEW") {
      mainAlertHtml = `
        <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 15px; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 15px;">
            <i class="fas fa-info-circle" style="color: #3b82f6; font-size: 1.5rem; margin-top: 2px;"></i>
            <div>
                <h4 style="color: #1e3a8a; margin: 0 0 5px 0;">Documento en Revisión</h4>
                <p style="color: #1e40af; margin: 0; font-size: 0.9em;">Tu documento final está siendo evaluado por el jurado para determinar si está listo para ser presentado.</p>
            </div>
        </div>
      `;
  }

  const isFinalPhase = ["FINAL_UNDER_REVIEW", "READY_TO_PRESENT"].includes(project.status);
  const textApproved = isFinalPhase ? "Presentar" : "Aprobado";
  const textRejected = isFinalPhase ? "No Presentar" : "No Aprobado";

  const reviewsHtml = reviews.length
    ? reviews.map((r) => {
      let decisionHtml = `<span class="review-decision decision-waiting">Pendiente</span>`;
      let commentHtml = `<span style="color:#94a3b8">Esperando revisión...</span>`;
      let pdfBtn = "";

      if (r.grade !== null) {
        const isApproved = parseInt(r.grade) >= 1;
        
        decisionHtml = isApproved
          ? `<span class="review-decision decision-approved"><i class="fas fa-check"></i> ${textApproved}</span>`
          : `<span class="review-decision decision-rejected"><i class="fas fa-times"></i> ${textRejected}</span>`;

        commentHtml = r.comment ? `"${escapeHtml(r.comment)}"` : "Sin comentarios adicionales.";

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
