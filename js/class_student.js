CLASS_API_URL = API_URL + "/class/";
CLASS_ASSIGNMENT_API_URL = API_URL + "/class/assigment/";
CLASS_ASSIGMENT_SUBMISSION_API_URL = CLASS_ASSIGNMENT_API_URL + "submission/";
STUDENTS_API_URL = API_URL + "/student/";
CLASS_STUDENTS_API_URL = API_URL + "/class/student/";

document.addEventListener("DOMContentLoaded", async () => {
  const classData = await loadAccountClass(); 
});

async function loadAccountClass() {
  try {
    const response = await CookieManager.fetchWithAuth(
      CLASS_API_URL,
      {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
        },
      },
    );

    if (!response.ok) {
      throw new Error("Error al cargar las clases");
    }

    const result = await response.json();
    if (result.data.length === 0) {
      SwalMessage({
        tile: "Error",
        text: "Clase no encontrada",
        icon: "error",
      });
      /*
      setTimeout(() => {
        window.location.href = BASE_URL + "/pages/classes.php";
      }, 2000);
      */
      return;
    }
    renderClassDetails(result.data[0]);
    return result.data[0];
  } catch (error) {
    console.error(error);
    document.getElementById("class-details-container").innerHTML =
      `<p class="error-text">Error al cargar la clase. Por favor, inténtelo de nuevo más tarde.</p>`;
  }
}
function renderClassInfo(classData) {
  const container = document.getElementById("class-info");

  container.innerHTML = `
    <h3>Información de la Clase</h3>
    <p><strong>Nombre:</strong> ${classData.name}</p>
    <p><strong>Carrera:</strong> ${classData.career}</p>
  `;
}

function renderAssignments(assignments = [], classId) {
  const container = document.getElementById("class-assignments");

  const header = `
    <div class="name-with-action">
      <h3>Actividades</h3>
    </div>
  `;

  if (!assignments.length) {
    container.innerHTML = `
      ${header}
      <p>No hay actividades creadas</p>
    `;
    return;
  }

  const assignmentsList = assignments
    .map(
      (a) => `
        <li class="assignment-item" onclick="selectAssignment(${a.id_assigment}, this)">
          ${a.title}
        </li>
      `,
    )
    .join("");

  container.innerHTML = `
    ${header}
    <ul>
      ${assignmentsList}
    </ul>
  `;
  // Guardamos para referencia
  window.__classAssignments = assignments;
}

function selectAssignment(idAssignment, el) {
  document
    .querySelectorAll(".assignment-item")
    .forEach((item) => item.classList.remove("active"));

  el.classList.add("active");

  const assignment = window.__classAssignments.find(
    (a) => a.id_assigment === idAssignment,
  );

  if (!assignment) return;

  renderAssignmentDetail(assignment);
}
function renderAssignmentDetail(assignment) {
  const container = document.getElementById("assignment-detail");

  container.classList.remove("assignment-empty");
  container.classList.add("assignment-detail");

  container.innerHTML = `
  <div class="name-with-action">
    <h3>${assignment.title}</h3>
    <button class="btn btn-primary">
      <i class="fas fa-paper-plane"></i> ${assignment.submissions.length > 0 ? "Editar" : "Enviar"} entrega
  </div>

    <div class="assignment-detail-grid">

      <!-- COLUMNA IZQUIERDA -->
      <div class="assignment-info">
        <h3><strong>Datos de la actividad</strong></h3>
        <p><strong>Descripción:</strong> ${assignment.description || "Sin descripción"}</p>

        <p><strong>Fecha límite de entrega:</strong> ${assignment.due_date}</p>

          <div class="assignment-preview">
            ${
        assignment.file_url
          ? `<iframe src="${assignment.file_url}" loading="lazy"></iframe>`
          : `<p>No hay archivo asociado</p>`
      }
          </div>
      </div>

      <!-- COLUMNA DERECHA -->
    ${assignment.submissions.length > 0 ? `
      <div class="submission-preview">
        <h3><strong>Tu entrega</strong></h3>
        <p><strong>Fecha de entrega:</strong> ${assignment.submissions[0].submitted_at}</p>
        <div class="form-row">
        <p><strong>Calificación:</strong> ${assignment.submissions[0].grade || "Sin calificar"}</p>
        <p><strong>Calificado el:</strong> ${assignment.submissions[0].graded_at || "Aún no calificado"}</p>
        </div>
        <p><strong>Feedback del profesor:</strong><br/>${assignment.submissions[0].feedback || "Sin comentarios"}</p>
        <iframe src="${assignment.submissions[0].file_url}" loading="lazy"></iframe>`
      : `<p>No has subido aún una entrega</p>`}
      </div>

    </div>
  `;

  const sendButton = container.querySelector(".btn-primary");
  sendButton.addEventListener("click", () => sendAssignment(assignment));
}

