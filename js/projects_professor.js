const PROFESSOR_PROJECTS_API = API_URL + "/professor/reviews";
const SUBMIT_REVIEW_API = API_URL + "/professor/submit_review/";
const PROFESSOR_PDF_API = API_URL + "/admin/pdf";

document.addEventListener("DOMContentLoaded", loadProfessorProjects);

async function loadProfessorProjects() {
  const container = document.getElementById("projects-container");
  container.innerHTML = "<p>Cargando revisiones...</p>";

  try {
    const res = await CookieManager.fetchWithAuth(PROFESSOR_PROJECTS_API);
    const data = await res.json();
    container.innerHTML = "";

    if (!data.data?.length) {
      container.innerHTML =
        "<p style='grid-column: 1/-1; text-align:center'>No tienes proyectos asignados.</p>";
      return;
    }

    const projects = data.data.sort((a, b) => {
      const aReviewed = (a.grade !== null && a.grade !== undefined) ? 1 : 0;
      const bReviewed = (b.grade !== null && b.grade !== undefined) ? 1 : 0;
      return aReviewed - bReviewed;
    });

    const statusMap = {
      "PENDING": { text: "Pendiente", class: "status-pending" },
      "UNDER_REVIEW": { text: "En Revisión", class: "status-under-review" },
      "APPROVED": { text: "Aprobado", class: "status-approved" },
      "REJECTED": { text: "Rechazado", class: "status-rejected" },
    };

    projects.forEach((p) => {
      const card = document.createElement("div");

      const isReviewed = p.grade !== null && p.grade !== undefined;

      let prevDecision = "";
      if (isReviewed) {
        prevDecision = (p.grade >= 1) ? "APPROVED" : "REJECTED";
      }

      const statusInfo = statusMap[p.status] ||
        { text: p.status, class: "status-pending" };

      card.className = "professor-card";
      if (isReviewed) {
        card.style.borderLeft = "5px solid #15803d";
      }

      const disabledAttr = isReviewed ? "disabled" : "";
      const bgFooter = isReviewed ? "background-color:#f0fdf4;" : "";

      card.innerHTML = `
          <div class="card-header">
             <div style="display:flex; align-items:center; gap:10px;">
                 <h3>${escapeHtml(p.title)}</h3>
                 ${
        isReviewed ? `<span class="badge-sent">✔ ENVIADO</span>` : ""
      }
             </div>
             <span class="status-badge ${statusInfo.class}">${statusInfo.text}</span>
          </div>

          <div class="card-body">
             <div class="student-row">
                <div>
                   <strong>${escapeHtml(p.student_name)}</strong>
                   <br><small>${escapeHtml(p.career)}</small>
                </div>
                
                <button type="button" class="btn-view-pdf-icon" onclick="window.openPdfModal('${p.id_final_project}')">
                    <i class="fas fa-eye"></i> Ver PDF
                </button>
             </div>
             
             <div class="abstract-box">
               ${escapeHtml(p.abstract)}
             </div>
          </div>

          <div class="card-footer" style="${bgFooter}">
             <div class="form-grid">
                 <div>
                    <label class="form-label">Dictamen</label>
                    <select id="decision_${p.id_fp_change_review}" class="form-select" ${disabledAttr}>
                      <option value="">Selecciona...</option>
                      <option value="APPROVED" ${
        prevDecision === "APPROVED" ? "selected" : ""
      }>Aprobado</option>
                      <option value="REJECTED" ${
        prevDecision === "REJECTED" ? "selected" : ""
      }>No Aprobado</option>
                    </select>
                 </div>
                 <div>
                    <label class="form-label">Comentarios</label>
                    <textarea id="comments_${p.id_fp_change_review}" class="form-textarea" placeholder="Escribe observaciones..." ${disabledAttr}>${
        p.comment || ""
      }</textarea>
                 </div>
             </div>

             ${
        !isReviewed
          ? `
                 <div class="action-row">
                    <input type="file" id="file_${p.id_fp_change_review}" accept="application/pdf" class="file-input-compact">
                    
                    <button class="btn-send" onclick="confirmAndSubmit(${p.id_fp_change_review})">
                        Enviar <i class="fas fa-paper-plane"></i>
                    </button>
                 </div>
             `
          : `
                 <div style="text-align:center; font-size:0.85rem; color:#15803d; font-weight:600; padding:10px;">
                    <i class="fas fa-lock"></i> Dictamen registrado correctamente.
                 </div>
             `
      }
             
             ${
        p.reviewer_pdf_url
          ? `<div style="margin-top:5px; font-size:0.75rem; color:#15803d; text-align:right;">
                      <i class="fas fa-file-pdf"></i> Archivo adjunto: 
                      <span style="text-decoration:underline; cursor:pointer;" onclick="window.open('..${p.reviewer_pdf_url}', '_blank')">Ver archivo subido</span>
                    </div>`
          : ""
      }
          </div>
        `;
      container.appendChild(card);
    });
  } catch (error) {
    console.error(error);
    container.innerHTML = "<p>Error al cargar información.</p>";
  }
}

window.confirmAndSubmit = function (id) {
  const decision = document.getElementById(`decision_${id}`).value;

  if (!decision) {
    Swal.fire("Atención", "Debes seleccionar un dictamen.", "warning");
    return;
  }

  Swal.fire({
    title: "¿Enviar dictamen?",
    text: "Una vez enviado, no podrás modificarlo desde este panel.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#4f46e5",
    cancelButtonColor: "#d33",
    confirmButtonText: "Sí, enviar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      submitReview(id);
    }
  });
};

async function submitReview(id) {
  const decision = document.getElementById(`decision_${id}`).value;
  const comments = document.getElementById(`comments_${id}`).value;
  const fileInput = document.getElementById(`file_${id}`);

  const form = new FormData();
  form.append("id_fp_change_review", id);
  form.append("decision", decision);
  form.append("comments", comments);
  if (fileInput.files.length > 0) {
    form.append("reviewer_file", fileInput.files[0]);
  }

  Swal.fire({
    title: "Enviando...",
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading(),
  });

  try {
    const res = await CookieManager.fetchWithAuth(SUBMIT_REVIEW_API, {
      method: "POST",
      body: form,
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || "Error");

    await Swal.fire("Éxito", "Dictamen registrado", "success");
    loadProfessorProjects(); // Recargar para ver los cambios
  } catch (err) {
    Swal.fire("Error", err.message, "error");
  }
}

window.openPdfModal = function (projectId) {
  const modal = document.getElementById("pdfModal");
  const viewer = document.getElementById("pdfViewer");
  if (modal && viewer) {
    viewer.src = `${PROFESSOR_PDF_API}?id=${projectId}`;
    modal.style.display = "flex";
  }
};

window.closePdfModal = function () {
  const modal = document.getElementById("pdfModal");
  const viewer = document.getElementById("pdfViewer");
  if (modal) modal.style.display = "none";
  if (viewer) viewer.src = "";
};

function escapeHtml(t) {
  return t ? t.replace(/&/g, "&amp;").replace(/</g, "&lt;") : "";
}
