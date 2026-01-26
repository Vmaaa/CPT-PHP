CLASS_API_URL = API_URL + "/class/";
CLASS_ASSIGNMENT_API_URL = API_URL + "/class/assigment/";
STUDENTS_API_URL = API_URL + "/student/";
CLASS_STUDENTS_API_URL = API_URL + "/class/student/";

document.addEventListener("DOMContentLoaded", async () => {
  const urlParams = new URLSearchParams(window.location.search);
  const classId = urlParams.get("id_class");
  if (classId) {
    const classDetails = await loadSpecificClass(classId);
    await fetchAvailableStudents(classDetails.id_career);
  } else {
    SwalMessage({
      tile: "Error",
      text: "Sucedió un error al cargar la clase",
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

function renderStudents(students = [], classId, careerId) {
  const container = document.getElementById("class-students");

  const header = `
    <div class="name-with-action">
      <h3>Alumnos</h3>
      <button class="btn btn-primary" onclick="openEditClassStudentsModal(${classId}, ${careerId})">
        <i class="fas fa-edit"></i> Editar Alumnos
      </button>
    </div>
  `;

  if (!students.length) {
    container.innerHTML = `
      ${header}
      <p>No hay alumnos inscritos</p>
    `;
    return;
  }

  const studentsList = students
    .map((s) => `<li>${s.name} – ${s.school_id_number}</li>`)
    .join("");

  container.innerHTML = `
    ${header}
    <ul>
      ${studentsList}
    </ul>
  `;
}
function renderAssignments(assignments = [], classId) {
  const container = document.getElementById("class-assignments");

  const header = `
    <div class="name-with-action">
      <h3>Actividades</h3>
      <button class="btn btn-primary" onclick="openNewAssignmentModal(${classId})">
        <i class="fas fa-plus"></i> Nueva Actividad
      </button>
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
    <button class="btn btn-primary" ${
    assignment.can_be_edited ? "" : "disabled"
  }>
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
  renderStudents(classData.students, classData.id_class, classData.id_career);
  renderAssignments(classData.assigments, classData.id_class);
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
  document.getElementById("delete-assignment-file-container").style.display =
    "none";
  // Reset checkbox
  document.getElementById("delete-assignment-file").checked = false;

  window.openModal("modal-assignment");
}
function openEditAssignmentModal(assignment) {
  if (!assignment.can_be_edited) {
    SwalMessage({
      tile: "Error",
      text: "La asignación no puede ser editada, ya que no la creaste tú",
      icon: "error",
    });
    return;
  }
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

  document.getElementById("delete-assignment-file-container").style.display =
    assignment.file_url ? "block" : "none";

  // Reset checkbox
  document.getElementById("delete-assignment-file").checked = false;

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
  const deleteCurrentFileCheckbox = document.getElementById(
    "delete-assignment-file",
  );

  // Validación archivo
  if (file) {
    if (file.type !== "application/pdf") {
      SwalMessage({
        tile: "Error",
        text: "El archivo debe ser un PDF",
        icon: "error",
      });
      return;
    }

    if (file.size > 5 * 1024 * 1024) {
      SwalMessage({
        tile: "Error",
        text: "El archivo no debe superar los 5MB",
        icon: "error",
      });
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

  if (editing && deleteCurrentFileCheckbox.checked) {
    formData.delete("file");
    formData.append("remove_url", "1");
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
      text: "Sucedio un error al guardar la asignación",
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
  //reload page
  const urlParams = new URLSearchParams(window.location.search);
  const classId = urlParams.get("id_class");
  if (classId) {
    await loadSpecificClass(classId);
    // Clear assignment detail
    if (editing) {
      document.getElementById("assignment-detail").innerHTML =
        `<p class="assignment-empty">Seleccione una asignación para ver los detalles</p>`;
    }
  }
}

async function fetchAvailableStudents(params) {
  const queryParams = new URLSearchParams(params);
  const response = await CookieManager.fetchWithAuth(
    `${STUDENTS_API_URL}?${queryParams.toString()}`,
    {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
      },
    },
  );

  if (!response.ok) {
    SwalsMessage({
      tile: "Error",
      text: "Error al cargar los alumnos disponibles",
      icon: "error",
    });
    return;
  }

  const result = await response.json();
  return result.data;
}

async function fetchClassStudents(classId) {
  const queryParams = new URLSearchParams({ id_class: classId });
  const response = await CookieManager.fetchWithAuth(
    `${CLASS_STUDENTS_API_URL}?${queryParams.toString()}`,
    {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
      },
    },
  );

  if (!response.ok) {
    SwalsMessage({
      tile: "Error",
      text: "Error al cargar los alumnos de la clase",
      icon: "error",
    });
    return;
  }

  const result = await response.json();
  return result.data;
}

async function openEditClassStudentsModal(classId, careerId) {
  document.getElementById("edit-students-class-id").value = classId;
  //load current students into the select
  const currentSelect = document.getElementById("current-students");
  currentSelect.innerHTML = "";
  const classStudents = await fetchClassStudents(classId);
  classStudents.forEach((student) => {
    const option = document.createElement("option");
    option.value = student.id_student;
    option.textContent = `${student.name} – ${student.school_id_number}`;
    option.selected = true;
    currentSelect.appendChild(option);
  });

  //load avaliable students into the select
  const select = document.getElementById("available-students");
  select.innerHTML = "";
  const avaliableStudents = await fetchAvailableStudents({
    id_career: careerId,
    "without_class": "1",
  });
  avaliableStudents.forEach((student) => {
    const option = document.createElement("option");
    option.value = student.id_student;
    option.textContent = `${student.name} – ${student.school_id_number}`;
    option.selected = false;
    select.appendChild(option);
  });

  window.openModal("modal-edit-students");
}

async function saveClassStudents() {
  const classId = document.getElementById("edit-students-class-id").value;
  const currentSelect = document.getElementById("current-students");
  const currentStudentIds = Array.from(currentSelect.options).map(
    (option) => option.selected ? option.value : null,
  );
  const availableSelect = document.getElementById("available-students");
  const availableStudentIds = Array.from(availableSelect.options).map(
    (option) => option.selected ? option.value : null,
  );
  const allStudentsIds = currentStudentIds.concat(availableStudentIds);
  const selectedStudentIds = allStudentsIds.filter((id) => id !== null);
  const selectedStudentInformation = Array.from(currentSelect.options)
    .concat(Array.from(availableSelect.options))
    .filter((option) => option.selected)
    .map((option) => ({
      id_student: option.value,
      name: option.textContent,
    }));

  const confirmed = await SwalConfirm({
    title: "Confirmar cambios",
    text: "Los alumnos seleccionados son los siguientes: " +
      selectedStudentInformation
        .map((s) => s.name)
        .join(", "),
    icon: "question",
    confirmButtonText: "Confirmar",
    cancelButtonText: "Cancelar",
  });

  if (!confirmed) {
    return;
  }

  const formData = new FormData();
  formData.append("id_class", classId);
  selectedStudentIds.forEach((id) => {
    formData.append("students_ids[]", id);
  });

  const response = await CookieManager.fetchWithAuth(
    CLASS_STUDENTS_API_URL,
    {
      method: "PUT",
      body: formData,
    },
  );

  if (!response.ok) {
    SwalMessage({
      tile: "Error",
      text: "Sucedió un error al guardar los alumnos de la clase",
      icon: "error",
    });
    return;
  }

  SwalMessage({
    tile: "Éxito",
    text: "Alumnos de la clase actualizados correctamente",
    icon: "success",
  });
  window.closeModal("modal-edit-students");
  //reload class details
  const urlParams = new URLSearchParams(window.location.search);
  const classIdParam = urlParams.get("id_class");
  if (classIdParam) {
    await loadSpecificClass(classIdParam);
  }
}