function renderClassDetails(classData) {
  renderClassInfo(classData);
  renderAssignments(classData.assigments, classData.id_class);
}

function sendAssignment(assigment) {
  const prevSubmission = assigment.submissions.length > 0 ? assigment.submissions[0] : null;
  const feedbackText = prevSubmission ?
  `Ya has enviado una entrega para esta actividad. Si deseas actualizar tu entrega, puedes subir un nuevo archivo.
  Ten en cuenta que tu calificación, si es que existe, será reemplazada por la nueva entrega y el profesor tendrá que calificarla nuevamente.`
  : "Sube tu archivo para enviar tu entrega. Asegúrate de que el archivo cumpla con los requisitos especificados por tu profesor.";

  document.getElementById("submission-feedback").innerText = feedbackText;
  document.getElementById("upload-submission-assignment-id").value = assigment.submissions.length > 0 ? assigment.submissions[0].id_assigment_submission : "";
  document.getElementById("upload-assignment-id").value = assigment.id_assigment;
  openModal("modal-upload-submission-file");
}

function closeUploadSubmissionFileModal() {
  closeModal("modal-upload-submission-file");
}

async function uploadSubmissionFile() {
  const fileInput = document.getElementById("submission-file");
  const file = fileInput.files[0];
  
  if (!file) {
    SwalMessage({
      title: "Error",
      text: "Por favor, selecciona un archivo para subir.",
      icon: "error",
    });
    return;
  }

  //pdf validation
  if (file.type !== "application/pdf") {
    SwalMessage({
      title: "Error",
      text: "Solo se permiten archivos PDF.",
      icon: "error",
    });
    return;
  }
  
  // Tamaño máximo de 5mb
  if (file.size > 5 * 1024 * 1024) {
    SwalMessage({
      title: "Error",
      text: "El archivo no puede superar los 5MB.",
      icon: "error",
    });
    return;
  }

  const editing = !!document.getElementById("upload-submission-assignment-id").value;

  const formData = new FormData();
  formData.append("file", file);
  formData.append("id_assigment", document.getElementById("upload-assignment-id").value);
  if (editing) {
  formData.append("id_assigment_submission", document.getElementById("upload-submission-assignment-id").value);
  }

  const response = await CookieManager.fetchWithAuth(
    CLASS_ASSIGMENT_SUBMISSION_API_URL,
    {
      method: editing ? "PUT" : "POST",
      body: formData,
    },
  );
  
  if (!response.ok) {
    SwalMessage({
      title: "Error",
      text: "Hubo un error al subir tu entrega. Por favor, inténtalo de nuevo.",
      icon: "error",
    });
    return;
  }
  
  SwalMessage({
    title: "Éxito",
    text: "Tu entrega ha sido subida correctamente.",
    icon: "success",
  });

  // Recargamos los detalles de la clase para actualizar el estado de la entrega
  await loadAccountClass();
  closeUploadSubmissionFileModal();

}
