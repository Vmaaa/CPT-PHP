const PROTOCOL_API_URL = API_URL + "/student/status/";

document.addEventListener("DOMContentLoaded", async () => {
  await loadProtocolStatus();
});

async function loadProtocolStatus() {
  const container = document.getElementById("protocol-container");
  container.innerHTML = `<p class="loading-text">Cargando información...</p>`;
  console.log(API_URL);
  try {
    const response = await CookieManager.fetchWithAuth(PROTOCOL_API_URL, {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
      },
    });

    if (!response.ok) {
      throw new Error("Error al obtener el estado del protocolo");
    }

    const result = await response.json();

    if (!result.hasProject) {
      renderNoProject(container);
      return;
    }

    renderProjectStatus(container, result);
  } catch (error) {
    console.error(error);
    container.innerHTML =
      `<p class="error-text">No se pudo cargar la información del protocolo</p>`;
  }
}

function renderNoProject(container) {
  container.innerHTML = `
    <div class="card">
      <h3>No tienes un proyecto registrado</h3>
      <p>Debes subir tu protocolo para iniciar el proceso.</p>
      <a href="/CPT/pages/protocolo_form.php" class="btn btn-primary">
        Subir protocolo
      </a>
    </div>
  `;
}

function renderProjectStatus(container, data) {
  const { project, reviews, completedReviews } = data;

  const reviewsHtml = reviews.length
    ? reviews.map((r) => `
        <li>
          <strong>${r.professor_name}</strong>:
          ${r.comment ?? "Sin respuesta"}
        </li>
      `).join("")
    : `<li class="empty-text">Aún no hay revisiones</li>`;

  container.innerHTML = `
    <div class="card">
      <h3>${project.title}</h3>
      <p><strong>Estado:</strong> ${project.status}</p>

      <h4>Revisiones (${completedReviews}/3)</h4>
      <ul class="reviews-list">
        ${reviewsHtml}
      </ul>
    </div>
  `;
}
