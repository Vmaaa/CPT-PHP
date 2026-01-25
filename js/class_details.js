CLASS_API_URL = API_URL + "/class/";
CLASS_ASSIGNMENT_API_URL = API_URL + "/class/assigment/";

document.addEventListener("DOMContentLoaded", async () => {
  const urlParams = new URLSearchParams(window.location.search);
  const classId = urlParams.get("id_class");
  if (classId) {
    const classDetails = await loadSpecificClass(classId);
  } else {
    SwalMessage({
      tile: "Error",
      text: "Suceció un error al cargar la clase",
      icon: "error",
    });
    setTimeout(() => {
      window.location.href = BASE_URL + "/pages/classes.php";
    }, 2000);
  }
});

async function loadSpecificClass(classId) {
  const queryParams = new URLSearchParams({ id_class: classId });

  try {
    const response = await CookieManager.fetchWithAuth(
      `${CLASS_API_URL}?${queryParams.toString()}`,
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
      setTimeout(() => {
        window.location.href = BASE_URL + "/pages/classes.php";
      }, 2000);
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
function renderProfessors(professors = []) {
  const container = document.getElementById("class-professors");

  if (professors.length === 0) {
    container.innerHTML =
      `<h3>Profesores</h3><p>No hay profesores asignados</p>`;
    return;
  }

  container.innerHTML = `
    <h3>Profesores</h3>
    <ul>
      ${
    professors
      .map(
        (p) => `
          <li>
            ${p.name}
          </li>
        `,
      )
      .join("")
  }
    </ul>
  `;
}
function renderStudents(students = []) {
  const container = document.getElementById("class-students");

  if (students.length === 0) {
    container.innerHTML = `<h3>Alumnos</h3><p>No hay alumnos inscritos</p>`;
    return;
  }

  // button to edit students
  container.innerHTML = `
<div class="name-with-action">
<h3>Alumnos</h3>
<button class="btn btn-primary" onclick="editStudents()">
    <i class="fas fa-edit"></i> Editar Alumnos</button>
</div>
    <ul>
      ${
    students
      .map(
        (s) => `
          <li>${s.name} – ${s.school_id_number}</li>
        `,
      )
      .join("")
  }
    </ul>
  `;
}
function renderAssignments(assignments = []) {
  const container = document.getElementById("class-assignments");

  if (assignments.length === 0) {
    container.innerHTML =
      `<h3>Actividades</h3><p>No hay actividades registradas</p>`;
    return;
  }

  container.innerHTML = `
    <div class="name-with-action">
      <h3>Actividades</h3>
      <button class="btn btn-primary">
        <i class="fas fa-plus"></i> Nueva Actividad</button>
    </div>
    <ul>
      ${
    assignments
      .map(
        (a) => `
          <li class="assignment-item" onclick="selectAssignment(${a.id_assigment}, this)">
            ${a.title}
          </li>
        `,
      )
      .join("")
  }
    </ul>
  `;

  const newActivityButton = container.querySelector(".btn-primary");
  newActivityButton.addEventListener("click", () => {
    openNewAssignmentModal(assignments[0].id_class);
  });
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
    <h3>Detalle de la Actividad</h3>
    <button class="btn btn-primary" >
      <i class="fas fa-edit"></i> Editar Actividad</button>
  </div>

    <div class="assignment-detail-grid">

      <!-- COLUMNA IZQUIERDA -->
      <div class="assignment-info">
        <p>${assignment.description || "Sin descripción"}</p>

        <p><strong>Fecha de entrega:</strong> ${assignment.due_date}</p>

        <h4>Entregados</h4>
        <ul class="delivered-list">
          ${
    assignment.deliveries?.length
      ? assignment.deliveries
        .map((d) => `<li>${d.student_name}</li>`)
        .join("")
      : "<li>Nadie ha entregado aún</li>"
  }
        </ul>
      </div>

      <!-- COLUMNA DERECHA -->
      <div class="assignment-preview">
        ${
    assignment.file_url
      ? `<iframe src="${assignment.file_url}" loading="lazy"></iframe>`
      : `<p>No hay archivo asociado</p>`
  }
      </div>

    </div>
  `;

  const editButton = container.querySelector(".btn-primary");
  editButton.addEventListener("click", () => {
    openEditAssignmentModal(assignment);
  });
}

function renderClassDetails(classData) {
  renderClassInfo(classData);
  renderProfessors(classData.professors);
  renderStudents(classData.students);
  renderAssignments(classData.assigments);
}

function openNewAssignmentModal(classId) {
  document.getElementById("assignment-modal-title").textContent =
    "Nueva asignación";

  document.getElementById("assignment-id").value = "";
  document.getElementById("assignment-class-id").value = classId;
  document.getElementById("assignment-title").value = "";
  document.getElementById("assignment-description").value = "";
  document.getElementById("assignment-due-date").value = "";
  document.getElementById("assignment-file").value = "";
  document.getElementById("current-assignment-file").textContent = "";

  window.openModal("modal-assignment");
}
function openEditAssignmentModal(assignment) {
  document.getElementById("assignment-modal-title").textContent =
    "Editar asignación";

  document.getElementById("assignment-id").value = assignment.id_assigment;

  document.getElementById("assignment-class-id").value = assignment.id_class;

  document.getElementById("assignment-title").value = assignment.title;

  document.getElementById("assignment-description").value =
    assignment.description || "";

  document.getElementById("assignment-due-date").value = assignment.due_date
    .replace(" ", "T");

  document.getElementById("assignment-file").value = "";

  document.getElementById("current-assignment-file").textContent =
    assignment.file_url
      ? `Archivo actual: ${assignment.file_url.split("/").pop()}`
      : "Sin archivo actual";

  window.openModal("modal-assignment");
}
async function submitAssignment() {
  const id = document.getElementById("assignment-id").value;
  let editing = false;
  if (id) {
    editing = true;
  }
  const fileInput = document.getElementById("assignment-file");
  const file = fileInput.files[0];

  // Validación archivo
  if (file) {
    if (file.type !== "application/pdf") {
      alert("El archivo debe ser un PDF");
      return;
    }

    if (file.size > 5 * 1024 * 1024) {
      alert("El archivo no debe superar los 5 MB");
      return;
    }
  }

  const formData = new FormData();
  formData.append(
    "id_class",
    document.getElementById("assignment-class-id").value,
  );
  formData.append(
    "title",
    document.getElementById("assignment-title").value,
  );
  formData.append(
    "description",
    document.getElementById("assignment-description").value,
  );

  // Due date in format YYYY-MM-DD HH:MM:SS
  const dueDateInput = document.getElementById("assignment-due-date").value;
  const dueDate = new Date(dueDateInput);
  const dueDateFormatted = dueDate
    .toISOString()
    .slice(0, 19)
    .replace("T", " ");
  formData.append("due_date", dueDateFormatted);

  if (editing) {
    formData.append("id_assigment", id);
  }

  if (file) {
    formData.append("file", file);
  }

  const response = await CookieManager.fetchWithAuth(
    CLASS_ASSIGNMENT_API_URL,
    {
      method: editing ? "PUT" : "POST",
      body: formData,
    },
  );

  if (!response.ok) {
    SwalMessage({
      tile: "Error",
      text: "Suceció un error al guardar la asignación",
      icon: "error",
    });
    return;
  }

  SwalMessage({
    tile: "Éxito",
    text: "Asignación" + (editing ? " editada" : " creada") + " correctamente",
    icon: "success",
  });
  window.closeModal("modal-assignment");
}
