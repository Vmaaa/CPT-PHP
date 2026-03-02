const PROFESSOR_FINAL_PROJECTS_API = API_URL + "/professor/final_reviews";
const SUBMIT_REVIEW_API = API_URL + "/professor/submit_review/"; 
const PROFESSOR_PDF_API = API_URL + "/admin/pdf";

document.addEventListener("DOMContentLoaded", loadProfessorFinalProjects);

async function loadProfessorFinalProjects() {
  const container = document.getElementById("projects-container");
  container.innerHTML = "<p>Cargando documentos finales...</p>";

  try {
    const res = await CookieManager.fetchWithAuth(PROFESSOR_FINAL_PROJECTS_API);
    const data = await res.json();
    container.innerHTML = "";

    if (!data.data?.length) {
      container.innerHTML = "<p style='grid-column: 1/-1; text-align:center'>No tienes documentos finales asignados para revisión.</p>";
      return;
    }

    const projects = data.data.sort((a, b) => {
      const aReviewed = (a.grade !== null && a.grade !== undefined) ? 1 : 0;
      const bReviewed = (b.grade !== null && b.grade !== undefined) ? 1 : 0;
      return aReviewed - bReviewed;
    });

    const statusMap = {
      "FINAL_UNDER_REVIEW": { text: "En Revisión Final", class: "status-under-review" },
      "READY_TO_PRESENT": { text: "Listo para Presentar", class: "status-approved" },
    };

    projects.forEach((p) => {
      const card = document.createElement("div");
      const isReviewed = p.grade !== null && p.grade !== undefined;

      let prevDecision = "";
      if (isReviewed) {
        prevDecision = (p.grade >= 1) ? "APPROVED" : "REJECTED";
      }

      const statusInfo = statusMap[p.status] || { text: p.status, class: "status-pending" };

      card.className = "professor-card";
      if (isReviewed) {
        card.style.borderLeft = "5px solid #15803d";
      }

      const disabledAttr = isReviewed ? "disabled" : "";
      const bgFooter = isReviewed ? "background-color:#f0fdf4;" : "";

      // Botón para la presentación si existe la URL
      let presentationBtn = "";
      if (p.presentation_url) {
          presentationBtn = `
              <button type="button" class="btn-view-pdf-icon" style="background-color: #f97316; border: none; color: white; margin-top: 5px;" onclick="window.openDocModal('${p.presentation_url}', 'Presentación del Trabajo Terminal')">
                  <i class="fas fa-file-powerpoint"></i> Ver Presentación
              </button>
          `;
      }

      card.innerHTML = `
          <div class="card-header">
             <div style="display:flex; align-items:center; gap:10px;">
                 <h3>${escapeHtml(p.title)}</h3>
                 ${isReviewed ? `<span class="badge-sent">✔ DICTAMINADO</span>` : ""}
             </div>
             <span class="status-badge ${statusInfo.class}">${statusInfo.text}</span>
          </div>

          <div class="card-body">
             <div class="student-row" style="align-items: flex-start;">
                <div>
                   <small style="color: #64748b; font-weight: 600;">Equipo:</small><br>
                   <strong>${escapeHtml(p.student_name)}</strong>
                   <br><small style="color: #64748b;">${escapeHtml(p.career)}</small>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <button type="button" class="btn-view-pdf-icon" onclick="window.openDocModal('${PROFESSOR_PDF_API}?id=${p.id_final_project}', 'Documento Final PDF')">
                        <i class="fas fa-file-pdf"></i> Ver PDF Final
                    </button>
                    ${presentationBtn}
                </div>
             </div>
          </div>

          <div class="card-footer" style="${bgFooter}">
             <div class="form-grid">
                 <div>
                    <label class="form-label">Veredicto del Jurado</label>
                    <select id="decision_${p.id_fp_change_review}" class="form-select" ${disabledAttr}>
                      <option value="">Selecciona...</option>
                      <option value="APPROVED" ${prevDecision === "APPROVED" ? "selected" : ""}>Presentar (Liberado)</option>
                      <option value="REJECTED" ${prevDecision === "REJECTED" ? "selected" : ""}>No Presentar (Corregir)</option>
                    </select>
                 </div>
                 <div>
                    <label class="form-label">Comentarios/Correcciones</label>
                    <textarea id="comments_${p.id_fp_change_review}" class="form-textarea" placeholder="Escribe observaciones para el documento final..." ${disabledAttr}>${p.comment || ""}</textarea>
                 </div>
             </div>

             ${!isReviewed ? `
                 <div class="action-row">
                    <input type="file" id="file_${p.id_fp_change_review}" accept="application/pdf" class="file-input-compact">
                    <button class="btn-send" onclick="confirmAndSubmit(${p.id_fp_change_review})">
                        Enviar <i class="fas fa-paper-plane"></i>
                    </button>
                 </div>
             ` : `
                 <div style="text-align:center; font-size:0.85rem; color:#15803d; font-weight:600; padding:10px;">
                    <i class="fas fa-lock"></i> Veredicto registrado correctamente.
                 </div>
             `}
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
    Swal.fire("Atención", "Debes seleccionar un veredicto.", "warning");
    return;
  }

  Swal.fire({
    title: "¿Enviar Veredicto?",
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

  Swal.fire({ title: "Enviando...", allowOutsideClick: false, didOpen: () => Swal.showLoading() });

  try {
    const res = await CookieManager.fetchWithAuth(SUBMIT_REVIEW_API, {
      method: "POST",
      body: form,
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || "Error");

    await Swal.fire("Éxito", "Veredicto registrado", "success");
    loadProfessorFinalProjects(); // Recargar la vista
  } catch (err) {
    Swal.fire("Error", err.message, "error");
  }
}

window.openDocModal = function (url, title) {
  const modal = document.getElementById("pdfModal");
  const viewer = document.getElementById("pdfViewer");
  const titleEl = document.getElementById("pdfModalTitle");

  if (modal && viewer) {
    if (titleEl) titleEl.innerText = title;
    viewer.src = url;
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
