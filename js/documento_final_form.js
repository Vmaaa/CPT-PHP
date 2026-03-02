const STATUS_API_URL = API_URL + "/student/status";
const FINAL_DOCUMENT_API = API_URL + "/student/final_doc/"; 

document.addEventListener("DOMContentLoaded", async () => {
  await loadProjectSummary();

  document
    .getElementById("final-document-form")
    .addEventListener("submit", submitFinalDocument);
});

async function loadProjectSummary() {
  try {
    const res = await CookieManager.fetchWithAuth(STATUS_API_URL);
    if (!res.ok) throw new Error("Error de conexión con el servidor");

    const data = await res.json();
    
    if (data.hasProject && data.project && data.project.status === "APPROVED") {
      const summaryDiv = document.getElementById("project-summary");
      
      summaryDiv.innerHTML = `
        <div style="background-color: #f0fdf4; padding: 20px; border-radius: 8px; border-left: 5px solid #16a34a;">
            <span style="background-color: #16a34a; color: white; padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; margin-bottom: 10px; display: inline-block;">
                PROTOCOLO APROBADO
            </span>
            <h3 style="margin: 0 0 10px 0; color: #1e293b; font-size: 1.2rem;">
                ${escapeHtml(data.project.title)}
            </h3>
            <p style="margin: 0; color: #475569; font-size: 0.9em;">
                Asegúrate de subir la versión definitiva. Una vez enviada, el estatus cambiará y el jurado comenzará la revisión de tu Trabajo Terminal.
            </p>
        </div>
      `;
      summaryDiv.style.display = "block";
    } else {
        Swal.fire({
            title: "Acceso denegado", 
            text: "No tienes un protocolo aprobado pendiente de subir documento final.", 
            icon: "warning",
            confirmButtonColor: "#1e40af"
        }).then(() => {
            window.location.href = "/CPT/pages/projects_student.php";
        });
    }
  } catch (error) {
    console.error("Error al cargar status:", error);
    Swal.fire("Error", "No se pudo verificar el estado de tu proyecto.", "error");
  }
}

async function submitFinalDocument(e) {
  e.preventDefault();

  const form = e.target;
  const data = new FormData(form);
  const fileInput = document.getElementById("final_file");

  if (fileInput.files.length === 0) {
    Swal.fire("Atención", "Debes seleccionar un archivo PDF.", "warning");
    return;
  }

  Swal.fire({
    title: "¿Enviar Documento Final?",
    text: "Vas a enviar la versión definitiva para la revisión del jurado. ¿Estás seguro?",
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#16a34a",
    cancelButtonColor: "#64748b",
    confirmButtonText: "Sí, enviar documento",
    cancelButtonText: "Revisar de nuevo"
  }).then(async (result) => {
      if (result.isConfirmed) {
          Swal.fire({ 
              title: "Subiendo documento...", 
              text: "Por favor no cierres esta ventana.",
              allowOutsideClick: false, 
              didOpen: () => Swal.showLoading() 
          });

          try {
            const res = await CookieManager.fetchWithAuth(FINAL_DOCUMENT_API, {
              method: "POST",
              body: data,
            });
            
            const resultData = await res.json();
            
            if (!res.ok) throw resultData;

            Swal.fire({
              title: "¡Entregado!",
              text: "Tu documento final ha sido recibido correctamente.",
              icon: "success",
              confirmButtonColor: "#1e40af",
            }).then(() => {
              window.location.href = "/CPT/pages/projects_student.php";
            });

          } catch (err) {
            console.error(err);
            Swal.fire("Error", err.error || "Ocurrió un error al subir el documento.", "error");
          }
      }
  });
}

function escapeHtml(text) {
  return text ? text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;") : "";
}
