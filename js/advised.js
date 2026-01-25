const ADVISED_API = API_URL + "/professor/advised";
const PDF_API = API_URL + "/admin/pdf";
const REVIEWS_API = API_URL + "/professor/project_reviews"; // Nuevo Endpoint

document.addEventListener("DOMContentLoaded", loadAdvised);

async function loadAdvised() {
  const container = document.getElementById("advised-container");
  container.innerHTML = "<p>Cargando alumnos...</p>";

  try {
    const res = await CookieManager.fetchWithAuth(ADVISED_API);
    const data = await res.json();

    container.innerHTML = "";

    if (!data.data || data.data.length === 0) {
      container.innerHTML = "<p>No tienes alumnos asignados como asesor.</p>";
      return;
    }

    const statusMap = {
      "PENDING": { text: "Pendiente", class: "status-pending" },
      "UNDER_REVIEW": { text: "En Revisión", class: "status-under-review" },
      "APPROVED": { text: "Aprobado", class: "status-approved" },
      "REJECTED": { text: "Rechazado", class: "status-rejected" },
    };

    data.data.forEach((p) => {
      const st = statusMap[p.status] ||
        { text: p.status, class: "status-pending" };

      const card = document.createElement("div");
      card.className = "advised-card"; // Clase del nuevo CSS

      card.innerHTML = `
                <div class="card-header">
                    <h3>${escapeHtml(p.student_name)}</h3>
                    <span class="status-badge ${st.class}">${st.text}</span>
                </div>
                
                <div class="card-body">
                    <p class="project-title">${escapeHtml(p.title)}</p>
                    <p class="career-text">${escapeHtml(p.career)}</p>
                    <small style="color:#94a3b8; margin-top:5px;">Versión actual: ${p.stage}</small>
                </div>

                <div class="card-actions">
                    <button class="btn-action btn-pdf" onclick="window.openPdf(${p.id_final_project})">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                    
                    ${
        p.status !== "PENDING"
          ? `
                    <button class="btn-action btn-obs" onclick="window.openReviews(${p.id_final_project})">
                        <i class="fas fa-comments"></i> Observaciones
                    </button>
                    `
          : ""
      }
                </div>
            `;
      container.appendChild(card);
    });
  } catch (error) {
    console.error(error);
    container.innerHTML = "<p>Error al cargar.</p>";
  }
}

window.openPdf = function (id) {
  const modal = document.getElementById("pdfModal");
  const viewer = document.getElementById("pdfViewer");
  viewer.src = `${PDF_API}?id=${id}`;
  modal.style.display = "flex";
};

window.openReviews = async function (id) {
  const modal = document.getElementById("reviewsModal");
  const container = document.getElementById("reviewsContent");

  container.innerHTML = "<p>Cargando comentarios...</p>";
  modal.style.display = "flex";

  try {
    const res = await CookieManager.fetchWithAuth(`${REVIEWS_API}?id=${id}`);
    const data = await res.json();

    if (!data.data || data.data.length === 0) {
      container.innerHTML =
        "<p class='text-muted'>Aún no hay comentarios registrados por los revisores.</p>";
      return;
    }

    let html = '<ul class="reviews-list-modal">';
    data.data.forEach((r) => {
      const gradeText = (parseInt(r.grade) >= 6 || parseInt(r.grade) === 1)
        ? '<span class="text-approved">APROBADO</span>'
        : '<span class="text-rejected">NO APROBADO</span>';

      const comment = r.comment
        ? escapeHtml(r.comment)
        : "<em>Sin comentarios escritos.</em>";

      html += `
                <li class="review-item-modal">
                    <div class="review-item-header">
                        <span>${escapeHtml(r.reviewer_name)}</span>
                        ${
        r.grade !== null
          ? gradeText
          : '<span style="color:#999">Pendiente</span>'
      }
                    </div>
                    <div style="font-size:0.9rem; color:#475569;">${comment}</div>
                </li>
            `;
    });
    html += "</ul>";
    container.innerHTML = html;
  } catch (error) {
    container.innerHTML = "<p>Error al cargar observaciones.</p>";
  }
};

window.closeModal = function (modalId) {
  document.getElementById(modalId).style.display = "none";
  if (modalId === "pdfModal") document.getElementById("pdfViewer").src = "";
};

function escapeHtml(t) {
  return t ? t.replace(/&/g, "&amp;").replace(/</g, "&lt;") : "";
}
